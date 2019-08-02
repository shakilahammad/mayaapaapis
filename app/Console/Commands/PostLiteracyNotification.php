<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Http\Helper;
use App\Models\Notification;
use Illuminate\Console\Command;
use App\Models\NotificationMessage;

class PostLiteracyNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'literacy:notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send post literacy notification!';

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
     * Execute the console command
     */
    public function handle()
    {
        $now = Carbon::now();
        $system = User::whereEmail(Helper::maya_encrypt('system@maya.com.bd'))->first();

        $results = \DB::select("select * from literacy_measure_results where question_id in (select distinct question_id from literacy_measure_results order by question_id desc) and TIMESTAMPDIFF(DAY, created_at, NOW()) > 30 group by user_id");

        foreach ($results as $result){
            $notificationId = $this->getNotificationMessageId('Post-Literacy');

            $notification = Notification::whereQuestionId($result->question_id)->whereNotifiable($result->user_id)->where('notifications_message_id', $notificationId)->exists();

            if ($now->diffInDays($result->created_at) > 30 && !$notification) {
                Notification::create([
                    'question_id' => $result->question_id,
                    'notifiable' => $result->user_id,
                    'notifier_id' => $system->id,
                    'notifications_message_id' => $this->getNotificationMessageId('Post-Literacy') ?? 34
                ]);
            }
        }
    }

    private function getNotificationMessageId($type)
    {
        $notificationType = NotificationMessage::whereType($type)->first();

        if ($notificationType->count()){
            return $notificationType->id;
        }

        return null;
    }
}
