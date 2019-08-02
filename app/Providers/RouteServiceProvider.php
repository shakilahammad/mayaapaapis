<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $apiV1Namespace = 'App\Http\Controllers\ApiV1';
    protected $apiV2Namespace = 'App\Http\Controllers\APIs\V2';
    protected $apiV3Namespace = 'App\Http\Controllers\APIs\V3';
    protected $apiV4NameSpace =  'App\Http\Controllers\APIs\V4';
    protected $apiV5NameSpace =  'App\Http\Controllers\APIs\V5';
    protected $partnersApiV1Namespace = 'App\Http\Controllers\Partners';
    protected $kioskApiV1Namespace = 'App\Http\Controllers\Partners\kiosk';
    protected $pocoApiV1Namespace = 'App\Http\Controllers\Partners\POCO';
    protected $banglaMedsApiV1Namespace = 'App\Http\Controllers\Partners\BanglaMeds';
    protected $paymentRoutesNamespace = 'App\Http\Controllers\Payment';
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapApiV2Routes();

        $this->mapApiV3Routes();

        $this->mapApiV4Routes();

        $this->mapApiV5Routes();

        $this->mapWebRoutes();

        $this->mapPaymentRoutes();

        $this->mapkioskApiV1Routes();

        $this->mapPocoApiV1Routes();

        $this->mapPartnersRoutes();

        $this->banglaMedsApiV1Namespace();

    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api/v1')
//             ->middleware('api')
             ->namespace($this->apiV1Namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */

    protected function mapApiV2Routes()
    {
        Route::prefix('api/v2')
             ->namespace($this->apiV2Namespace)
             ->group(base_path('routes/api-v2.php'));
    }

    protected function mapApiV3Routes()
    {
        Route::prefix('api/v3')
            ->namespace($this->apiV3Namespace)
            ->group(base_path('routes/api-v3.php'));
    }

    protected function mapApiV4Routes()
    {
        Route::prefix('api/v4')
            ->namespace($this->apiV4NameSpace)
            ->group(base_path('routes/api-v4.php'));
    }

    protected function mapApiV5Routes()
    {
        Route::prefix('api/v5')
            ->namespace($this->apiV5NameSpace)
            ->group(base_path('routes/api-v5.php'));
    }

    protected function mapPartnersRoutes()
    {
        Route::prefix('api/partners')
            ->namespace($this->partnersApiV1Namespace)
            ->group(base_path('routes/partners_api.php'));
    }

    protected function mapkioskApiV1Routes()
    {
        Route::prefix('api/v1/kiosk')
            ->namespace($this->kioskApiV1Namespace)
            ->group(base_path('routes/kiosk.php'));
    }

    protected function mapPocoApiV1Routes()
    {
        Route::prefix('api/v1/poco')
            ->namespace($this->pocoApiV1Namespace)
            ->group(base_path('routes/poco.php'));
    }

    protected function banglaMedsApiV1Namespace()
    {
        Route::prefix('api/v1/banglameds')
            ->namespace($this->banglaMedsApiV1Namespace)
            ->group(base_path('routes/partners_api.php'));
    }

    /**
     * Define the "payment" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapPaymentRoutes()
    {
        Route::namespace($this->paymentRoutesNamespace)
            ->group(base_path('routes/payments.php'));
    }

}
