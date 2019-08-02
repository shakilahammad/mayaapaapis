<?php

namespace App\Http\Controllers\Payment\GP;

use App\Classes\MakeResponse;
use App\Http\Controllers\APIs\V3\PremiumPackageController;
use App\Http\Controllers\Controller;
use App\Models\PremiumLogs;
use App\Models\PremiumPackage;
use App\Models\PremiumPayment;
use App\Models\PremiumUser;
use App\Models\User;

class GPPayment extends Controller
{
    protected $endpoint;
    protected $appKey;
    protected $appSecret;
    protected $username;
    protected $password;
    protected $createPayment;
    protected $executePayment;
    protected $capturePayment;
    protected $tokenGrant;
    protected $tokenRefresh;
    protected $pushOTP;
    protected $chargePayment;
    protected $charge_code;
    protected $grant_type;
    protected $port;

    public function __construct()
    {
        $this->port = config('gp.api.port');
        $this->endpoint = config('gp.api.url');
        $this->pushOTP = '/pushotp';
        $this->capturePayment = 'payments/v2/customers/';
        $this->chargePayment = '/chargeotp';
        $this->tokenGrant = 'oauth/v1/token';
        $this->clientId = config('gp.credentials.client_id');
        $this->clientSecret = config('gp.credentials.client_secret');
        $this->sourceId = config('gp.credentials.sourceId');
        $this->serviceId = config('gp.credentials.serviceId');
        $this->idType = config('gp.credentials.idType');
        $this->category = config('gp.credentials.category');
        $this->charge_code = config('gp.credentials.charge_code');
        $this->grant_type = config('gp.credentials.grant_type');
    }
    public function post($url, array $data = [])
    {
        $headers = $this->getHeaderForToken();
        return $this->curlRequest($url, $headers, $data);
    }
    public function appCredentials()
    {
        return [
            'app_key' => $this->appKey,
            'app_secret' => $this->appSecret
        ];
    }
    public function generateHeaders($token)
    {
        return [
            "Content-Type:application/json",
            "Authorization:Bearer {$token->id_token}",
            "X-APP-Key:{$this->appKey}"
        ];
    }
    public function getHeaderForToken()
    {
        return [
            "Accept:application/json",
            "Content-Type:application/json",
        ];
    }

    public function getBodyForToken()
    {
        return [
            "client_id:jUmwr7w7qweuYBhkYrAiViAT4PLSsG08",
            "client_secret:ELebytqpFGTZHot4",
            "grant_type:client_credentials"
        ];
    }

    public function getAccessToken(){
        $headers = $this->getHeaderForToken();
        $url = $this->endpoint . $this->tokenGrant;
        $body = $this->getBodyForToken();
//        dd(json_encode($body));
        $data['client_id'] = $this->clientId;
        $data['client_secret'] = $this->clientSecret;
        $data['grant_type'] = $this->grant_type;
//        return $this->curlRequest($url, $headers, $data);
        return $this->curlGPAccessToken();
//        dd($access_token['accessToken']);
//        $access_token['accessToken'] = 'ACWxnkMsvzqcWyufOg8SgTzCJGYz';
//        $pushOTPObj = $this->curlGPPushOTP($access_token['accessToken'], '8801737076523');
//        $OTPObj = json_decode($pushOTPObj);
//        dd($pushOTPObj);
//        $otpTrasactionId = $OTPObj->data->otpTrasactionId;
//        $otpTrasactionId = '5660211161548320151700219';
//        dd($otpTrasactionId);
//        $data = json_decode('{ "data": { "serverReferenceCode": "5720211171548320755548786", "amount": "10.00", "transactionId": "5460211141548320709698552" }, "accessInfo": { "timestamp": "2019-01-24T15:05:55+06:00" }, "statusCode": "200" }');
//        dd($data->data->amount);
//        return $this->curlGPCharge($access_token['accessToken'], $otpTrasactionId, '8801737076523');
//        return $this->curlGPPushOTP();
    }

    public function gpPushOTP($phone, $package_id, $user_id=null){
        if(strpos($phone, '88') === false) {
            $phone = '88'.$phone;
        }
        $headers = $this->getHeaderForToken();
        $url = $this->endpoint . $this->tokenGrant;
        $body = $this->getBodyForToken();
        $data['client_id'] = $this->clientId;
        $data['client_secret'] = $this->clientSecret;
        $data['grant_type'] = $this->grant_type;
//        return $this->curlRequest($url, $headers, $data);
        $access_token = $this->curlGPAccessToken();
        $access_token = json_decode($access_token);
//        dd($access_token->accessToken);
//        $access_token['accessToken'] = 'ACWxnkMsvzqcWyufOg8SgTzCJGYz';
        $package = PremiumPackage::select('id','price')->whereType('telco')->whereId($package_id)->first();
        if($phone=='8801737076523'||$phone=='8801728320638'||$phone=='8801711505928') $package->price = 1;
        $pushOTPObj = $this->curlGPPushOTP($access_token->accessToken, $phone, $package->price);
        $OTPObj = json_decode($pushOTPObj);
        $this->saveLog($user_id, 'request', json_encode($OTPObj), 'GP push OTP request');
        $OTPObj->data->access_token = !empty($access_token->accessToken) ? $access_token->accessToken : '';
        $dataObj = [
            'access_token' => $OTPObj->data->access_token,
            'otpTrasactionId' => $OTPObj->data->otpTrasactionId
        ];
        $status_code = isset($OTPObj->statusCode) ? $OTPObj->statusCode : $OTPObj->accessInfo->statusCode;
        if($status_code==200){
            return MakeResponse::successResponse($dataObj);
        }else{
            return MakeResponse::errorResponseOperator('gp push otp failed', 'GP charge failed');
        }
//        dd(json_encode($OTPObj));
//        $otpTrasactionId = $OTPObj->data->otpTrasactionId;
//        $otpTrasactionId = '5660211161548320151700219';
//        dd($otpTrasactionId);
//        $data = json_decode('{ "data": { "serverReferenceCode": "5720211171548320755548786", "amount": "10.00", "transactionId": "5460211141548320709698552" }, "accessInfo": { "timestamp": "2019-01-24T15:05:55+06:00" }, "statusCode": "200" }');
//        dd($data->data->amount);
//        return $this->curlGPCharge($access_token['accessToken'], $otpTrasactionId, '8801737076523');
//        return $this->curlGPPushOTP();
    }

    public function gpCharge($access_token, $otpTrasactionId, $phone, $pin, $package_id, $user_id=null){

        if(strpos($phone, '88') === false) {
            $phone = '88'.$phone;
        }

        /* Finding Premium User */
        $puser = PremiumPayment::select('premium_payments.invoice_id as pay_invoice', 'premium_users.*')
            ->join('premium_users', 'premium_payments.user_id', '=', 'premium_users.user_id')
            ->where('premium_payments.user_id', $user_id)
            ->where('status', '=', 'active')
            ->orderby('id', 'desc')
            ->first();

        $package = PremiumPackage::whereType('telco')->whereId($package_id)->get();

        $pkg = new PremiumPackageController();
        $premiumPackages = $pkg->transformPackages_multisource(
            $package,
            $user_id
        );

        /* Checking Users Active Subscription */
        if(isset($puser)){
//            $return_data = ['invoice_id'=>$puser->pay_invoice];
            return response()->json([
                'status' => 'success',
                'is_premium'=>($package[0]->type=='chat') ? 0 : 1,
                'data' => $premiumPackages,
                'error_code' => 0,
                'error_message' => 'User Already Premium'
            ]);
        }else {
//        dd(json_encode($body));
//        return $this->curlRequest($url, $headers, $data);
            $gpChargeCurl = $this->curlGPCharge($access_token, $otpTrasactionId, $phone, $pin);
//        $gpChargeCurl = '{ "data": { "serverReferenceCode": "5520211151548582352712952", "amount": "1.00", "transactionId": "5760211171548582278909953" }, "accessInfo": { "timestamp": "2019-01-27T15:45:52+06:00" }, "statusCode": "200" }';
            $gpCharge = json_decode($gpChargeCurl);
//            dd($access_token, $otpTrasactionId, $phone, $pin, $puser, $gpCharge);
            $status_code = isset($gpCharge->statusCode) ? $gpCharge->statusCode : $gpCharge->accessInfo->statusCode;
//        dd($status_code, $gpChargeCurl, $gpCharge->accessInfo->statusCode);
            if ($status_code == 200) {
                $this->saveLog($user_id, 'accepted', json_encode($gpCharge), 'GP charge successfull');

                $premium_user = PremiumUser::whereUserId($user_id)->first();

                if (!isset($premium_user)) {
                    $u = User::with('location')->find($user_id);
                    /*
                        Creating premium user
                        Need to look into it later
                    */
                    $name = isset($u->f_name) ?? $u->f_name . ' ' . $u->l_name;
                    $premium_user = PremiumUser::create([
                        'user_id' => $user_id,
                        'name' => $u->f_name . ' ' . $u->l_name,
                        'email' => $u->email,
                        'phone' => isset($u->phone) ? $u->phone : $phone,
                        'address' => isset($u->location->location) ? $u->location->location : '',
                        'city' => isset($u->location->city) ? $u->location->city : '',
                        'state' => isset($u->location->city) ? $u->location->city : '',
                        'zipcode' => isset($u->location->email) ? $u->location->email : '',
                        'country' => $u->location->country ?? 'Bangladesh'
                    ]);
                }

                $data['currency'] = 'BDT';

                if ($package) {
                    $data['product_name'] = $package[0]->name_en;
                    $data['product_description'] = $package[0]->desc_en;
                    $data['amount'] = 0;
                    $data['days'] = (floor($package[0]->days * 0.3) < 1) ? 1 : floor($package[0]->days * 0.3);
                }

                $premium_user->invoice_id = substr(str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghijklmnopqrstuvwxyz", 10)), 0, 10);
                $premium_user->save();

                $effective_time = NOW();
                $newdate = strtotime('+' . $package[0]->days . ' day', strtotime($effective_time));
                $newdate = date('Y-m-j h:i:s', $newdate);
                $premium_payment = PremiumPayment::create([
                    'currency' => $data['currency'],
                    'provider' => 'gp',
                    'package_id' => $package[0]->id,
                    'effective_time' => $effective_time,
                    'expiry_time' => $newdate,
                    'user_id' => $user_id,
                    'invoice_id' => $premium_user->invoice_id,
                    'amount' => $gpCharge->data->amount,
                    'status' => 'active'
                ]);
                $user = User::find($user_id);
                $user->is_premium = 1;
                $user->save();

                return response()->json([
                    'status' => 'success',
                    'is_premium' => 1,
                    'data' => $premiumPackages,
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            } else {
                $this->saveLog($user_id, 'cancelled', json_encode($gpCharge), 'GP charge failed');
                if(isset($gpCharge->message)&&strpos($gpCharge->message, 'LOW BAL')!==false) {
                    $error = 'Insufficient Balance';
                }else
                    $error = '';
                return MakeResponse::errorResponseOperator('failure', $error);
            }
        }
    }

    public function curlRequest($requestUrl, $headers, $data)
    {
//        dd($data);
        $url = curl_init($requestUrl);
        curl_setopt($url, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($url, CURLOPT_POSTFIELDS,
            "client_id=".$this->clientId."&client_secret=".$this->clientSecret."&grant_type=client_credentials");
//        if (!empty($data)) {
//            curl_setopt($url, CURLOPT_POSTFIELDS, json_encode($data));
//        }
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $results = curl_exec($url);
//        dd($results);
        curl_close($url);
        return json_decode($results, true);
    }

    public function curlGPAccessToken(){
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );

        curl_setopt_array($curl, array(
            CURLOPT_PORT => $this->port,
            CURLOPT_URL => $this->endpoint . $this->tokenGrant,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "client_id=".$this->clientId."&client_secret=".$this->clientSecret."&grant_type=".$this->grant_type."&undefined=",
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Content-Type: application/x-www-form-urlencoded",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return MakeResponse::errorResponse("cURL Error #:" . $err);
        } else {
            return $response;
        }
    }

    public function curlGPPushOTP($accessToken, $phone, $amount){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => "9001",
            CURLOPT_URL => $this->endpoint . $this->capturePayment . $phone . $this->pushOTP,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\r\n  \"sourceId\":\"$this->sourceId\",\r\n  \"idType\":\"$this->idType\",\r\n  \"amount\":\"$amount\",\r\n  \"priceCode\":\"$this->charge_code\",\r\n  \"serviceId\":\"$this->serviceId\",\r\n  \"description\":\"Testing\" \r\n}\r\n",
            CURLOPT_HTTPHEADER => array(
                "Accept-Encoding: application/gzip",
                "Authorization: Bearer $accessToken",
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return MakeResponse::errorResponse("cURL Error #:" . $err);
        } else {
            return $response;
        }
    }

    public function curlGPCharge($accessToken, $otpTransactionId, $phone, $pin){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_PORT => "9001",
            CURLOPT_URL => $this->endpoint . $this->capturePayment . $phone . $this->chargePayment,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\r\n  \"sourceId\": \"$this->sourceId\",\r\n  \"idType\": \"$this->idType\",\r\n  \"serviceId\": \"$this->serviceId\",\r\n  \"transactionPin\": \"$pin\",\r\n  \"otpTransactionId\": \"$otpTransactionId\",\r\n  \"category\": \"$this->category\",\r\n  \"description\": \"test desc\"\r\n}\r\n",
//            CURLOPT_POSTFIELDS => "{\r\n  \"sourceId\": \"AGWMayaAPA\",\r\n  \"idType\": \"MSISDN\",\r\n  \"serviceId\": \"PPU00021805127\",\r\n  \"transactionPin\": \"3948\",\r\n  \"otpTransactionId\": \"5520211151548580054728242\",\r\n  \"category\": \"App\",\r\n  \"description\": \"test desc\"\r\n}\r\n",
            CURLOPT_HTTPHEADER => array(
                "Accept-Encoding: application/gzip",
                "Authorization: Bearer $accessToken",
                "Content-Type: application/json",
//                "Postman-Token: ac44d3ad-7662-480b-8337-6f000f70ee6a",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return MakeResponse::errorResponse("cURL Error #:" . $err);
        } else {
            return $response;
        }
    }

    function saveLog($user_id, $status='request', $data, $uagent=''){
        if(empty($uagent)) $uagent = $_SERVER['HTTP_USER_AGENT'];
        $request_log = PremiumLogs::create([
            'user_id'=>$user_id,
            'status'=>$status,
            'data'=>$data,
            'user_agent'=>$uagent
        ]);
        return $request_log;
    }

}