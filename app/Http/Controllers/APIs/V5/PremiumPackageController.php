<?php

namespace App\Http\Controllers\APIs\V5;

use App\Classes\MakeResponse;
use App\Classes\Miscellaneous;
use App\Models\PremiumFeature;
use App\Models\PremiumPackage;
use App\Models\PremiumPayment;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PremiumPackageController extends Controller
{

    private function checkIsTrialFinished($userId)
    {
        if (empty($userId)) return false;

        $trialPackage = PremiumPackage::wherePrice(0)->first();

        if (!count($trialPackage)) return false;

        return PremiumPayment::whereUserId($userId)->whereStatus('expired')->wherePackageId($trialPackage->id)->exists();
    }

    public function getPremiumStatus_v5($userId)
    {
        try{
            $quiz_sql = \DB::select("SELECT * FROM quizzes q JOIN ( SELECT (quiz_group_id + 1) AS grp_id FROM quiz_users WHERE user_id = ".$userId." AND ( deleted_at = '' OR deleted_at IS NULL ) ORDER BY quiz_group_id DESC LIMIT 1 ) d ON q.group_id = d.grp_id");

            Miscellaneous::storeActiveUser($userId, request()->header('version'));

            $canAsk = 1; $isPrescription = false; $averageTime = '30-60' ; $phoneNumber = '';
            $user = User::find($userId);
            $payment = PremiumPayment::with(['premiumPackage'])
                ->whereUserId($userId)
                ->whereIn('status', array('active', 'free_premium'))
                ->orderByRaw("CASE WHEN package_id <> 6 THEN 0 ELSE 1 END, package_id")
                ->first();

            if (count($payment)) {
                $packageConfig = config("admin.package.$payment->package_id");
                $questionCount = $payment->question_count;
                $questionCap = $payment->premiumPackage->question_cap;
                $isQuestionCapFinished = $questionCap == 0 ? false : $questionCap <= $questionCount;
                $canAsk = $payment->premiumPackage->question_cap - $questionCount;
                if($payment->package_id < 6) $features = $this->getFeatureList($payment->package_id);
                elseif($payment->package_id == 8) $features = $this->getFeatureList(5);
                elseif($payment->package_id == 9) $features = $this->getFeatureList(9);
                elseif($payment->package_id == 7) $features = $this->getFeatureList(7);
                else $features = [];

                if ($payment->premiumPackage->isPrescription()){
                    $isPrescription = true;
                    $payment->premiumPackage->title_en = $packageConfig['title_en'];
                    $payment->premiumPackage->title_bn = $packageConfig['title_bn'];
                    $payment->premiumPackage->subtitle_en = $packageConfig['subtitle_en'];
                    $payment->premiumPackage->subtitle_bn = $packageConfig['subtitle_bn'];
                    $averageTime = $packageConfig['average_time'];
                    $phoneNumber = $packageConfig['phone_number'];
                }
            } else {
                $startOfMonth = Carbon::now()->startOfMonth();
                $endOfMonth = Carbon::now()->endOfMonth();
                $questionCount = Question::whereUserId($userId)->whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
                $isQuestionCapFinished = 30 <= $questionCount;
                $features = null;
            }

            $isTrialFinished = $this->checkIsTrialFinished($userId);

            $isPremium = $user->is_premium;

            $five_question_premium = Question::where('user_id', $userId)->get();

            if (($five_question_premium->count() < 5) || ($isPremium == 1 && $payment->question_count < $packageConfig['limit'])) {
                $can_ask_premium = 1;
            }

            return response()->json([
                'status' => 'success',
                'data' => $payment,
                'is_premium' => $isPremium,
                'is_trial_finished' => $isTrialFinished,
                'is_question_cap_finished' => $isQuestionCapFinished,
                'can_ask' => $canAsk,
                'can_ask_premium' => $can_ask_premium ?? '',
                'question_count' => $questionCount,
                'features' => $features,
                'fifty_percent_discount_code' => [],
//                $this->fiftyPercentDiscountCode($userId),
                'is_prescription' => $isPrescription,
                'phone_number' => $phoneNumber,
                'average_time' => $averageTime,
                'is_quiz_available' => (count($quiz_sql) > 0) ? 1 : 0,
                'error_code' => 0,
                'error_message' => '',
            ]);
        } catch (\Exception $exception){
            return 0;
        }
    }

    private function getFeatureList($packageId)
    {
        return PremiumFeature::select(['id', 'name'])->where("p{$packageId}", 1)->get();
    }

}
