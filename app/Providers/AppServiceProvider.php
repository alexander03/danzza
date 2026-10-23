<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        // Antes estaba en routes/web.php: ignora los warnings (variables/índices no definidos)
        // para que no se conviertan en excepciones. Aquí también aplica con las rutas en caché.
        error_reporting(E_ALL ^ E_WARNING);
    }
}
