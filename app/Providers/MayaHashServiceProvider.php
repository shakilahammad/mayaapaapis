<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MayaHasher;

class MayaHashServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->singleton('hash', function() {
            return new MayaHasher();
        });
    }

}
