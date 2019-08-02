<?php
/**
 * Created by PhpStorm.
 * User: razib
 * Date: 2019-06-18
 * Time: 13:52
 */

namespace App\Http\Controllers\Partners\BanglaMeds;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class BanglaMedsController
{

    public function __construct()
    {
        $this->consumerKey = '1cb615ef0b50b640324bb7e614551f7b';
        $this->consumerSecret = 'edf2e5500bd06bdcf0a48182236917af';
        $this->token_secret = 'd4b2655c7355faa644dd9dcf20948b57';
        $this->token = 'bfddd465b6ea96755e5b170c7dc5adec';
        $this->authType = OAUTH_AUTH_TYPE_AUTHORIZATION;
        $this->base_url = 'http://staging.banglameds.com.bd';

        $this->oauthClient = new \OAuth($this->consumerKey, $this->consumerSecret, OAUTH_SIG_METHOD_HMACSHA1, $this->authType);
        $this->oauthClient->enableDebug();
        $this->oauthClient->setToken($this->token, $this->token_secret);

        $this->headers = array('Accept' => 'application/json',
            'Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8');
        $this->headers = array('Content-Type' => 'application/json' , 'Accept' => '*/*');
    }

    public function GetSettings(Request $request){

        try {
//            $resourceUrl = "http://staging.banglameds.com.bd/api/rest/productlist/";
            //$resourceUrl = "http://staging.banglameds.com.bd/api/rest/productlist/?"."page=1&name=".rawurlencode('Ace plus');
            $endpoint = "/api/rest/settings";
            //$resourceUrl = "http://staging.banglameds.com.bd/api/rest/districts";
            //$resourceUrl = "http://staging.banglameds.com.bd/api/rest/areas/485";
            //$resourceUrl = "http://staging.banglameds.com.bd/api/rest/orderstatus/3383";

            //$resourceUrl = "http://staging.banglameds.com.bd/api/rest/orderlist";
//            $resourceUrl = "http://staging.banglameds.com.bd/api/rest/order/3383";
            $this->CallAPI($endpoint, 'GET');

            $data['status'] = 'success';
            $data['data'] = json_decode($this->oauthClient->getLastResponse());


            return Response::json($data);

        } catch (OAuthException $e) {

            dd($e);
//            Log::emergency($e->getMessage() . $e->getFile() . $e->getLine());
//            $data['status'] = 'failure';
//            $data['data'] = json_decode($this->oauthClient->getLastResponse());

//            return Response::json($data);
        }
    }

    public function GetProductList(Request $request){
        $endpoint = '/api/rest/productlist';

        $this->CallAPI($endpoint, 'GET');

        $data['status'] = 'success';
        $data['data'] = json_decode($this->oauthClient->getLastResponse());


        return Response::json($data);

    }

    public function GetDistrict(Request $request){
        $endpoint = '/api/rest/districts';

        $this->CallAPI($endpoint, 'GET');

        $data['status'] = 'success';
        $data['data'] = json_decode($this->oauthClient->getLastResponse());


        return Response::json($data);

    }

    public function GetAreas(Request $request, $district_id){

        $endpoint = '/api/rest/areas/'. $district_id;

        $this->CallAPI($endpoint, 'GET');

        $data['status'] = 'success';
        $data['data'] = json_decode($this->oauthClient->getLastResponse());


        return Response::json($data);

    }

    public function MakeCustomCheckout(Request $request){

        $endpoint = '/api/rest/customcheckout';

        $data = $request->all();

        $this->CallAPI($endpoint, 'POST', $data);

        $data['status'] = 'success';
        $data['data'] = json_decode($this->oauthClient->getLastResponse());


        return Response::json($data);

    }

    public function GetOrderStatus(Request $request, $id){

        $endpoint = '/api/rest/orderstatus/'. $id;

        $this->CallAPI($endpoint, 'GET');

        $data['status'] = 'success';
        $data['data'] = json_decode($this->oauthClient->getLastResponse());


        return Response::json($data);
    }

    public function GetOrderList(Request $request){

        $endpoint = '/api/rest/orderlist';

        $this->CallAPI($endpoint, 'GET');

        $data['status'] = 'success';
        $data['data'] = json_decode($this->oauthClient->getLastResponse());


        return Response::json($data);
    }

    public function GetOrderHistory(Request $request, $order){

        $endpoint = '/api/rest/order/'. $order;

        $this->CallAPI($endpoint, 'GET');

        $data['status'] = 'success';
        $data['data'] = json_decode($this->oauthClient->getLastResponse());


        return Response::json($data);
    }


    public function CallAPI($endpoint, $http_method, $body = []){
//        dd(phpinfo());
        $body = $body;

        try{
            $this->oauthClient->fetch($this->base_url . $endpoint, $body , $http_method, $this->headers);
        }catch (\Exception $e) {
            dd($e);
            $data['status'] = 'failure';
            $data['data'] = json_decode($this->oauthClient->getLastResponse());

//            Log::emergency(json_encode($data) .' '. $e->getMessage() .' '. $e->getFile() .' '. $e->getLine());
            return Response::json($data);
        }

    }


}