<?php

namespace App\Http\Controllers\ApiV1;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Question;
use App\Models\Location;
use App\Models\PremiumUser;
use App\Models\PremiumCoupon;
use App\Classes\MakeResponse;
use App\Models\PremiumPackage;
use App\Models\PremiumPayment;
use App\Models\PremiumFeature;
use App\Classes\Miscellaneous;
use App\Http\Controllers\Controller;

class PremiumPackageController extends Controller
{
    public function getPackageList($userId = null)
    {
        $isTrialFinished = $this->checkIsTrialFinished($userId);

        if ($isTrialFinished) {
            $packages = PremiumPackage::where('price', '>', 0)->notPrescription()->whereStatus('active')->get();
        } else {
            $packages = PremiumPackage::whereStatus('active')->notPrescription()->get();
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

    public function getCustomPackageList($userId = null)
    {
        $isTrialFinished = $this->checkIsTrialFinished($userId);

        if ($isTrialFinished) {
            $packages = PremiumPackage::whereRaw("price > 0 AND type = 'telco'")->orWhereRaw("price > 0 AND status = 'active'")->get();
        } else {
            $packages = PremiumPackage::whereRaw("price > 0 AND type = 'telco'")->orWhereRaw("price > 0 AND status = 'active'")->get();
        }

        $premiumPackages = $this->transformCustomPackages(
            $packages,
            $userId
        );

        usort($premiumPackages, function ($a, $b) {
            return $b['is_taken'] - $a['is_taken'];
        });

        return MakeResponse::successResponse($premiumPackages);
    }

    public function getPackageListOld($userId = null)
    {
        $isTrialFinished = $this->checkIsTrialFinished($userId);

        if ($isTrialFinished) {
            $packages = PremiumPackage::where('price', '>', 0)->notPrescription()->whereStatus('active')->get();
        } else {
            $packages = PremiumPackage::whereStatus('active')->notPrescription()->get();
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

    public function fetchPremiumUserInfo($userId)
    {
        $premiumUser = PremiumUser::whereUserId($userId)->first();

        if (count($premiumUser)) {
            return MakeResponse::successResponse($premiumUser);
        } else {
            $user = User::whereId($userId)->first();
            if (strpos($user->f_name, 'nymous') == false) $premiumUser['name'] = $user->f_name . ' ' . $user->l_name;
            else $premiumUser['name'] = '';
            $premiumUser['email'] = (!empty($user['email'])) ? $user->email : '';
            $premiumUser['phone'] = (!empty($user['phone'])) ? $user->phone : '';
            if ($user->location_id != 0) {
                $loc = Location::whereId($user->location_id)->first();
                if (!empty($loc->lat) && !empty($loc->long) && empty($loc->city)) {
                    $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($loc->lat) . ',' . trim($loc->long) . '&sensor=false';
                    $json = @file_get_contents($url);
                    $data_json = json_decode($json);
                    if ($data_json->status == 'OK') {
                        $array = $data_json->results[0];
                        if (isset($array)) {
                            $response = array();
                            foreach ($array->address_components as $addressComponet) {
                                if (in_array('postal_code', $addressComponet->types)) {
                                    $response[$addressComponet->types[0]] = $addressComponet->long_name;
                                }
                            }
                        }
                        if (isset($response['neighborhood'])) {
                            $area = $response['neighborhood'];
                        } elseif (isset($response['locality'])) {
                            $area = $response['locality'];
                        } else {
                            $area = $response['administrative_area_level_2'];
                        }
                        $city = isset($response['administrative_area_level_2']) ? $response['administrative_area_level_2'] : $response['administrative_area_level_1'];
                        $premiumUser['lat'] = $loc->lat;
                        $premiumUser['long'] = $loc->long;
                        $premiumUser['address'] = (empty($loc->area)) ? $area : $loc->area;
                        $premiumUser['city'] = $premiumUser['state'] = (empty($loc->city)) ? $city : $loc->city;
                        $premiumUser['zipcode'] = $response['postal_code'];
                    }
                } elseif (!empty($loc->lat) && !empty($loc->long) && !empty($loc->city)) {
                    $premiumUser['lat'] = $loc->lat;
                    $premiumUser['long'] = $loc->long;
                    $premiumUser['address'] = $loc->area;
                    $premiumUser['city'] = $premiumUser['state'] = $loc->city;

                    $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($loc->lat) . ',' . trim($loc->long) . '&sensor=false';
                    $json = @file_get_contents($url);
                    $data_json = json_decode($json);
                    if ($data_json->status == 'OK') {
                        $array = $data_json->results[0];
                        if (isset($array)) {
                            $response = array();
                            foreach ($array->address_components as $addressComponet) {
                                if (in_array('postal_code', $addressComponet->types)) {
                                    $response[$addressComponet->types[0]] = $addressComponet->long_name;
                                } else $response['postal_code'] = '';
                            }
                            $premiumUser['zipcode'] = $response['postal_code'];
                        }
                    }
                } else {
                    $premiumUser['lat'] = '';
                    $premiumUser['long'] = '';
                    $premiumUser['address'] = '';
                    $premiumUser['city'] = '';
                    $premiumUser['zipcode'] = '';
                }
            }
            $pUser = (object)$premiumUser;
            return MakeResponse::successResponse($pUser);
        }

//        return MakeResponse::errorResponse('No premium user info found!');
    }

    public function getPremiumStatus($userId)
    {
        Miscellaneous::storeActiveUser($userId, request()->header('version'));

        $canAsk = 1;
        $user = User::find($userId);
        $payment = PremiumPayment::with(['premiumPackage'])->whereUserId($userId)->whereStatus('active')->first();

        if (count($payment)) {
            $questionCount = $payment->question_count;
            $questionCap = $payment->premiumPackage->question_cap;
            $isQuestionCapFinished = $questionCap == 0 ? false : $questionCap <= $questionCount;
            $canAsk = $payment->premiumPackage->question_cap - $questionCount;
            $features = $this->getFeatureList($payment->package_id);
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
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    private function fiftyPercentDiscountCode($userId)
    {
        $now = Carbon::now();
        $appliedPromo = \DB::select("select pc.id, pc.code, pc.discount as discount, pc.max_discount as max_discount, pca.created_at as apply_time from premium_coupons pc, premium_coupon_applied pca where pc.id = pca.coupon_id and pca.user_id = {$userId} and pca.deleted_at is not NULL and pc.code = 'RETURNINGUSER50' and pc.expiry_time > '{$now}' order by pca.created_at asc limit 0, 1");

        if (count($appliedPromo)) return [];

        $promo = PremiumCoupon::select(['id', 'code', 'discount', 'max_discount'])->where([
             'code' => 'RETURNINGUSER50'
        ])->first();

        if (count($promo)) return [$promo];

        return [];
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
                'question_cap' => $package->question_cap,
                'conditions_en' => $package->conditions_en,
                'conditions_bn' => $package->conditions_bn,
                'is_taken' => $this->isPackageTaken($userId, $package->id),
                'color' => config("admin.package.$package->id")['color'] ?? '#000000',
                'created_at' => $this->formattedTime($package)
            ];
        })->toArray();
    }

    private function transformCustomPackages($packages, $userId)
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
                    'free_premium' => 0,
                    'question_cap' => $package->question_cap,
                    'conditions_en' => $package->conditions_en,
                    'conditions_bn' => $package->conditions_bn,
                    'is_taken' => $this->isPackageTaken($userId, $package->id),
                    'color' => config("admin.package.$package->id")['color'] ?? '#000000',
                    'title_en' => config("admin.package.$package->id")['title_en'] ?? '',
                    'title_bn' => config("admin.package.$package->id")['title_bn'] ?? '',
                    'subtitle_en' => config("admin.package.$package->id")['subtitle_en'] ?? '',
                    'subtitle_bn' => config("admin.package.$package->id")['subtitle_bn'] ?? '',
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
                    'free_premium' => 0,
                    'question_cap' => $package->question_cap,
                    'conditions_en' => $package->conditions_en,
                    'conditions_bn' => $package->conditions_bn,
                    'is_taken' => $this->isPackageTaken($userId, $package->id),
                    'color' => config("admin.package.$package->id")['color'] ?? '#000000',
                    'title_en' => config("admin.package.$package->id")['title_en'] ?? '',
                    'title_bn' => config("admin.package.$package->id")['title_bn'] ?? '',
                    'subtitle_en' => config("admin.package.$package->id")['subtitle_en'] ?? '',
                    'subtitle_bn' => config("admin.package.$package->id")['subtitle_bn'] ?? '',
                    'created_at' => $this->formattedTime($package)
                ];
        })->toArray();
    }

//    private function getFreePremiumStatus($package, $userId)
//    {
//        try{
//            if($package->id==5 || $package->id==5) return 0;
//            else{
//                $free_premium = PremiumPayment::whereUserId($userId)->wherePackageId($package->id)->where('status', '<>', 'pending')->first();
//                return is_null($free_premium) ? (int)floor(($package->days*0.3)<1 ? 1 : ($package->days*0.3)) : 0;
//            }
//        } catch (\Exception $exception){
//            return 0;
//        }
//    }

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

}
