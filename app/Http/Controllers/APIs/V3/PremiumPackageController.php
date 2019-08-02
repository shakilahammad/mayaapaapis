<?php

namespace App\Http\Controllers\APIs\V3;

use App\Models\PromoNotification;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Question;
use App\Models\PremiumCoupon;
use App\Classes\MakeResponse;
use App\Models\PremiumPackage;
use App\Models\PremiumPayment;
use App\Models\PremiumFeature;
use App\Classes\Miscellaneous;
use App\Models\PremiumCouponApplied;
use App\Http\Controllers\Controller;

class PremiumPackageController extends Controller
{
    public function getPackages($userId = null, $nextPurchase = 0)
    {
        $isTrialFinished = $this->checkIsTrialFinished($userId);

        if ($nextPurchase > 0){
            $this->nextPurchase($userId);
        }

        if ($isTrialFinished) {
            $packages = PremiumPackage::where('price', '>', 0)->whereStatus('active')->where('type', '<>', 'chat')->get();
        } else {
            $packages = PremiumPackage::whereStatus('active')->where('type', '<>', 'chat')->get();
        }

        $premiumPackages = $this->transformPackages(
            $packages,
            $userId
        );

        usort($premiumPackages, function ($a, $b) {
            return $b['is_taken'] - $a['is_taken'];
        });

        return MakeResponse::successResponse($premiumPackages);
    }

    public function getPackages_v3($userId = null, $nextPurchase = 0)
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

    public function getPackages_multisource($userId = null, $phone = null,  $nextPurchase = 0)
    {

        if ($nextPurchase > 0){
            $this->nextPurchase($userId);
        }

        $packages = PremiumPackage::where('price', '>', 0)->where('type', 'telco')->get();

        $premiumPackages = $this->transformPackages_multisource(
            $packages,
            $userId
        );

        usort($premiumPackages, function ($a, $b) {
            return $b['is_taken'] - $a['is_taken'];
        });

        return MakeResponse::successResponse($premiumPackages);
    }

    public function getChatPackage($userId = null, $nextPurchase = 0)
    {
        $isTrialFinished = $this->checkIsTrialFinished($userId);

        if ($nextPurchase > 0){
            $this->nextPurchase($userId);
        }

        if ($isTrialFinished) {
            $packages = PremiumPackage::where('price', '>', 0)->whereStatus('active')->whereType('chat')->get();
        } else {
            $packages = PremiumPackage::whereStatus('active')->whereType('chat')->get();
        }

        $premiumPackages = $this->transformPackages(
            $packages,
            $userId
        );

        usort($premiumPackages, function ($a, $b) {
            return $b['is_taken'] - $a['is_taken'];
        });

        return MakeResponse::successResponse($premiumPackages);
    }

    public function getPackageList($userId = null, $nextPurchase = 0)
    {
        $isTrialFinished = $this->checkIsTrialFinished($userId);

        if ($nextPurchase > 0){
            $this->nextPurchase($userId);
        }

        if ($isTrialFinished) {
            $packages = PremiumPackage::where('price', '>', 0)->where('id', '>', 1)->where('status','<>','inactive')->get();
        } else {
            $packages = PremiumPackage::where('status','<>','inactive')->where('id', '>', 1)->get();
        }

        $dt = new \App\Http\Controllers\APIs\V4\PremiumPackageController();
        $premiumPackages = $dt->transformPackageList(
            $packages,
            $userId
        );
//        $premiumPackages = $this->transformPackageList(
//            $packages,
//            $userId
//        );

        usort($premiumPackages, function ($a, $b) {
            return $b['is_taken'] - $a['is_taken'];
        });

        return MakeResponse::successResponse($premiumPackages);
    }

    private function nextPurchase($userId)
    {
        if (!empty($userId)){
            $coupon = PremiumCoupon::whereCode('RETURNINGUSER50')->where('expiry_time', '>', Carbon::now())->first();

            if (count($coupon)) {
                PremiumCouponApplied::updateOrCreate([
                    'user_id' => $userId,
                    'coupon_id' => $coupon->id
                ]);
            }
        }
    }

    public function getPremiumStatus($userId)
    {
        Miscellaneous::storeActiveUser($userId, request()->header('version'));

        $canAsk = 1; $isPrescription = false; $averageTime = '30-60' ; $phoneNumber = '';
        $user = User::find($userId);
        $payment = PremiumPayment::with(['premiumPackage'])->whereUserId($userId)->whereStatus('active')->first();

        if (count($payment)) {
            $questionCount = $payment->question_count;
            $questionCap = $payment->premiumPackage->question_cap;
            $isQuestionCapFinished = $questionCap == 0 ? false : $questionCap <= $questionCount;
            $canAsk = $payment->premiumPackage->question_cap - $questionCount;
            if($payment->package_id < 6) $features = $this->getFeatureList($payment->package_id);
            elseif($payment->package_id == 8) $features = $this->getFeatureList(5);
            else $features = null;

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
            $isQuestionCapFinished = 1 <= $questionCount;
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
            'fifty_percent_discount_code' => $this->fiftyPercentDiscountCode($userId),
            'is_prescription' => $isPrescription,
            'phone_number' => $phoneNumber,
            'average_time' => $averageTime,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    private function fiftyPercentDiscountCode($userId)
    {
        $now = Carbon::now();

        $returningPromo = PremiumCoupon::select(['id', 'code', 'discount', 'max_discount'])->where([
             'code' => 'RETURNINGUSER50'
        ])->first();

        $applied = PremiumCouponApplied::withTrashed()->whereCouponId($returningPromo->id)->whereUserId($userId)->first();

        if (count($applied) && $applied->trashed()) return [];

        $appliedPromo = \DB::select("select pc.id, pc.code, pc.discount as discount, pc.max_discount as max_discount, pca.created_at as apply_time, pca.deleted_at from premium_coupons pc, premium_coupon_applied pca where pc.id = pca.coupon_id and pca.user_id = {$userId} and pc.expiry_time > '{$now}'");

        $data = collect($appliedPromo)
                      ->filter(function ($promo) use ($returningPromo){
                            return $promo->discount >= $returningPromo->discount;
                      });

        if (count($data)) return [];

        return [$returningPromo];
    }

    private function getFeatureList($packageId)
    {
        return PremiumFeature::select(['id', 'name'])->where("p{$packageId}", 1)->get();
    }

    private function checkIsTrialFinished($userId)
    {
        if (empty($userId)) return false;

        $trialPackage = PremiumPackage::wherePrice(0)->first();

        if (!count($trialPackage)) return false;

        return PremiumPayment::whereUserId($userId)->whereStatus('expired')->wherePackageId($trialPackage->id)->exists();
    }

    private function transformPackages($packages, $userId)
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
    public function transformPackages_multisource($packages, $userId)
    {
        $subscribed = PremiumPayment::whereUserId($userId)->whereStatus('active')->first();

        return $packages->map(function ($package) use ($userId, $subscribed) {
            if(!is_null($subscribed)&&$subscribed->package_id == $package->id)
                return [
                    'id' => $package->id,
                    'name_en' => $package->name_en,
                    'name_bn' => $package->name_bn,
                    'desc_en' => $package->desc_en,
                    'desc_bn' => $package->desc_bn,
                    'price' => $package->price,
                    'discount_price' => "0",
                    'days' => $package->days,
                    'status' => $package->status,
                    'type' => $package->type,
                    'subscribe_button' => 'active',
                    'free_premium' => 0,
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
                    'discount_price' => "0",
                    'days' => $package->days,
                    'status' => $package->status,
                    'type' => $package->type,
                    'subscribe_button' => ($package->status=='coming_soon') ? 'upcoming' : 'on',
                    'free_premium' => 0,
                    'question_cap' => $package->question_cap,
                    'conditions_en' => $package->conditions_en,
                    'conditions_bn' => $package->conditions_bn,
                    'is_taken' => $this->isPackageTaken($userId, $package->id),
                    'color' => config("admin.package.$package->id")['color'] ?? '#000000',
                    'created_at' => $this->formattedTime($package)
                ];
        })->toArray();
    }
    private function transformPackageList($packages, $userId)
    {
        $appliedPromo = $this->getValidAppliedPromo($userId);

        $subscribed = PremiumPayment::whereUserId($userId)->whereIn('status', array('active', 'free_premium'))->first();

        return $packages->map(function ($package) use ($userId, $appliedPromo, $subscribed) {
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
                    'question_cap' => $package->question_cap,
                    'conditions_en' => $package->conditions_en,
                    'conditions_bn' => $package->conditions_bn,
                    'is_taken' => $this->isPackageTaken($userId, $package->id),
                    'color' => config("admin.package.$package->id")['color'] ?? '#000000',
                    'created_at' => $this->formattedTime($package)
                ];
        })->toArray();
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

    private function getValidAppliedPromo($userId)
    {
        if (empty($userId)) return null;

        $now = Carbon::now();
        $promo = \DB::select("select pc.id, pc.discount as discount, pc.max_discount as max_discount, pca.created_at as apply_time from premium_coupons pc, premium_coupon_applied pca where pc.id = pca.coupon_id and pca.user_id = {$userId} and pca.deleted_at is NULL and pc.expiry_time > '{$now}' order by pca.created_at asc limit 0, 1");

        return $promo[0] ?? null;
    }

    private function getDiscountedPrice($package, $promo)
    {
        if (empty($promo)) return 0;

        $discount = (int)($promo->discount * $package->price) / 100;

        if ($discount >= $promo->max_discount && $promo->max_discount > 0) {
            return ceil($package->price - $promo->max_discount) . ".00";
        }

        return ceil($package->price - $discount) . ".00";
    }

    public function getPaymentHistory($userId)
    {
        $premiumHistory = PremiumPayment::with(['premiumPackage'])
                                        ->whereIn('status', ['active', 'expired'])
                                        ->whereUserId($userId)
                                        ->orderBy('created_at', 'desc')->get();

        if (count($premiumHistory)) {
            return MakeResponse::successResponse($premiumHistory);
        }

        return MakeResponse::errorResponse('Payment history not found!');
    }

    public function unsubscribe($user_id, $package_id){
        $package = PremiumPayment::where('user_id', $user_id)->where('package_id', $package_id)->whereIn('status', ['active','free_premium'])->first();
        if(count($package)){
            $package->status = 'expired';
            $package->expiry_time = Carbon::now();
            $package->save();
            $user = User::find($user_id);
            $user->is_premium = 0;
            $user->save();

            return MakeResponse::successResponse($package);
        }

        return MakeResponse::errorResponse('Invalid entry.');
    }

}
