<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\PremiumPayment;
use Illuminate\Console\Command;

class UnSubscribeUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unsubscribe:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unsubscribe user from premium';

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
     */
    public function handle()
    {
        $count = 0;
        $activePremiumUsers = PremiumPayment::whereIn('status', ['active', 'free_premium'])->whereRAW('expiry_time < CURRENT_TIMESTAMP')->get();

        if (count($activePremiumUsers)){
            foreach ($activePremiumUsers as $activePremium){
//                if (Carbon::now()->toDateTimeString() > $activePremium->getOriginal('expiry_time')) {
                    $activePremium->update([
                        'status' => 'expired'
                    ]);

                    $user = User::find($activePremium->user_id);
                    $user->update([
                        'is_premium' => 0
                    ]);

                    $count++;
//                }
            }
        }

        $this->info("Unsubscribed " . $count . " users!");
    }
}
