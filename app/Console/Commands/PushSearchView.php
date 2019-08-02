<?php

namespace App\Console\Commands;

use App\Classes\Miscellaneous;
use App\Http\Controllers\ApiV1\PushNotificationReceiveController;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PushSearchView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'push:search_view';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Search View Push Notification';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }



    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $controller = new PushNotificationReceiveController();
        $controller->callPushAction();
//        $url = 'http://52.76.173.213/search_views_push/';
//
//        $curl = curl_init();
//        curl_setopt_array($curl, [
//            CURLOPT_RETURNTRANSFER => 1,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_CUSTOMREQUEST => "POST",
//            CURLOPT_URL => $url
//        ]);
//        $results = curl_exec($curl);
//        curl_close ($curl);
//
//        return $results;
    }
}