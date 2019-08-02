<?php

namespace App\Observers;

use App\Classes\Miscellaneous;
use App\Events\CreatePointTransaction;
use App\Models\Like;
use App\Models\Question;
use App\Classes\FollowNotification;
use App\Classes\NotificationForUser;
use Illuminate\Support\Facades\Log;

class LikeObserver
{
    /**
     * Listen to the User created/updated event.
     *
     * @param Like $like
     * @return void
     */
    public function created(Like $like){

        Log::emergency('like created');
//        Miscellaneous::create_transaction($like->user_id, 4);
        event(new CreatePointTransaction($like->user_id, 4));

    }

    public function saved(Like $like)
    {
        try {
            $question = Question::find($like->question_id);
            $followNotification = new FollowNotification();
            if($question->user_id != $like->user_id) {
                NotificationForUser::createNotification($question, $question->user_id, $like->user_id, 'Like');
                $followNotification->createFollowNotification($like->question_id, $like->user_id, 'Like');
            }
        }catch (\Exception $exception){
//            \Log::emergency('From Like: ' .' '. $exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }

}
