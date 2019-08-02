<?php

namespace App\Http\Controllers\Payment\GP;

use Carbon\Carbon;
use App\Models\User;
use App\Models\BkashToken;
use App\Models\PremiumUser;
use App\Models\PremiumLogs;
use App\Models\PremiumPackage;
use App\Models\PremiumPayment;
use App\Models\PremiumCouponApplied;

class GPController extends GPPayment
{
    const CURRENCY = 'BDT';
    const INTENT = 'sale';
    const PROVIDER = 'BKASH-CHECKOUT';

    public function checkout()
    {
        $params = request()->all();

        $package = PremiumPackage::find($params['packageId']);


        return view('payment.bkash.checkout')->with([
            'amount' => $this->getDiscountedPrice($package, $params['userId']),
            'intent' => self::INTENT
        ]);
    }

    public function createPayment($userId, $packageId)
    {
        try {
            if (PremiumPayment::whereUserId($userId)->whereStatus('active')->exists()) {
                return response()->json([
                    'status' => 'already',
                    'data' => null
                ]);
            }

            $user = User::with(['location'])->find($userId);
            $package = PremiumPackage::find($packageId);
            $invoiceId = strtoupper(str_random(8) . '' . rand(1, 5));

            $amount = $this->getDiscountedPrice($package, $user->id);

            $this->createPremiumUser($user, $invoiceId);
            $this->createPremiumPayment($user, $package, $amount, $invoiceId);

            if ($amount < 1) {
                $this->updatePremiumPayment($user, $packageId);
                $user->update([
                    'is_premium' => 1
                ]);
                return response()->json([
                    'status' => 'freemium',
                    'data' => null
                ]);
            }

            $requestUrl = $this->endpoint . $this->createPayment;
            $response = $this->makeRequest($requestUrl, [
                'amount' => $amount,
                'currency' => self::CURRENCY,
                'intent' => self::INTENT,
                'merchantInvoiceNumber' => $invoiceId
            ]);

            $this->createLog(request()->userAgent(), $user->id, 'request', json_encode($response));

            if (isset($response['errorCode'])) {
                return response()->json([
                    'status' => 'bkash-error',
                    'data' => $response
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $response
            ]);

        } catch (\Exception $exception) {
            $errorResponse = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];

            $this->createLog(request()->userAgent(), $user->id, 'rejected', json_encode($errorResponse));

            return response()->json([
                'status' => 'failure',
                'data' => null
            ]);
        }
    }

    public function executePayment($userId, $packageId)
    {
        try {
            $requestUrl = $this->endpoint . $this->executePayment . request('paymentID');
            $response = $this->makeRequest($requestUrl);

            if (isset($response['trxID'])) {
                $user = User::find($userId);
                $this->updatePremiumPayment($user, $packageId);
                $user->update([
                    'is_premium' => 1
                ]);

                $this->createLog(request()->userAgent(), $userId, 'accepted', json_encode($response));

                return response()->json([
                    'status' => 'success',
                    'data' => $response
                ]);
            }

            $this->createLog(request()->userAgent(), $userId, 'rejected', json_encode($response));

            return response()->json([
                'status' => 'bkash-error',
                'data' => $response
            ]);

        }catch (\Exception $exception){
            $response = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];

            $this->createLog(request()->userAgent(), $userId, 'rejected', json_encode($response));

            return response()->json([
                'status' => 'failure',
                'data' => null
            ]);
        }
    }

    public function capturePayment($paymentId)
    {
        $requestUrl = $this->endpoint . $this->capturePayment . $paymentId;
        return $this->makeRequest($requestUrl);
    }

    private function getToken()
    {
        $oldToken = BkashToken::orderBy('created_at', 'desc')->first();

        if (!count($oldToken) || ($oldToken->created_at->diffInDays(Carbon::now())) > 30) {
            return $this->generateToken();
        }

        if ($oldToken->created_at->diffInSeconds(Carbon::now()) > $oldToken->expires_in) {
            return $this->refreshToken($oldToken);
        }

        return $oldToken;
    }

    private function generateToken()
    {
        $requestUrl = $this->endpoint . $this->tokenGrant;
        $token = $this->post($requestUrl, $this->appCredentials());

        return BkashToken::create($token);
    }

    public function refreshToken($tokenObject)
    {
        $requestUrl = $this->endpoint . $this->tokenRefresh;
        $data = array_merge($this->appCredentials(), ['refresh_token' => $tokenObject->refresh_token]);

        $tokenObject->update($this->post($requestUrl, $data));
        return $tokenObject;
    }

    private function makeRequest($requestUrl, array $data = [])
    {
        $headers = $this->generateHeaders($this->getToken());
        return $this->curlRequest($requestUrl, $headers, $data);
    }

    private function createPremiumUser($user, $invoiceId)
    {
        $info = [
            'user_id' => $user->id,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'invoice_id' => $invoiceId,
            'city' => optional($user->location)->city ?? '',
            'address' => optional($user->location)->location ?? '',
            'country' => optional($user->location)->country ?? ''
        ];

        PremiumUser::updateOrCreate(['user_id' => $user->id], $info);
    }

    private function createLog($agent, $user_id, $status, $data)
    {
        PremiumLogs::create([
            'user_id' => $user_id,
            'status' => $status,
            'data' => $data,
            'user_agent' => $agent
        ]);
    }

    private function getDiscountedPrice($package, $userId)
    {
        $now = Carbon::now();
        $promo = \DB::select("select pc.id, pc.discount as discount, pc.max_discount as max_discount, pca.created_at as apply_time from premium_coupons pc, premium_coupon_applied pca where pc.id = pca.coupon_id and pca.user_id = {$userId} and pca.deleted_at is NULL and pc.expiry_time > '{$now}' order by pca.created_at asc limit 0, 1");

        if (empty($promo[0])) return $package->price;

        $discount = (int)($promo[0]->discount * $package->price) / 100;

        if ($discount >= $promo[0]->max_discount && $promo[0]->max_discount > 0) {
            return ceil($package->price - $promo[0]->max_discount) . ".00";
        }

        return ceil($package->price - $discount) . ".00";
    }

    private function createPremiumPayment($user, $package, $amount, $invoiceId)
    {
        PremiumPayment::create([
            'currency' => self::CURRENCY,
            'provider' => self::PROVIDER,
            'package_id' => $package->id,
            'effective_time' => Carbon::now(),
            'expiry_time' => Carbon::now()->addDays($package->days),
            'user_id' => $user->id,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
            'status' => 'pending'
        ]);
    }

    private function updatePremiumPayment($user, $packageId)
    {
        $package = PremiumPackage::find($packageId);
        $appliedCoupon = $this->getValidAppliedPromo($user->id);
        $lastPayment = PremiumPayment::whereUserId($user->id)->wherePackageId($packageId)->whereStatus('pending')->orderBy('created_at', 'desc')->first();

        if (count($appliedCoupon)){
            $this->deletedAppliedPromo($user->id, $appliedCoupon->id);
            $appliedCouponId = $appliedCoupon->id;
        }else{
            $appliedCouponId = null;
        }

        $lastPayment->update([
            'currency' => self::CURRENCY,
            'provider' => self::PROVIDER,
            'package_id' => $package->id,
            'coupon_id' => $appliedCouponId,
            'effective_time' => Carbon::now(),
            'expiry_time' => Carbon::now()->addDays($package->days),
            'user_id' => $user->id,
            'status' => 'active'
        ]);
    }

    private function getValidAppliedPromo($userId)
    {
        $now = Carbon::now();
        $promo = \DB::select("select pc.id, pc.discount as discount, pc.max_discount as max_discount, pca.created_at as apply_time from premium_coupons pc, premium_coupon_applied pca where pc.id = pca.coupon_id and pca.user_id = {$userId} and pca.deleted_at is NULL and pc.expiry_time > '{$now}' order by pca.created_at asc limit 0, 1");

        return $promo[0] ?? null;
    }

    private function deletedAppliedPromo($userId, $couponId)
    {
        $appliedPromo = PremiumCouponApplied::whereUserId($userId)->whereCouponId($couponId)->first();

        if (count($appliedPromo)) {
            $appliedPromo->delete();
        }
    }

    public function paymentSucess()
    {
        $params = request()->all();

        $data = [
            'errorCode' => $params['errorCode'],
            'errorMessage' => $params['errorMessage']
        ];

        if ($params['status'] == 'ACCEPTED') {
            return view('payment.bkash.success')->with($data);
        } elseif ($params['status'] == 'ALREADY') {
            return view('payment.bkash.already')->with($data);
        }

        return view('payment.bkash.error')->with($data);
    }

}
