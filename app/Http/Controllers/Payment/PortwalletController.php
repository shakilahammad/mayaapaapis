<?php
/**
 * User: itsfaruque
 * Date: 7/5/18
 * Time: 12:16 PM
 */

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\APIs\V4\PremiumPackageController;
use App\Http\Helper;
use App\Models\PremiumCouponApplied;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use App\Models\PremiumPackage;
use App\Models\PremiumLogs;
use App\Models\PremiumUser;
use App\Models\PremiumPayment;
use App\Models\AccessToken;
use App\Models\Invite;
use App\Classes\MakeResponse;
use App\Models\User;
use Carbon\Carbon;

class PortwalletController extends Controller
{

    private $discounted_package_prices = [
        "Maya Plus 7-days free trial" => 0,
        "Maya Prescription" => 199,
        "Maya Alap" => 100,
        "Maya Mix" => 50,
//        "Maya Bot" => 0,
        "Maya Shuru" => 20
    ];

    /**
     * @param Request $request
     * @param string $call
     * @return mixed
     */
    public function createInvoice(Request $request, $call = 'gen_invoice'){

        $data = $request->all();

        $data['state'] = $data['city'];

        $package = PremiumPackage::find($data['package_id']);

        /* Checking user id. If there is none get it using access token */
        if(!isset($data['user_id'])){
            if ($request->hasHeader('access-token')){
                $accessToken = $request->header('access-token');
                $accessToken = AccessToken::whereToken($accessToken)->first();
            }
            $data['user_id'] = $accessToken->user_id;
        }

        /* Finding Premium User */
        $puser = PremiumPayment::select('premium_payments.invoice_id as pay_invoice','premium_users.*')
            ->join('premium_users', 'premium_payments.user_id', '=', 'premium_users.user_id')
            ->where('premium_payments.user_id', $data['user_id'])
            ->where('status', '=', 'active')
            ->orderby('id', 'desc')
            ->first();

        /* Checking Users Active Subscription */
        if(isset($puser)){
            $return_data = ['invoice_id'=>$puser->pay_invoice];
            return response()->json([
                'status' => 'success',
                'is_premium'=>1,
                'data' => $return_data,
                'error_code' => 0,
                'error_message' => ''
            ]);
        }else{

            $premium_user = PremiumUser::whereUserId($data['user_id'])->first();

            if(isset($premium_user)){
                /* Updating premium user */
                $premium_user->name = $data['name'];
                $premium_user->email = $data['email'];
                $premium_user->phone = $data['phone'];
                $premium_user->address = $data['address'];
                $premium_user->city = $data['city'];
                $premium_user->state = $data['city'];
                $premium_user->zipcode = $data['zipcode'];
                $premium_user->country = $data['country'];
                $premium_user->save();
            }else{
                /* Creating premium user */
                $premium_user = PremiumUser::create([
                    'user_id'=>$data['user_id'],
                    'name'=>$data['name'],
                    'email'=>$data['email'],
                    'phone'=>$data['phone'],
                    'address'=>$data['address'],
                    'city'=>$data['city'],
                    'state'=>$data['state'],
                    'zipcode'=>$data['zipcode'],
                    'country'=>$data['country']
                ]);
            }

            $data['currency'] = (isset($data['currency'])) ? $data['currency'] : 'BDT';
            $data['call'] = $call;

            if($package){
                $data['product_name'] = $package->name_en;
                $data['product_description'] = $package->desc_en;
                $data['amount'] = $request->is('*/new/*') ?
                    $this->getDiscountedPriceNew($package, $data['user_id']) :
                    $this->getDiscountedPrice($package->price, $data['user_id']);

//                $data['amount'] = $this->getDiscountedPrice($package->price, $data['user_id']);
//                $dt = new PremiumPackageController();
//                $ddt = $dt->getPackages_v4($data['user_id']);
//                $coupon_applied = PremiumCouponApplied::with('coupon')->whereUserId(93349)->first();
//                $dis = $coupon_applied->coupon->discount/100;
//                $dis = ($dis==1) ? $package->price
//                $data['amount'] = ($dis==1) ? 0 : ($package->price - ($dis * $package->price));
//                dd($coupon_applied->coupon->discount, $dis, $total);
//                $data['amount'] = $package->price;
                $data['days'] = $package->days;
            }

            $data['redirect_url'] = 'https://maya-apa.com/mayaapi/payment/portwallet/response';
            $data['ipn_url'] = 'https://maya-apa.com/mayaapi/payment/portwallet/ipn';

            /* Logging Request before sending to Portwallet */
            $logged = $this->saveLog($data['user_id'], 'request', json_encode($data), 'invoice created');

            /* Call to Portwallet */
            $result = $this->getFormattedArray($data);
            $json = json_decode($result);

            $premium_user->invoice_id = (isset($json->data->invoice_id)) ? $json->data->invoice_id : 0;
            $premium_user->save();

            $sstring = explode('/payment/', $request->url());
            $sstring = explode('/', $sstring[1]);

            if($json->data->amount==0){
                /*
                 * Entry the free request to Payment
                 * table & user premium status update
                 */

                $appliedCoupon = $this->getValidAppliedPromo($data['user_id']);

                if(count($appliedCoupon) > 0)
                    $this->deletedAppliedPromo($data['user_id'], $appliedCoupon->id);
                $effective_time = NOW();
                $newdate = strtotime('+7 day', strtotime($effective_time));
                $newdate = date('Y-m-j h:i:s', $newdate);
                $premium_payment = PremiumPayment::create([
                    'currency'=>$data['currency'],
                    'provider'=>$sstring[0],
                    'package_id'=>$data['package_id'],
                    'effective_time'=>$effective_time,
                    'expiry_time'=>$newdate,
                    'user_id'=>$data['user_id'],
                    'invoice_id'=>$premium_user->invoice_id,
                    'amount'=>$json->data->amount,
                    'status'=>'active'
                ]);
                $user = User::find($data['user_id']);
                $user->is_premium = 1;
                $user->save();
            }else{
                /* Entry the paid request to Payment table */
                $premium_payment = PremiumPayment::create([
                    'currency'=>$data['currency'],
                    'provider'=>$sstring[0],
                    'package_id'=>$data['package_id'],
                    'user_id'=>$data['user_id'],
                    'invoice_id'=>$premium_user->invoice_id,
                    'amount'=>$json->data->amount
                ]);
            }

            if($json->status==200){
                $log_status = (isset($json->data->status)) ? $json->data->status : 'pending';
                $logged = $this->saveLog($data['user_id'], $log_status, $result);
                $return_data = ['invoice_id'=>$json->data->invoice_id];
                if($json->data->amount==0) $isPremium = 1;
                else $isPremium = 0;
                return response()->json([
                    'status' => 'success',
                    'is_premium'=>$isPremium,
                    'data' => $return_data,
                    'error_code' => 0,
                    'error_message' => ''
                ]);
            }else{
                if($json->status==400) $error_message = 'Your request could not completed. Either you provided wrong parameters or you forgot to provide some mandatory parameters';
                elseif($json->status==500) $error_message = 'You are not allowed to perform this action.';
                $log_status = (isset($json->data->status)) ? $json->data->status : 'cancelled';
                $logged = $this->saveLog($data['user_id'], $log_status, $result);
                return response()->json([
                    'status' => 'failure',
                    'is_premium'=>0,
                    'data' => null,
                    'error_code' => 0,
                    'error_message' => $error_message
                ]);
            }

        }

    }

    /*
     * This method is to be called from getResponsePortwallet method
     * @param Request $request
     * @return mixed
     */
    public function verifyTransaction(Request $request, $call = 'ipn_validate'){

        $data = $request->input();
        $data['call'] = $call;

        $result = $this->getFormattedArray($data);

        $json = json_decode($result);

        /*
         * Payment table update on success
         * User table update on success
         */
        $premium_payment = PremiumPayment::where('invoice_id',$data['invoice'])->first();

        if(isset($premium_payment)) {

            if ($json->data->status == 'ACCEPTED') {
                $status = 'active';
                $user = User::find($premium_payment->user_id);
                $user->is_premium = 1;
                $user->save();
            } else $status = strtolower($json->data->status);
            $premium_payment->gateway = $json->data->gateway_name;
            $premium_payment->gateway_category = $json->data->gateway_category;
            $premium_payment->gateway_network = $json->data->gateway_network;
            $premium_payment->issuer = (isset($json->data->issuer)) ? $json->data->issuer : '';
            $premium_payment->status = $status;

            if ($status == 'refunded')
                $premium_payment->refunded_at = NOW();

            $premium_payment->save();

            $this->saveLog($premium_payment->user_id, $json->data->status, $result, $json->data->user_agent);
        }

        return $result;
    }

    /*
     * This method is to be called from portwallet server method
     * @param Request $request
     * @return mixed
     */
    public function ipnStatus(Request $request){

//        $user = User::find(266715);
//        //8801826017767@phone.com.bd, 8801826017767
//        $id = Helper::maya_encrypt('8801871209023');
//        dd($id, $user->email, $user->phone);
        $data = $request->input();

        /*
         * Payment table update on success
         * User table update on success
         */
        $premium_payment = PremiumPayment::where('invoice_id',$data['invoice'])->first();

        if(isset($premium_payment)) {

            if ($data['status'] == 'ACCEPTED') {
                $status = 'active';
                $user = User::find($premium_payment->user_id);
                $user->is_premium = 1;
                $user->save();
//                $now = Carbon::now()->format('Y-m-d h:i:s');

                $appliedCoupon = $this->getValidAppliedPromo($data['user_id']);

                if(count($appliedCoupon) > 0)
                    $this->deletedAppliedPromo($data['user_id'], $appliedCoupon->id);

            } else $status = strtolower($data['status']);

            $premium_payment->status = $status;
            if ($status == 'refunded')
                $premium_payment->refunded_at = NOW();
            else {
                $package = PremiumPackage::find($premium_payment->package_id);
                $premium_payment->effective_time = NOW();
                $days = $package->days.' day';
                $newdate = strtotime('+'.$days, strtotime($premium_payment->effective_time));
                $newdate = date('Y-m-j h:i:s', $newdate);
                $premium_payment->expiry_time = $newdate;
            }
            $premium_payment->save();

        }

        $this->saveLog($data['user_id'], strtolower($data['status']), json_encode($data), 'ipn_call_from_portwallet');

    }

    public function retrieveTransaction(Request $request, $call = 'get_invoice'){
        $data = $request->input();
        $data['call'] = $call;

        $result = $this->getFormattedArray($data);

        return $result;
    }

    public function refundRequest(Request $request, $call = 'refund_request'){
        $data = $request->input();
        $data['call'] = $call;

        $result = $this->getFormattedArray($data);
        $json = json_decode($result);

        $premium_payment = PremiumPayment::where('invoice_id',$data['invoice'])->first();

        /*
         * Log Entry
         * Payment table update on success
         * User table update on success
         */
        $this->saveLog($data['user_id'], $json->data->status, $result, $json->data->user_agent);

        return $result;

    }

    public function getResponsePortwallet(Request $request){
        $data = [
            'status' => $request->input('status'),
            'invoice_id' => $request->input('invoice'),
            'try_again_url' => config('custom.payment.portwallet_payment_url')
        ];

        if($request->input('amount')!=0){
            $invoice_details = $this->verifyTransaction($request);
        }

        return view('payment.portwallet.success')->with($data);

    }

    function getFormattedArray($arr){

        $arr['app_key'] = config('custom.payment.portwallet_key');
        $arr['timestamp'] = time();
        $arr['token'] = md5(config('custom.payment.portwallet_secret').$arr['timestamp']);

        foreach($arr as $key=>$val){
            $form[$key] = $val;
        }

        // Portwallet URL
        $url = config('custom.payment.portwallet_url');

        $client = new Client(); //GuzzleHttp\Client
        $result = $client->post($url, [
            'form_params' => $form
        ]);

        return $result->getBody()->getContents();

//        foreach($arr as $key=>$ar){
//            $$key = $ar;
//        }
//        return $$key;

    }

    public function ipnLog(Request $request){
        $this->saveLog('14228', 'pending', $request->input(), '');
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

    private function getDiscountedPrice($price, $userId)
    {
        $appliedPromo = PremiumCouponApplied::with('coupon')->whereUserId($userId)->first();

        if (!count($appliedPromo)){
            return $price;
        }elseif ($appliedPromo->coupon->expiry_time < Carbon::now()){
            return $price;
        }

        $discount = (int) ($appliedPromo->coupon->discount * $price) / 100;

        if ($discount >= $appliedPromo->coupon->max_discount && $appliedPromo->coupon->max_discount > 0) {
            return $price - $appliedPromo->coupon->max_discount;
        }

        return ceil($price - $discount);
    }

    private function getDiscountedPriceNew($package, $userId)
    {
        $discounted_package_price = $this->discounted_package_prices[$package->name_en];
        $now = Carbon::now();
        $promo = \DB::select("select pc.id, pc.discount as discount, pc.max_discount as max_discount, pca.created_at as apply_time from premium_coupons pc, premium_coupon_applied pca where pc.id = pca.coupon_id and pca.user_id = {$userId} and pca.deleted_at is NULL and pc.expiry_time > '{$now}' order by pc.discount desc limit 0, 1");

        if (empty($promo[0])) return $discounted_package_price;

        $discount = (int)($package->price - ($promo[0]->discount * $package->price) / 100);

        if($discount <= $discounted_package_price){
            return $discount . ".00";
        }

        return $discounted_package_price . ".00";
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

}
