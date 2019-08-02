<?php

namespace App\Classes;

use App\Models\User;
use App\Mail\ExpertEmail;

class SendEmailToSpecialist
{
    /**
     * Send Mail Notification to User
     *
     * @param $data
     */
    public static function checkEmailNotificationType($data)
    {
        switch ($data->Message->type){
            case 'Refer-Expert':
                self::sendReferEmail($data);
                break;
            case 'Referrer':
                self::sendEmailToReferrer($data);
                break;
            case 'Audit':
                self::sendAuditedEmail($data);
                break;
            case 'Pending':
                self::sendPendingEmail($data);
                break;
            case 'Ex Follow Up':
                self::sendFollowUpEmail($data);
                break;
        }
    }

    /**
     * Send Email notification to specialist
     *
     * @param $data
     */
    public static function sendReferEmail($data)
    {
        $referred_by = User::find($data->notifier_id);
        $referred_to = User::find($data->notifiable);

        if (count($referred_by) && count($referred_to)) {
            $mail_data = [
                'id'          => $data->question_id,
                'referred_by' => $referred_by->f_name. ' ' .$referred_by->l_name,
                'user_id'     => $data->notifiable,
                'email'       => $referred_to->email,
                'sub' => 'New Referred Question!',
                'template' => 'referred'
            ];

            self::sendEmail($mail_data);
        }
    }


    /**
     * Send email to question referrer
     *
     * @param $data
     */
    public static function sendEmailToReferrer($data)
    {
        $mail_data = [
            'id' => $data->question_id,
            'email' => $data->question->email,
            'sub' => 'Your Referred Question Has Been Answered!',
            'template' => 'referrer'
        ];

        self::sendEmail($mail_data);
    }


    /**
     * Send Audit Email
     *
     * @param $data
     */
    public static function sendAuditedEmail($data)
    {
        $audit = $data->question->answer->audit;
        $auditedBy = User::find($data->question->answer->user_id);
        if (count($auditedBy)) {
            $mail_data = [
                'expert' => $audit->auditedBy->f_name,
                'expert_id' => $data->question->answer->user_id,
                'question_id' => $data->question->id,
                'email' => $auditedBy->email,
                'message' => $audit->message,
                'rating' => $audit->rating,
                'sub' => 'Your answer has been audited by an expert!',
                'template' => 'audit'
            ];

            self::sendEmail($mail_data);
        }
    }

    /**
     * Send Pending Question Email
     *
     * @param $data
     */
    public static function sendPendingEmail($data)
    {
        $mail_data  = [
            'email'   => $data->question->email,
            'questions'=> $data->question_id,
            'user_id' => $data->notifiable,
            'sub' => 'Pending Queries - Need Quick Response!',
            'template' => 'reminder'
        ];

        self::sendEmail($mail_data);
    }

    public static function sendFollowUpEmail($data)
    {
        $mail_data  = [
            'question_id'=> $data->question_id,
            'expert_id' => $data->notifiable,
            'email' => User::find($data->notifiable)->email,
            'sub' => 'Follow Up - Maya Apa User!',
            'template' => 'followup'
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
        if (filter_var($data['email'], FILTER_VALIDATE_EMAIL) == true) {
            \Mail::to($data['email'])->queue(new ExpertEmail($data));
        }
    }

}
