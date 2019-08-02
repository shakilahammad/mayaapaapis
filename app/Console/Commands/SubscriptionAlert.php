<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\PremiumPayment;
use Illuminate\Console\Command;
use App\Classes\Subscriptions\SubscribeNotification;

class SubscriptionAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscription:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send subscription alert notification to user before ending package date!';

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
     */
    public function handle()
    {
        $count = 0;
        $activePremiumUsers = PremiumPayment::whereStatus('active')->get();

        foreach ($activePremiumUsers as $payment){
            $hours = Carbon::now()->diffInHours($payment->getOriginal('expiry_time'));
            if($hours <= 24){
                SubscribeNotification::sendAlert($payment);
                $count++;
            }
        }

        $this->info("Send alert notification: " . $count . " users!");
    }
}
