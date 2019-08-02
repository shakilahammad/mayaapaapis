<?php

namespace App\Http\Controllers\APIs\V2;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Question;
use App\Classes\MakeResponse;
use App\Models\PremiumPayment;
use App\Http\Controllers\Controller;

class UserStatusController extends Controller
{
    public function index($userId)
    {
        $payment = PremiumPayment::with(['premiumPackage'])->whereUserId($userId)->whereStatus('active')->first();

        if (count($payment)) {
            $packageConfig = config("admin.package.$payment->package_id");

            $limit = $packageConfig['limit'];

            $data = [
                'package_name_en' => $payment->premiumPackage->name_en,
                'package_name_bn' => $payment->premiumPackage->name_bn,
                'expiry_date' => $payment->expiry_time,
                'asked_premium' => $payment->question_count > $limit ? $limit : $payment->question_count,
                'left_premium' => $payment->question_count >= $limit ? 0 : $limit - $payment->question_count,
//                'premium_tat' => $this->getTAT($payment, 1),
//                'nonpremium_tat' => $this->getTAT($payment, 1),
                'premium_tat' => $packageConfig['minute'],
                'nonpremium_tat' => $packageConfig['max']/60,
                'weekly_question_count' => $this->getWeeklyQuestionCount($payment->user_id),
                'isChatActivated' => $this->checkChatSubscription($payment->user_id),
                'get_consultent_by' => $this->getConsultentCount($payment->user_id)
            ];

            return MakeResponse::successResponse($data);
        }

        return MakeResponse::errorResponse('Payment history not found!');
    }

    private function getWeeklyQuestionCount($user_id)
    {
        $now = Carbon::now();
        $weekOne = Carbon::now()->subWeek();
        $weekTwo = Carbon::parse($weekOne)->subWeek(1);
        $weekThree = Carbon::parse($weekTwo)->subWeek();
        $weekFour = Carbon::parse($weekThree)->subWeek();

        $week[0] = \DB::select("SELECT count(*) as total FROM questions WHERE user_id = {$user_id} AND created_at BETWEEN '{$weekOne}' AND '{$now}' GROUP BY week(created_at)");
        $week[1] = \DB::select("SELECT count(*) as total FROM questions WHERE user_id = {$user_id} AND created_at BETWEEN '{$weekTwo}' AND '{$weekOne}' GROUP BY week(created_at)");
        $week[2] = \DB::select("SELECT count(*) as total FROM questions WHERE user_id = {$user_id} AND created_at BETWEEN '{$weekThree}' AND '{$weekTwo}' GROUP BY week(created_at)");
        $week[3] = \DB::select("SELECT count(*) as total FROM questions WHERE user_id = {$user_id} AND created_at BETWEEN '{$weekFour}' AND '{$weekThree}' GROUP BY week(created_at)");

        return $this->processWeekData($week);
    }

    private function processWeekData($weekData)
    {
        $results = [];
        foreach ($weekData as $key => $data) {
            $array = [
                "week" => empty($data) ? 0 : $data[0]->total
            ];

            array_push($results, $array);
        }

        return $results;
    }

    private function getConsultentCount($userId)
    {
        $results =  \DB::select("select count(distinct a.user_id) as total from questions q, answers a where q.id = a.question_id and q.user_id = {$userId} and q.status = 'answered'");
        return $results[0]->total;
    }

    private function getTAT($payment, $type)
    {
        $limit = config("admin.package.$payment->package_id.limit");
        $offset = 0;
        if ($type === 0) {
            $offset = $limit;
            $limit = $payment->question_count;
        }

        $tat = \DB::select("SELECT avg(TIMESTAMPDIFF(MINUTE, q.created_at, a.created_at)) as tat FROM questions q, answers a where q.id = a.question_id and q.is_premium = {$type} AND q.user_id = {$payment->user_id} and q.created_at between '{$payment->effective_time}' and '{$payment->expiry_time}' LIMIT {$offset}, {$limit}");

        return $tat[0]->tat ?? 0;
    }

    public function questionCapCount($userId)
    {
        try {
            if (!User::whereId($userId)->count()){
                return MakeResponse::errorResponse('No user found with this id!');
            }

            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $questionCount = Question::whereUserId($userId)->whereIsPremium(0)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            return MakeResponse::successResponse([
                'question_count' => $questionCount
            ]);
        }catch (\Exception $exception){
            return MakeResponse::errorResponse('Something went wrong!');
        }
    }

    public function checkChatSubscription($userId){
        try {
            if (!User::whereId($userId)->count()){
                return MakeResponse::errorResponse('No user found with this id!');
            }

            $chatActivated = PremiumPayment::whereUserId($userId)->whereStatus('Active')->first();
            dd($chatActivated);
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $questionCount = Question::whereUserId($userId)->whereIsPremium(0)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();

            return MakeResponse::successResponse([
                'question_count' => $questionCount
            ]);
        }catch (\Exception $exception){
            return MakeResponse::errorResponse('Something went wrong!');
        }
    }
}

