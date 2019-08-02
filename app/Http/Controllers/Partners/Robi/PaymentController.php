<?php

namespace App\Http\Controllers\Partners\Robi;

use App\Classes\MakeResponse;
use App\Http\Controllers\Controller;
use App\Models\PremiumLogs;
use App\Models\PremiumPackage;
use App\Models\PremiumPayment;
use App\Models\PremiumUser;
use App\Models\User;
use GuzzleHttp\Client;

/**
 * Class QuestionController
 * @package App\Http\Controllers\Partners\Robi
 */
class PaymentController extends Controller
{
    /**
     * @param $phn - phone number of user for charging
     * @param $amount - payment amount as per package
     */
    public function multisourceRobiCharging($user_id, $phone, $package_id)
    {
        $user = User::find($user_id);
        $operator = $this->checkOperator($phone);

        if($operator['status']==1){

            $user_status = intval($this->curlSubscribe('http://202.73.4.105/maya/c2wapapi.php?Type=status&MobileNo='.$phone));
            $dnd_status = intval($this->curlSubscribe('http://202.73.4.105/smsstatus/dndstatus.php?mn='.$phone));

            /* Finding Premium User */
            $puser = PremiumPayment::select('premium_payments.invoice_id as pay_invoice','premium_users.*')
                ->join('premium_users', 'premium_payments.user_id', '=', 'premium_users.user_id')
                ->where('premium_payments.user_id', $user->id)
                ->where('status', '=', 'active')
                ->orderby('id', 'desc')
                ->first();

            if(isset($puser)){
//                $return_data = ['invoice_id'=>$puser->pay_invoice];
                return response()->json([
                    'status' => 'already',
                    'data' => null,
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }else{
                $logged = $this->saveLog($user->id, 'request', json_encode(['multisource'=>'robi', 'service'=>'app', 'package_id'=>2, 'multisource_user_status'=>$user_status, 'multisource_dnd_status'=>$dnd_status, 'price_point'=>4973]), 'robi_charging');

                if($dnd_status==0){
                    $form['MobileNo'] = isset($phone) ? $phone : $user->phone;
                    $form['Amount'] = config('multisource.robi.package.total_price');
                    if($form['MobileNo']=="8801827215471"||$form['MobileNo']=="8801838198104") $form['Amount'] = 1;
                    // Multisource API URL
//                $url = config('multisource.robi.charging');

                    $response = $this->curlSubscribe('http://202.73.4.105/maya/payment.php?MobileNo='.$form["MobileNo"].'&Amount='.$form["Amount"]);

//                $response = intval($result->getBody()->getContents());

                    if($response==1){
                        $premium_payment = $this->savetelcoPayment($user, $package_id, $operator['provider']);
                        $logged = $this->saveLog($user->id, 'accepted', json_encode(['multisource'=>'robi', 'service'=>'app', 'package_id'=>2, 'multisource_user_status'=>$user_status, 'price_point'=>4748]), 'multisource_wap_service');
                        return MakeResponse::successResponse($premium_payment);
                    }else
                        $logged = $this->saveLog($user->id, 'rejected', json_encode(['multisource'=>'robi', 'service'=>'app', 'package_id'=>2, 'price_point'=>4748]), 'multisource_wap_service');
                        return MakeResponse::errorResponse("Payment failed to complete");
                }else{
                   return MakeResponse::errorResponse("DND status true");
                }
            }

        }else{
            return MakeResponse::errorResponseOperator('operator_failure', "This operator is not supported");
        }

    }

    public function checkOperator($phone){
        if(preg_match("/88018/", $phone))
            $operator = [
                'status' => 1,
                'provider' => 'multisource_robi'
            ];
        elseif (preg_match("/88016/", $phone))
            $operator = [
                'status' => 1,
                'provider' => 'multisource_airtel'
            ];
        else $operator = [
            'status' => 0,
            'provider' => ''
        ];
        return $operator;
    }

    public function savetelcoPayment($user, $package_id, $provider){
        /* Checking user id. If there is none get it using access token */
        //        if(!isset($data['user_id'])){
        //            if ($request->hasHeader('access-token')){
        //                $accessToken = $request->header('access-token');
        //                $accessToken = AccessToken::whereToken($accessToken)->first();
        //            }
        //            $data['user_id'] = $accessToken->user_id;
        //        }

        $premium_user = PremiumUser::whereUserId($user->id)->first();

        if (isset($premium_user)) {
            /* Updating premium user */
            $premium_user->name = $user->name;
            $premium_user->email = $user->email;
            $premium_user->phone = $user->phone;
            $premium_user->address = $user->address;
            $premium_user->city = $user->city;
            $premium_user->state = $user->city;
            $premium_user->zipcode = $user->zipcode;
            $premium_user->country = $user->country;
            $premium_user->save();
        } else {
            /* Creating premium user */
            $premium_user = PremiumUser::create([
                'user_id' => $user->id,
                'name' => is_null($user->name) ? 'Anonymous' : $user->name,
                'email' => is_null($user->email) ? '' : $user->email,
                'phone' => is_null($user->phone) ? '' : $user->phone,
                'address' => is_null($user->address) ? '' : $user->address,
                'city' => is_null($user->city) ? '' : $user->city,
                'state' => is_null($user->city) ? '' : $user->city,
                'zipcode' => is_null($user->zipcode) ? '' : $user->zipcode,
                'country' => is_null($user->country) ? 'BD' : $user->country
            ]);
        }

//            $data['currency'] = 'BDT';

        $package = PremiumPackage::find($package_id);

        if ($package) {
            $data['product_name'] = $package->name_en;
            $data['product_description'] = $package->desc_en;
            $data['amount'] = $package->price;
//                $coupon_applied = PremiumCouponApplied::with('coupon')->whereUserId(93349)->first();
//                $dis = $coupon_applied->coupon->discount/100;
//                $dis = ($dis==1) ? $package->price
//                $data['amount'] = ($dis==1) ? 0 : ($package->price - ($dis * $package->price));
//                dd($coupon_applied->coupon->discount, $dis, $total);
//                $data['amount'] = $package->price;
            $data['days'] = $package->days;
        }

        $s = substr(str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghijklmnopqrstuvwxyz", 10)), 0, 10);

        $effective_time = NOW();
        $newdate = strtotime('+7 day', strtotime($effective_time));
        $newdate = date('Y-m-j h:i:s', $newdate);
        $premium_payment = PremiumPayment::create([
            'currency'=>'BDT',
            'provider'=>$provider,
            'package_id'=>$package->id,
            'effective_time'=>$effective_time,
            'expiry_time'=>$newdate,
            'user_id'=>$user->id,
            'invoice_id'=>$s,
            'amount'=>$package->price,
            'status'=>'active'
        ]);
        $user->is_premium = 1;
        $user->save();

        $logged = $this->saveLog($user->id, 'accepted', json_encode(['multisource'=>'robi', 'package_id'=>9, 'price_point'=>4748, 'invoice_id'=>$s]), 'invoice created for Multisource Robi');
        return $premium_payment;
    }

    function saveLog($user_id, $status, $data, $uagent=''){
        if(empty($uagent)) $uagent = $_SERVER['HTTP_USER_AGENT'];
        $request_log = PremiumLogs::create([
            'user_id'=>$user_id,
            'status'=>$status,
            'data'=>$data,
            'user_agent'=>$uagent
        ]);
        return $request_log;
    }

    private function curlSubscribe($url, $messageData=[]) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
        return curl_exec($ch);
    }
}
