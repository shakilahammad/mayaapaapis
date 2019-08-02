<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Answer;
use App\Models\Question;
use App\Classes\FollowNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FollowNotificationJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public $answer;

    /**
     * Create a new job instance.
     *
     * @param $answer
     */
    public function __construct(Answer $answer)
    {
        $this->answer = $answer;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $question = Question::find($this->answer->question_id);
            $user = User::find($question->user_id);
            if (count($question) && count($user)) {
                $followNotification = new FollowNotification();
                $followNotification->createFollowNotification($question->id, $user->id, 'Answered');
            }
        }catch (\Exception $exception){
//            \Log::info($exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }
}
