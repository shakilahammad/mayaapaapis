<?php

namespace App\Http\Controllers\Payment\Bkash;

use App\Http\Controllers\Controller;

class BkashPayment extends Controller
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

    public function __construct()
    {
        $this->endpoint = config('bkash.api.url');
        $this->createPayment = 'payment/create';
        $this->executePayment = 'payment/execute/';
        $this->capturePayment = 'payment/capture/';
        $this->tokenGrant = 'token/grant';
        $this->tokenRefresh = 'token/refresh';
        $this->appKey = config('bkash.credentials.bkash_app_key');
        $this->appSecret = config('bkash.credentials.bkash_app_secret');
        $this->username = config('bkash.credentials.username');
        $this->password = config('bkash.credentials.password');
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
            "Content-Type:application/json",
            "password:{$this->password}",
            "username:{$this->username}",
        ];
    }
    public function curlRequest($requestUrl, $headers, array $data)
    {
        $url = curl_init($requestUrl);
        curl_setopt($url, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        if (!empty($data)) {
            curl_setopt($url, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $results = curl_exec($url);
        curl_close($url);
        return json_decode($results, true);
    }
}
