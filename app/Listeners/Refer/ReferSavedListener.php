<?php

namespace App\Listeners\Refer;

use App\Models\Refer;
use App\Events\ReferSaved;
use App\Events\PremiumQuestion;
use App\Classes\Miscellaneous;

class ReferSavedListener
{
    public function handle(ReferSaved $event)
    {
//        $refers = Refer::displayableQuestions($event->refer->referred_to)->get();
//
//        if ($refers){
//            $data = $refers->reject(function ($refer){
//                return empty($refer->question);
//            })->map(function ($refer){
//                return $refer->question;
//            });
//
//            $myQuestion = count($data) ? Miscellaneous::getFormattedQuestionsForListing($data) : null;
//
//            $options = [
//                'cluster' => 'ap1',
//                'encrypted' => true
//            ];
//
//            $pusher = new Pusher(env('PUSHER_KEY'), env('PUSHER_SECRET'), env('PUSHER_APP_ID'), $options);
//
//            $data = [
//                'status' => 'success',
//                'myQuestion' => $myQuestion
//            ];
//
//            $pusher->trigger(['my-question'], PremiumQuestion::class, $data);
//        }
    }
}
