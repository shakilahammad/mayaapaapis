<?php

namespace App\Jobs\Literacy;

use App\Http\Helper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use App\Classes\NotificationForUser;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLiteracyNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $question;
    public $questionViewObject;
    public $type;

    public function __construct($question, $questionViewObject, $type)
    {
        $this->question = $question;
        $this->questionViewObject = $questionViewObject;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        NotificationForUser::createNotification(
            $this->question,
            $this->questionViewObject->user_id,
            $this->getSystemUserId(),
            $this->type
        );
    }

    private function getSystemUserId()
    {
        $user = User::whereEmail(Helper::maya_encrypt('system@maya.com.bd'))->first();

        return $user->id;
    }
}
