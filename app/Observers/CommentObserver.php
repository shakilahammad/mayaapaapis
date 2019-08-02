<?php

namespace App\Observers;

use App\Events\CreatePointTransaction;
use App\Models\User;
use App\Models\Comment;
use App\Models\Question;
use App\Classes\FollowNotification;
use App\Classes\NotificationForUser;

class CommentObserver
{

    public function created(Comment $comment){

        event(new CreatePointTransaction($comment->user_id, 2));
    }

    public function saved(Comment $comment)
    {
        try {
            $question = Question::find($comment->question_id);
            $notifiable = $question->user_id;
            $checkExpert = User::find($comment->user_id);

            if (count($checkExpert) && ($checkExpert->type == 'specialist' || $checkExpert->type == 'admin')) {
                $notification_type = 'Comment-expert';
            } else {
                $notification_type = 'Comment';
            }

            NotificationForUser::createNotification(
                $question,
                $notifiable,
                $comment->user_id,
                $notification_type
            );

            $followNotification = new FollowNotification();
            $followNotification->createFollowNotification($comment->question_id, $comment->user_id, 'Comment');
        }catch (\Exception $exception){
//            \Log::emergency('From Comment: ' .' '. $exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }

}
