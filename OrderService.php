<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public const TAX_RATE = 0.10; // PB1 10%

    /**
     * Buat order (header + detail) dalam satu database transaction.
     * Jika invoice bentrok (dua kasir simpan bersamaan) transaksi diulang maks 3x.
     */
    public function create(array $data, int $userId): Order
    {
        $attempts = 0;

        while (true) {
            try {
                return DB::transaction(fn () => $this->persist($data, $userId));
            } catch (UniqueConstraintViolationException $e) {
                if (++$attempts >= 3) {
                    throw $e;
                }
            }
        }
    }

    private function persist(array $data, int $userId): Order
    {
        $subtotal = 0;
        $lines = [];

        foreach ($data['items'] as $i => $item) {
            // Kunci baris produk: cegah dua kasir menjual stok terakhir bersamaan
            $product = Product::lockForUpdate()->findOrFail($item['product_id']);
            $qty = (int) $item['qty'];

            if (! $product->aktif) {
                throw ValidationException::withMessages([
                    "items.$i.product_id" => "Menu {$product->nama_produk} sudah tidak aktif.",
                ]);
            }

            if ($product->stok < $qty) {
                throw ValidationException::withMessages([
                    "items.$i.qty" => "Stok {$product->nama_produk} tidak cukup (sisa {$product->stok}).",
                ]);
            }

            $lineTotal = $product->harga_jual * $qty;
            $subtotal += $lineTotal;

            $lines[] = [
                'product_id' => $product->id,
                'harga_satuan' => $product->harga_jual,
                'harga_pokok' => $product->harga_pokok,
                'kuantiti' => $qty,
                'subtotal_baris' => $lineTotal,
                'catatan_khusus' => $item['note'] ?? null,
            ];

            $product->decrement('stok', $qty);
        }

        // Semua angka dihitung di server, bukan dari form
        $nilai = (float) ($data['diskon_nilai'] ?? 0);
        $diskon = ($data['diskon_tipe'] ?? 'nominal') === 'persen'
            ? (int) round($subtotal * min($nilai, 100) / 100)
            : (int) min($nilai, $subtotal);

        $pajak = (int) round(($subtotal - $diskon) * self::TAX_RATE);
        $grandTotal = $subtotal - $diskon + $pajak;

        $dibayar = $data['metode_bayar'] === 'tunai'
            ? (int) ($data['uang_dibayar'] ?? 0)
            : $grandTotal;

        if ($dibayar < $grandTotal) {
            throw ValidationException::withMessages([
                'uang_dibayar' => 'Uang yang dibayar kurang dari total tagihan.',
            ]);
        }

        $order = Order::create([
            'invoice_number' => $this->nextInvoiceNumber(),
            'user_id' => $userId,
            'tipe_pesanan' => $data['tipe_pesanan'],
            'nomor_meja' => $data['tipe_pesanan'] === 'dine_in' ? $data['nomor_meja'] : null,
            'nama_pemesan' => $data['nama_pemesan'] ?? null,
            'total_subtotal' => $subtotal,
            'diskon_nominal' => $diskon,
            'pajak_nominal' => $pajak,
            'grand_total' => $grandTotal,
            'metode_bayar' => $data['metode_bayar'],
            'uang_dibayar' => $dibayar,
            'uang_kembali' => $dibayar - $grandTotal,
            'status' => 'diproses',
        ]);

        $order->details()->createMany($lines);

        return $order;
    }

    /** Ubah status; pembatalan mengembalikan stok. Hanya dari "diproses". */
    public function updateStatus(Order $order, string $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            $order = Order::with('details')->lockForUpdate()->findOrFail($order->id);

            if ($order->status !== 'diproses') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pesanan berstatus Diproses yang dapat diubah.',
                ]);
            }

            if ($status === 'dibatalkan') {
                foreach ($order->details as $detail) {
                    Product::whereKey($detail->product_id)->increment('stok', $detail->kuantiti);
                }
            }

            $order->update(['status' => $status]);

            return $order;
        });
    }

    /** Format INV-POS-YYYYMMDD-XXXX, urut per hari. */
    public function nextInvoiceNumber(): string
    {
        $prefix = 'INV-POS-' . now()->format('Ymd') . '-';

        $last = Order::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
