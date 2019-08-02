<?php

namespace App\Listeners\Question;

use App\Models\Question;
use App\Classes\Miscellaneous;
use App\Models\PremiumPayment;
use App\Events\QuestionWasCreated;
use App\Models\PremiumQuestionQueue;
use Illuminate\Support\Facades\Log;

class RealTimeUpdate
{
    public function handle(QuestionWasCreated $event)
    {
        try{
            $this->realtimeUpdate($event->question);
        }catch (\Exception $exception){
//            \Log::emergency('Realtime Updtae: ' .' '. $exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }

    private function realtimeUpdate($question)
    {
        if ($question->status = 'pending') {
            $fetchQuestion = Question::whereId($question->id)->whereIsPremium(1)->get();
            if (count($fetchQuestion)) {
                $this->segregate(
                    $this->getPackage($fetchQuestion[0]->user_id),
                    $fetchQuestion
                );
            }
        }
    }

    private function getPackage($userId)
    {
        return PremiumPayment::whereUserId($userId)->whereIn('status', ['active', 'free_premium'])->first();
    }

    private function segregate($payment, $question)
    {
        $limit = 0; $minute = 0;
        if (isset($payment) && count($payment)) {
            $packageInfo = config("admin.package.$payment->package_id");
            $limit = $packageInfo['limit'];
            if($payment->package_id==5 && $payment->status=='free_premium')
                $minute = config("admin.package.6.minute"); // Free premium prescription question push into 30 minute queue
            else
                $minute = $packageInfo['minute'];
        } else {
            $payment = null;
        }

        $this->pushToQueue($question, $payment, $limit, $minute);
    }

    private function pushToQueue($question, $package, $limit, $minute)
    {
        $is_paid = isset($package) && $package->amount > 0 ? true : false;
        $question_count = $this->questionCount($question[0]->user_id);

        if (!empty($package)) {
            $this->checkTimeAndCount($question, $package, $limit)
                ? $this->triggerPusher($question, $minute, $is_paid)
                : $this->triggerPusher($question, 91, $is_paid);
        } elseif (empty($package) && $question_count <= 2) {
            $this->triggerPusher($question, 30);
        } elseif (empty($package) && $question_count <= 5) {
            $this->triggerPusher($question, 90);
        } elseif ($question[0]->source == 'kiosk') {
            $this->triggerPusher($question, 10, true);
        } else {
            $this->triggerPusher($question);
        }
    }

    private function questionCount($user_id){
        $questionCount = \DB::select("SELECT count(*) as count FROM  questions WHERE user_id = {$user_id} AND deleted_at is null");

        return $questionCount[0]->count;
    }

    private function checkTimeAndCount($question, $package, $count)
    {
        $start = '08:00:00';
        $end = '20:00:00';
        $time = $question[0]->created_at->format('H:i:s');
        $questionCount = \DB::select("SELECT count(*) as count FROM  questions WHERE user_id = {$question[0]->user_id} AND created_at BETWEEN '{$package->effective_time}' AND '{$package->getOriginal('expiry_time')}' AND HOUR(created_at) BETWEEN 08 AND 19");

        return $questionCount[0]->count <= $count && $time >= $start && $time <= $end;
    }

    private function triggerPusher($question, $type = 91, $is_paid = false)
    {
        $type = $this->getWordsFromNumber($type);
        $this->createQueue($question[0], $type);

        Miscellaneous::realtimeUpdate($question, $type, $is_paid);
    }

    private function createQueue($question, $limit)
    {
        PremiumQuestionQueue::updateOrCreate(
            ['question_id' => $question->id],
            ['limit' => $limit, 'status' => $question->status]
        );
    }

    private function getWordsFromNumber($number)
    {
        if ($number === 10) {
            return 'ten';
        } elseif ($number === 30) {
            return 'thirty';
        } elseif ($number === 90) {
            return 'ninety';
        } else {
            return 'others';
        }
    }

}
