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


class FreePremiumController extends Controller
{
    /**
     * @param Request $request
     * @param string $call
     * @return mixed
     */
    public function createInvoice(Request $request, $call = 'gen_invoice'){

        $data = $request->all();

//        $data['state'] = $data['city'];

        $package = PremiumPackage::find($data['package_id']);

        /* Checking user id. If there is none get it using access token */
        if(!isset($data['user_id'])){
            if ($request->hasHeader('access-token')){
                $accessToken = $request->header('access-token');
                $accessToken = AccessToken::whereToken($accessToken)->first();
            }
            $data['user_id'] = $accessToken->user_id;
        }
//$u = User::with('location')->find($data['user_id']);
//        dd($u);
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
                'is_premium'=>($package->id==6) ? 0 : 1,
                'data' => $return_data,
                'error_code' => 0,
                'error_message' => ''
            ]);
        }else{

            $premium_user = PremiumUser::whereUserId($data['user_id'])->first();

            if(!isset($premium_user)){
                $u = User::with('location')->find($data['user_id']);
                if(isset($u->location)&&is_null($u->location->country)) $u->location->country = 'BD';
                /*
                    Creating premium user
                    Need to look into it later
                */
                $premium_user = PremiumUser::create([
                    'user_id'=>$data['user_id'],
                    'name'=>$u->f_name.' '.$u->l_name,
                    'email'=>$u->email,
                    'phone'=>is_null($u->phone) ? '' : $u->phone,
                    'country'=>is_null($u->location) ? 'BD' : $u->location->country
                ]);
                if(!is_null($u->location)){
                    $premium_user->address = $u->location->location;
                    $premium_user->city = $u->location->city;
                    $premium_user->state = $u->location->city;
                    $premium_user->save();
                }
            }

            $data['currency'] = (isset($data['currency'])) ? $data['currency'] : 'BDT';
            $data['call'] = $call;

            if($package){
                $data['product_name'] = $package->name_en;
                $data['product_description'] = $package->desc_en;
                $data['amount'] = 0;
                $data['days'] = (floor($package->days*0.3)<1) ? 1 : floor($package->days*0.3);
            }

            /* Logging Request before sending to Portwallet */
            $logged = $this->saveLog($data['user_id'], 'accepted', json_encode($data), 'free premium invoice created');

            $premium_user->invoice_id = substr(str_shuffle(str_repeat("ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghijklmnopqrstuvwxyz", 10)), 0, 10);
            $premium_user->save();

            $effective_time = NOW();
            $days = $data['days']*.3;
            $newdate = strtotime('+'.$days.' day', strtotime($effective_time));
            $newdate = date('Y-m-j h:i:s', $newdate);
            $premium_payment = PremiumPayment::create([
                'currency'=>$data['currency'],
                'provider'=>'free_premium',
                'package_id'=>$data['package_id'],
                'effective_time'=>$effective_time,
                'expiry_time'=>$newdate,
                'user_id'=>$data['user_id'],
                'invoice_id'=>$premium_user->invoice_id,
                'amount'=>0,
                'status'=>'free_premium'
            ]);
            $user = User::find($data['user_id']);
            $user->is_premium = 1;
            $user->save();

            $return_data = ['invoice_id'=>$premium_user->invoice_id];

            return response()->json([
                'status' => 'success',
                'is_premium'=>1,
                'data' => $return_data,
                'error_code' => 0,
                'error_message' => ''
            ]);

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
