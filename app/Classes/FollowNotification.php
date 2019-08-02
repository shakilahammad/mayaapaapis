<?php

namespace App\Classes;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Question;
use App\Models\QuestionTag;
use App\Models\TopicFollow;
use App\Models\Notification;
use App\Models\QuestionFollow;
use App\Models\NotificationMessage;

class FollowNotification
{

    /**
     * @var int
     */
    public $min_number_of_notification_per_day = 3;

    /**
     * @param $question_id
     * @param $user_id
     * @param $type
     */
    public function create_question_follower($question_id, $user_id, $type)
    {
        $engagedUser = User::find($user_id);
        if (!$this->isQFollower($question_id, $user_id) && $engagedUser->type == 'user') {
            QuestionFollow::create(['question_id' => $question_id, 'user_id' => $user_id]);
        } else {
            QuestionFollow::whereQuestionId($question_id)->whereUserId($user_id)->update(['updated_at' => Carbon::now()]);
        }
    }

    /**
     * @param $question_id
     * @param $user_id
     * @param $type
     */
    public function notifyFollowers($question_id, $user_id, $type)
    {
        $followers = $this->followerOfQuestion($question_id);
        foreach ($followers as $follower) {
            if ($user_id != $follower->user_id) {
//                $this->createNotification($question_id, $user_id, $follower->user_id, $type, 'Q_follower');
            }
        }
    }

    /**
     * @param $tag_id
     * @param $user_id
     */
    public function create_topic_follower($tag_id, $user_id)
    {
        if (!$this->isTFollower($tag_id, $user_id)) {
            TopicFollow::create(['tag_id' => $tag_id, 'user_id' => $user_id]);
        } else {
            TopicFollow::whereTagId($tag_id)->whereUserId($user_id)->update(['updated_at' => Carbon::now()]);
        }
    }

    /**
     * @param $question_id
     * @param $user_id
     * @param $type
     */
    public function createFollowNotification($question_id, $user_id, $type)
    {
        $questionAsker = User::find(Question::find($question_id)->user_id);

        if (count($questionAsker)) {
            if ($user_id != $questionAsker->id) {
                $this->create_question_follower($question_id, $user_id, $type);
            }

            $this->notifyFollowers($question_id, $user_id, $type);
        }

        $tags = QuestionTag::whereQuestionId($question_id)->get();
        foreach ($tags as $tag) {
            $this->create_topic_follower($tag->tag_id, $user_id);
//            $followers = $this->followerOfTopic($tag->tag_id);
//            foreach ($followers as $follower){
//                if(!$this->isUserEngagedEnough($follower->user_id)){
//                   $this->createNotification($question_id, $user_id, $follower->user_id, $type,'T_follower');
//                }
//            }
        }
    }

    /**
     * @param $question_id
     * @param $notifier_id
     * @param $notifiable
     * @param $type
     * @param $for
     */
    public function createNotification($question_id, $notifier_id, $notifiable, $type, $for)
    {
        switch ($type) {
            case 'Comment':
                if ($for == 'T_follower') {
                    $notification_type = 'Comment_t';
                } else {
                    $notification_type = 'Comment_q';
                }

                $notification_id = $this->isUserAlreadyNotified($notifiable, $question_id, $notification_type);
                if ($notification_id < 0) {
                    if ($notifiable != $notifier_id) {
                        Notification::create([
                            'question_id' => $question_id,
                            'notifiable' => $notifiable,
                            'notifier_id' => $notifier_id,
                            'notifications_message_id' => $this->getMessageId($notification_type)
                        ]);
                    }
                } else {
                    $this->updateNotification($notifier_id, $notification_id);
                }

                break;
            case 'Like':
                if ($for == 'T_follower') {
                    $notification_type = 'Like_t';
                } else {
                    $notification_type = 'Like_q';
                }

                $notification_id = $this->isUserAlreadyNotified($notifiable, $question_id, $notification_type);
                if ($notification_id < 0) {
                    if ($notifiable != $notifier_id) {
                        Notification::create([
                            'question_id' => $question_id,
                            'notifiable' => $notifiable,
                            'notifier_id' => $notifier_id,
                            'notifications_message_id' => $this->getMessageId($notification_type)
                        ]);
                    }
                } else {
                    $this->updateNotification($notifier_id, $notification_id);

                }
                break;
            case 'Answered':
                if ($for == 'T_follower') {
                    $notification_type = 'Answered_t';
                } else {
                    $notification_type = 'Answered_q';
                }
                $notification_id = $this->isUserAlreadyNotified($notifiable, $question_id, $notification_type);
                if ($notification_id < 0) {
                    if ($notifiable != $notifier_id) {
                        Notification::create([
                            'question_id' => $question_id,
                            'notifiable' => $notifiable,
                            'notifier_id' => $notifier_id,
                            'notifications_message_id' => $this->getMessageId($notification_type)
                        ]);
                    }
                } else {
                    $this->updateNotification($notifier_id, $notification_id);
                }
                break;
        }
    }


    /**
     * Return array of followers of a particlar question
     *
     * @param $question_id
     * @return mixed
     */
    public function followerOfQuestion($question_id)
    {
        return QuestionFollow::whereQuestionId($question_id)->get();
    }

    /**
     * @param $tag_id
     * @return mixed
     */
    public function followerOfTopic($tag_id)
    {
        return TopicFollow::whereTagId($tag_id)->get();
    }

    /**
     * Return true if user is follower of particular question
     *
     * @param $question_id
     * @param $user_id
     * @return bool
     */
    public function isQFollower($question_id, $user_id)
    {
        return QuestionFollow::whereQuestionId($question_id)->whereUserId($user_id)->exists();
    }

    /**
     * @param $tag_id
     * @param $user_id
     * @return bool
     */
    public function isTFollower($tag_id, $user_id)
    {
        return TopicFollow::whereUserId($user_id)->whereTagId($tag_id)->exists();
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function isUserEngagedEnough($user_id)
    {
        if (Notification::whereNotifiable($user_id)->where('updated_at', '>', Carbon::now()->subDay(1))->count() <= $this->min_number_of_notification_per_day) {
            return false;
        } else {
            return true;
        }
    }


    /**
     * Return notification_id if user is already notified, returns -1 if user is not notified
     *
     * @param $user_id
     * @param $question_id
     * @param $type
     * @return int
     */
    public function isUserAlreadyNotified($user_id, $question_id, $type)
    {
        $notification = Notification::whereNotifiable($user_id)->whereQuestionId($question_id)->whereNotificationsMessageId($this->getMessageId($type))->first();
        if (count($notification) > 0) {
            return $notification->id;
        }

        return -1;
    }

    /**
     * @param $type
     * @return mixed
     */
    public function getMessageId($type)
    {
        return NotificationMessage::whereType($type)->first()->id;
    }

    /**
     * @param $notifier_id
     * @param $notification_id
     */
    public function updateNotification($notifier_id, $notification_id)
    {
        $notification = Notification::whereId($notification_id)->first();
        if ($notification->notifiable != $notifier_id) {
            $notification->update([
                'updated_at' => Carbon::now(),
                'notifier_id' => $notifier_id,
                'count' => $notification->count + 1
            ]);
        }
    }

}
