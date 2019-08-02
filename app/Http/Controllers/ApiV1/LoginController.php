<?php

namespace App\Http\Controllers\ApiV1;

use Carbon\Carbon;
use App\Models\User;
use App\Http\Helper;
use GuzzleHttp\Client;
use App\Models\AccessToken;
use App\Classes\SetLocation;
use Illuminate\Http\Request;
use App\Models\TrackDownload;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\ClientException;

class LoginController extends Controller
{
    protected $client;

    public function __construct() {
        $this->client = new Client();
    }

    public function login($provider, Request $request)
    {
        switch ($provider) {
            case 'phone':
                return $this->phoneLogin($request);
                break;
            case 'email':
                return $this->emailLogin($request);
                break;
            case 'facebook':
                return $this->facebookLogin($request);
                break;
            default:
                return $this->makeResponse('failure', null, 1, 'Something went wrong!');
        }
    }

    private function phoneLogin($request)
    {
        list($accountkitResponse, $accessTokenErrorMessage) = $this->getAccessToken($request);

        if (empty($accountkitResponse)){
            return $this->makeResponse('failure', null, 1, $accessTokenErrorMessage);
        }

        list($response, $errorMessage) = $this->getEmailOrPhoneFromAccessToken($accountkitResponse->access_token);

        if (!empty($response)) {
            $phone = preg_replace('/[^0-9]/', '', $response->phone->number);
            $email = $response->phone->number . '@phone.com.bd';

            $user = $this->getUserByPhone($phone, $email);

            if (empty($user)) {
                $user = $this->registerWithPhone($request,  $phone);
            }

            $this->storeAccessToekn($user->id, $accountkitResponse->access_token);

            return $this->makeResponse('success', $user, 0, '', $accountkitResponse->access_token);
        }

        return $this->makeResponse('failure', null, 1, $errorMessage);
    }

    private function emailLogin($request)
    {
        list($accountkitResponse, $accessTokenErrorMessage) = $this->getAccessToken($request);

        if (empty($accountkitResponse)){
            return $this->makeResponse('failure', null, 1, $accessTokenErrorMessage);
        }

        list($response, $errorMessage) = $this->getEmailOrPhoneFromAccessToken($accountkitResponse->access_token);

        if (!empty($response)) {
            $email = $response->email->address;
            $user = $this->getUserByEmail($email);

            if (empty($user)) {
                $user = $this->registerWithEmail($request, $email);
            }

            $this->storeAccessToekn($user->id, $accountkitResponse->access_token);
            return $this->makeResponse('success', $user, 0, '', $accountkitResponse->access_token);
        }

        return $this->makeResponse('failure', null, 1, $errorMessage);
    }

    private function facebookLogin($request)
    {
        $accessToken = $request->access_token;
        list($facebookResponse, $errorMessage) = $this->getResponseFromFacebook($accessToken);

        if (!empty($facebookResponse)) {
            $user = $this->fetchOrCreateFacebookUser($facebookResponse, $request);
            $this->storeAccessToekn($user->id, $accessToken);

            return $this->makeResponse('success', $user, 0, '', $accessToken);
        }

        return $this->makeResponse('failure', null, 1, $errorMessage);
    }

    private function getAccessToken($request)
    {
        $authorizationCode = $request->access_token;
        $apiVersion = config('custom.accountkit.api_version');
        $appId = config('custom.accountkit.app_id');
        $appSecret = config('custom.accountkit.app_secret');
        $url = "https://graph.accountkit.com/{$apiVersion}/access_token?grant_type=authorization_code&code={$authorizationCode}&access_token=AA|{$appId}|{$appSecret}";

        return $this->get($url);
    }

    private function getEmailOrPhoneFromAccessToken($accessToken)
    {
        $url = "https://graph.accountkit.com/v1.1/me/?access_token={$accessToken}";

        return $this->get($url);
    }

    private function getResponseFromFacebook($accessToken)
    {
        $url = "https://graph.facebook.com/me?fields=birthday,email,gender&access_token={$accessToken}";
        return $this->get($url);
    }

    private function getUserByEmail($email)
    {
        $user = User::whereEmail(Helper::maya_encrypt($email))->first();

        if (count($user)) {
            $user->update([
                'session' => 1
            ]);
            $user->is_new = 0;
            return $user;
        }

        return null;
    }

    private function getUserByPhone($phone, $email)
    {
        $user = User::wherePhone(Helper::maya_encrypt($phone))->orWhere('email', Helper::maya_encrypt($email))->first();

        if (count($user)) {
            $user->update([
                'session' => 1
            ]);
            $user->is_new = 0;
            return $user;
        }

        return null;
    }

    public function registerWithEmail($request, $email)
    {
        $lat = 23.991734; $long = 90.419588;
        if (isset($request->lat) && isset($request->long) && $request->long != 0 && $request->lat != 0) {
            $lat = $request->lat;
            $long = $request->long;
        }

        $location = SetLocation::formattedLocation(0, $lat, $long);

        $user_data['email'] = $email;
        $user_data['source'] = 'app';
        $user_data['session'] = 1;
        $user_data['registered'] = 1;
        $user_data['location_id'] = $location->id;

        if (isset($requestData['device_id'])) {
            $track_download_id = TrackDownload::whereDeviceId($request->device_id)->first();
            if (count($track_download_id)) {
                $user_data['track_download_id'] = $track_download_id->id;
            }
        }
        $createUser = User::create($user_data);
        $createUser = User::find($createUser->id);
        $location->user_id = $createUser->id;
        $location->save();
        $createUser->location = '';
        return $createUser;
    }

    private function registerWithPhone($request, $phoneNumber)
    {
        $lat = 23.991734; $long = 90.419588;
        if (isset($request->lat) && isset($request->long) && $request->long != 0 && $request->lat != 0) {
            $lat = $request->lat;
            $long = $request->long;
        }

        $location = SetLocation::formattedLocation(0, $lat, $long);

        $user_data['phone'] = $phoneNumber;
        $user_data['email'] = $phoneNumber . '@phone.com.bd';
        $user_data['source'] = 'app';
        $user_data['session'] = 1;
        $user_data['registered'] = 1;
        $user_data['location_id'] = $location->id;

        if (isset($post_data['device_id'])) {
            $track_download_id = TrackDownload::whereDeviceId($request->device_id)->first();
            if (count($track_download_id)) {
                $user_data['track_download_id'] = $track_download_id->id;
            }
        }

        $createUser = User::create($user_data);
        $createUser = User::find($createUser->id);
        $location->user_id = $createUser->id;
        $location->save();
        $createUser->location = '';
        return $createUser;
    }

    private function fetchOrCreateFacebookUser($facebookResponse, $request)
    {
        $email = $facebookResponse->email ?? $facebookResponse->id . '@facebook.com';
        $userByFbId = User::whereFbId($facebookResponse->id)->first();
        $userByEmail = User::whereEmail(Helper::maya_encrypt($email))->first();

        if (count($userByFbId)) {
            $userByFbId->update([
                'session' => 1
            ]);
            $userByFbId->is_new = 0;
            return $userByFbId;
        }elseif (count($userByEmail)){
            $userByEmail->update([
                'session' => 1
            ]);
            $userByEmail->is_new = 0;
            return $userByEmail;
        }

        $is_new = 1;
        $location = SetLocation::formattedLocation($request->ip(), $request->lat, $request->long);

        $userData = [
            'f_name' => $request->f_name ?? 'Anonymous',
            'l_name' => $request->l_name ?? '',
            'email' => $email,
            'fb_id' => $facebookResponse->id,
            'gender' => $facebookResponse->gender ?? 'other',
            'birthday' => $facebookResponse->birthday ?? null,
            'source' => 'app',
            'location_id' => $location->id,
            'track_download_id' => $this->fetchTrackDownloadID($request),
            'registered' => 1,
            'session' => 1
        ];

        $user = User::create($userData);
        $location->user_id = $user->id;
        $location->save();
        $user->is_new = $is_new;
        return $user;
    }

    private function fetchTrackDownloadID($request)
    {
        $download_id = 0;
        if (isset($request->device_id)) {
            $track_download_id = TrackDownload::whereDeviceId($request->device_id)->first();
            if (count($track_download_id)) {
                $download_id = $track_download_id->id;
            }
        }

        return $download_id;
    }

    private function get($url)
    {
        try {
            $response = $this->client->get($url);
            $jsonResponse = json_decode($response->getBody());
            return [$jsonResponse, ''];
        } catch (ClientException $exception) {
            $errorMessage = json_decode($exception->getResponse()->getBody()->getContents());
            return [null, $errorMessage->error->message];
        }
    }

    public function logout(Request $request)
    {
        $user = User::find($request->id);
        try{
            $user->update([
                'session' => 0
            ]);

            $accessToken = $request->header('access-token');
            $accessToken = AccessToken::whereToken($accessToken)->first();
            $accessToken->delete();

            return $this->makeResponse('success', null, 0, '');

        }catch (\Exception $exception) {
            return $this->makeResponse('failure', null, 0, '');
        }
    }

    private function makeResponse($status, $data, $errorCode, $errorMessage, $accessToken = null)
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'access_token' => $accessToken,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);
    }

    private function storeAccessToekn($userId, $accessToken)
    {
        AccessToken::updateOrCreate(
            ['user_id' => $userId],
            ['token' => $accessToken, 'last_requested_at' => Carbon::now()]
        );
    }
}
