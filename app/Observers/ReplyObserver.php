<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Comment;
use App\Models\Question;
use App\Models\Reply;
use App\Classes\FollowNotification;
use App\Classes\NotificationForUser;

class ReplyObserver
{
    /**
     * Listen to the User created/updated event.
     *
     * @param Reply $reply
     * @return void
     */
    public function saved(Reply $reply)
    {
        try {
            $comment = Comment::find($reply->comment_id);
            $question = Question::find($comment->question_id);
            $notifiable = User::find($question->user_id);
            $reply->question_id = $question->id;

            $expert = User::find($reply->user_id);

//            if (count($expert) && ($expert->type == 'specialist' || $expert->type == 'admin')) {
//                $notification_type = 'Reply-Expert';
//            } else {
//                $notification_type = 'Reply';
//            }

            NotificationForUser::createNotificationReply(
                $question,
                $notifiable->id,
                $comment->user_id,
                $reply->user_id
//                $notification_type
            );

            $followNotification = new FollowNotification();
            $followNotification->createFollowNotification($question->id, $reply->user_id, 'Comment');

        }catch (\Exception $exception){
//            \Log::emergency('From Reply: ' .' '. $exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }

}
