<?php

namespace App\Classes;

use App\Models\User;
use App\Mail\UserEmail;
use App\Models\Question;

class SendEmailToUser
{
    /**
     * Check eMail Notification type
     *
     * @param $data
     */
    public static function checkEmailNotificationType($data)
    {
        switch ($data->Message->type){
//            case 'Refer':
//                self::sendReferEmail($data);
//                break;
//            case 'Spam':
//                self::sendSpamEmail($data);
//                break;
            case 'Answered':
                self::sendAnsweredEmail($data);
                break;
//            case 'Updated':
//                self::sendAnswerUpdatedEmail($data);
//                break;
//            case 'Block':
//                self::sendBlockEmail($data);
//                break;
//            default:
//                echo 'Something went wrong';
        }
    }

    /**
     * Send mail to the questioner
     *
     * @param $data
     */
    public static function sendReferEmail($data)
    {
        $user = User::find($data->question->user_id);
        if (count($user)) {
            $data = [
                'id' => $data->question->id,
                'email' => $user->email,
                'sub' => 'Your Question has been referred!',
                'template' => 'questioner'
            ];

            self::sendEmail($data);
        }
    }

    /**
     * Send spam email to user
     *
     * @param $data
     */
    public static function sendSpamEmail($data)
    {
        $spamCount = Question::whereUserId($data->question->user_id)->whereStatus('spam')->count();
        $user = User::find($data->notifiable);
        if (count($user)) {
            $mail_data = [
                'user_id' => $user->id,
                'name' => $user->f_name . ' ' . $user->l_name,
                'q_id' => $data->question->id,
                'email' => $user->email,
                'totalSpam' => $spamCount,
                'sub' => 'Spam Question!',
                'template' => 'spam'
            ];

            self::sendEmail($mail_data);
        }
    }

    /**
     * Send answered email to user
     *
     * @param $data
     */
    public static function sendAnsweredEmail($data)
    {
        $user = User::find($data->question->user_id);
        if (count($user)) {
            $mail_data = [
                'id' => $data->question->id,
                'name' => 'Maya Apa User',
                'email' => $user->email,
                'sub' => 'Question Answered!',
                'template' => 'answered'
            ];

            self::sendEmail($mail_data);
        }
    }


    /**
     * Send answered email to user
     *
     * @param $data
     */
    public static function sendAnswerUpdatedEmail($data)
    {
        $user = User::find($data->question->user_id);
        if (count($user)) {
            $mail_data = [
                'id' => $data->question->id,
                'email' => $user->email,
                'sub' => 'Updated Answer!',
                'template' => 'updated'
            ];

            self::sendEmail($mail_data);
        }
    }

    /**
     * Send Block Email to User
     *
     * @param $data
     */
    public static function sendBlockEmail($data)
    {
        $spamCount = Question::whereUserId($data->question->user_id)->whereStatus('spam')->count();
        $mail_data = [
            'email' => $data->question->email,
            'totalSpam' => $spamCount,
            'sub' => 'You are blocked!',
            'template' => 'block'
        ];

        self::sendEmail($mail_data);
    }

    /**
     * Send Email
     *
     * @param $data
     */
    public static function sendEmail($data)
    {
        if(filter_var($data['email'], FILTER_VALIDATE_EMAIL) == true){
            \Mail::to($data['email'])->send(new UserEmail($data));
        };
    }

}
