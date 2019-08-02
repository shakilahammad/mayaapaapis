<?php

namespace App\Http\Controllers\ApiV1;

use Carbon\Carbon;
use App\Classes\MakeResponse;
use App\Models\PremiumCoupon;
use App\Models\PremiumCouponApplied;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class PromoCodeController extends Controller
{
    public function apply($code, $userId)
    {
        try {
            $coupon = PremiumCoupon::whereCode(strtoupper($code))->where('expiry_time', '>', Carbon::now())->first();

            if (count($coupon)) {
                $applied = PremiumCouponApplied::whereCouponId($coupon->id)->whereUserId($userId)->exists();

                if ($applied == false) {
                    PremiumCouponApplied::create([
                        'coupon_id' => $coupon->id,
                        'user_id' => $userId
                    ]);

                    return MakeResponse::successResponse(null);
                } else {
                    return MakeResponse::successResponse('Already Applied!', 'applied');
                }

            }

            return response()->json([
                'status' => 'invalid',
                'data' => null,
                'error_code' => 0,
                'error_message' => ''
            ]);
        } catch (\Exception $exception) {
//            Log::info('Promo Code - "' . $code . '". User ID - "' . $userId . '". ' . $exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }

    public function getAppliedPromo($userId)
    {
        $now = Carbon::now();
        $appliedPromo = \DB::select("SELECT pc.id, pc.expiry_time, pc.code, pc.discount, pc.max_discount, pc.type, pc.expiry_time, pc.created_at FROM premium_coupons pc, premium_coupon_applied pca WHERE pc.id = pca.coupon_id AND pca.user_id = {$userId} AND pc.code <>'RETURNINGUSER50' AND  pca.deleted_at is null AND pc.expiry_time > '{$now}'");

        $data = collect($appliedPromo)
//                 ->reject(function ($promo){
//                    return $promo->code === 'RETURNINGUSER50';})
                 ->map(function ($promo){
                    return [
                        'id' => $promo->id,
                        'code' => $promo->code,
                        'discount' => $promo->discount,
                        'max_discount' => $promo->max_discount,
                        'type' => $promo->type,
                        'expiry_time' => Carbon::parse($promo->expiry_time)->format('d M Y'),
                        'created_at' => $this->formattedTime($promo->created_at)
                    ];
                });


        if(count($data)) {
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'error_code' => 0,
                'error_message' => ''
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'data' => null,
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

    private function formattedTime($time)
    {
        return Carbon::parse($time)->diffForHumans();
    }

}
