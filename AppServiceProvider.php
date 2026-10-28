<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // @rp($angka) => Rp 12.500
        Blade::directive('rp', function ($expression) {
            return "<?php echo 'Rp ' . number_format((float) ({$expression}), 0, ',', '.'); ?>";
        });
    }
}
