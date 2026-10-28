<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public const STATUSES = ['diproses', 'selesai', 'dibatalkan'];

    public const METODE = ['tunai' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer Bank', 'debit' => 'Kartu Debit'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function scopeSelesai($query)
    {
        return $query->where('status', 'selesai');
    }

    public function getMetodeLabelAttribute(): string
    {
        return self::METODE[$this->metode_bayar] ?? $this->metode_bayar;
    }

    public function getTipeLabelAttribute(): string
    {
        return $this->tipe_pesanan === 'dine_in' ? 'Dine-In' : 'Takeaway';
    }
}
