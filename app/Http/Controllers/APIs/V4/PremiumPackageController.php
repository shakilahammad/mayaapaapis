<?php

namespace App\Http\Controllers\APIs\V4;

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

    // TODO: declare fixed values for packages
    private $discounted_package_prices = [
        "Maya Plus 7-days free trial" => 0,
        "Maya Prescription" => 199,
        "Maya Alap" => 199,
        "Maya Mix" => 129,
//        "Maya Bot" => 0,
        "Maya Shuru" => 99
    ];

    private function getValidAppliedPromo($userId)
    {
        if (empty($userId)) return null;

        $now = Carbon::now();

        // TODO: take promo applied which has the maximum discount
        $promo = \DB::select("select pc.id, pc.discount as discount, pc.max_discount as max_discount, pca.created_at as apply_time from premium_coupons pc, premium_coupon_applied pca where pc.id = pca.coupon_id and pca.user_id = {$userId} and pca.deleted_at is NULL and pc.expiry_time > '{$now}' order by pc.discount desc limit 0, 1");

        return $promo[0] ?? null;
    }

    private function checkIsTrialFinished($userId)
    {
        if (empty($userId)) return false;

        $trialPackage = PremiumPackage::wherePrice(0)->first();

        if (!count($trialPackage)) return false;

        return PremiumPayment::whereUserId($userId)->whereStatus('expired')->wherePackageId($trialPackage->id)->exists();
    }

    public function getPackages_v4($userId = null, $nextPurchase = 0)
    {
        $isTrialFinished = $this->checkIsTrialFinished($userId);

        if ($nextPurchase > 0){
            $this->nextPurchase($userId);
        }

        if ($isTrialFinished) {
            $packages = PremiumPackage::where('price', '>', 0)->where('status', '<>', 'inactive')->get();
        } else {
            $packages = PremiumPackage::where('status', '<>', 'inactive')->get();
        }

        $premiumPackages = $this->transformPackageList(
            $packages,
            $userId
        );

        usort($premiumPackages, function ($a, $b) {
            return $b['is_taken'] - $a['is_taken'];
        });

        return MakeResponse::successResponse($premiumPackages);
    }

    public function transformPackages($packages, $userId)
    {
        $appliedPromo = $this->getValidAppliedPromo($userId);

        return $packages->map(function ($package) use ($userId, $appliedPromo) {
            return [
                'id' => $package->id,
                'name_en' => $package->name_en,
                'name_bn' => $package->name_bn,
                'desc_en' => $package->desc_en,
                'desc_bn' => $package->desc_bn,
                'price' => $package->price,
                'discount_price' => (string)$this->getDiscountedPrice($package, $appliedPromo),
                'days' => $package->days,
                'status' => $package->status,
                'type' => $package->type,
                'question_cap' => $package->question_cap,
                'conditions_en' => $package->conditions_en,
                'conditions_bn' => $package->conditions_bn,
                'is_taken' => $this->isPackageTaken($userId, $package->id),
                'color' => config("admin.package.$package->id")['color'] ?? '#000000',
                'created_at' => $this->formattedTime($package)
            ];
        })->toArray();
    }

    public function transformPackageList($packages, $userId)
    {
        $appliedPromo = $this->getValidAppliedPromo($userId);

        return $packages->map(function ($package) use ($userId, $appliedPromo) {

            $subscribed = PremiumPayment::whereUserId($userId)->wherePackageId($package->id)->whereIn('status', array('active', 'free_premium'))->first();

            if(!is_null($subscribed)&&$subscribed->package_id == $package->id)
                return [
                    'id' => $package->id,
                    'name_en' => $package->name_en,
                    'name_bn' => $package->name_bn,
                    'desc_en' => $package->desc_en,
                    'desc_bn' => $package->desc_bn,
                    'price' => $package->price,
                    'discount_price' => (string)$this->getDiscountedPrice($package, $appliedPromo),
                    'days' => $package->days,
                    'status' => $package->status,
                    'type' => $package->type,
                    'subscribe_button' => 'active',
                    'free_premium' => $this->getFreePremiumStatus($package, $userId),
                    'question_cap' => $package->question_cap,
                    'conditions_en' => $package->conditions_en,
                    'conditions_bn' => $package->conditions_bn,
                    'is_taken' => $this->isPackageTaken($userId, $package->id),
                    'color' => config("admin.package.$package->id")['color'] ?? '#000000',
                    'created_at' => $this->formattedTime($package)
                ];
            else
                return [
                    'id' => $package->id,
                    'name_en' => $package->name_en,
                    'name_bn' => $package->name_bn,
                    'desc_en' => $package->desc_en,
                    'desc_bn' => $package->desc_bn,
                    'price' => $package->price,
                    'discount_price' => (string)$this->getDiscountedPrice($package, $appliedPromo),
                    'days' => $package->days,
                    'status' => $package->status,
                    'type' => $package->type,
                    'subscribe_button' => ($package->status=='coming_soon') ? 'upcoming' : 'on',
                    'free_premium' => $this->getFreePremiumStatus($package, $userId),
                    'question_cap' => $package->question_cap,
                    'conditions_en' => $package->conditions_en,
                    'conditions_bn' => $package->conditions_bn,
                    'is_taken' => $this->isPackageTaken($userId, $package->id),
                    'color' => config("admin.package.$package->id")['color'] ?? '#000000',
                    'created_at' => $this->formattedTime($package)
                ];
        })->toArray();
    }

    private function getDiscountedPrice($package, $promo)
    {
        // TODO: if promo is empty then return the values from the desired $package
        $discounted_package_price = $this->discounted_package_prices[$package->name_en];

        if(empty($promo)) {
//            dd($package);
            return $discounted_package_price;

        }

        // TODO: get price after discount
        $discount = (int) ( $package->price - ($package->price * $promo->discount)/100);


        // TODO: check if the discounted amount is bigger than our fixed values of packages
        if($discount <= $discounted_package_price){
            return $discount . ".00";
        }

        return $discounted_package_price . ".00";

    }

    private function getFreePremiumStatus($package, $userId)
    {
        try{
            if($package->id==5) return 0;
            else{
                $free_premium = PremiumPayment::whereUserId($userId)->wherePackageId($package->id)->where('status', '<>', 'pending')->first();
                return is_null($free_premium) ? (int)floor(($package->days*0.3)<1 ? 1 : ($package->days*0.3)) : 0;
            }
        } catch (\Exception $exception){
            return 0;
        }
    }

    public function getPremiumStatus_v4($userId)
    {
        try{
//            $sql = \DB::select("SELECT count(*) FROM quizzes WHERE group_id NOT IN ( SELECT quiz_group_id FROM quiz_users WHERE user_id = $userId ) AND quizzes.deleted_at IS NULL GROUP BY group_id");

            Miscellaneous::storeActiveUser($userId, request()->header('version'));

            $canAsk = 1; $isPrescription = false; $averageTime = '30-60' ; $phoneNumber = '';
            $user = User::find($userId);
            $payment = PremiumPayment::with(['premiumPackage'])
                ->whereUserId($userId)
                ->whereIn('status', array('active', 'free_premium'))
                ->orderByRaw("CASE WHEN package_id <> 6 THEN 0 ELSE 1 END, package_id")
                ->first();

            if (count($payment)) {
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
                    $packageConfig = config("admin.package.$payment->package_id");
                    $isPrescription = true;
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

            return response()->json([
                'status' => 'success',
                'data' => $payment,
                'is_premium' => $isPremium,
                'is_trial_finished' => $isTrialFinished,
                'is_question_cap_finished' => $isQuestionCapFinished,
                'can_ask' => $canAsk,
                'question_count' => $questionCount,
                'features' => $features,
                'fifty_percent_discount_code' => [],
//                $this->fiftyPercentDiscountCode($userId),
                'is_prescription' => $isPrescription,
                'phone_number' => $phoneNumber,
                'average_time' => $averageTime,
//                'is_quiz_available' => ($sql>0) ? 1 : 0,
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

    private function isPackageTaken($userId, $packageId)
    {
        if (empty($userId)) return false;

        return PremiumPayment::whereUserId($userId)->wherePackageId($packageId)->whereStatus('active')->exists();
    }


    private function formattedTime($package)
    {
        return Carbon::parse($package->created_at)->format('d M Y');
    }

//    private function setFreePremium($user_id, $package_id)
//    {
//
//        return MakeResponse::successResponse($premiumPackages);
//    }

}
