<?php

namespace App\Http\Controllers\APIs\V2;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Location;
use App\Models\PremiumUser;
use App\Models\AccessToken;
use App\Http\Controllers\Controller;

class FakeLoginController extends Controller
{

    public function fake($userId)
    {
        $user = User::find($userId);

        list($location, $premiumUser) = $this->getLocationAndPremiumInfo($user);

        $accessToken = AccessToken::firstOrCreate(
            ['user_id' => $userId], ['token' => str_random(100), 'last_requested_at' => Carbon::now()]
        );

        return $this->makeSuccessResponse($user, $location, $premiumUser, $accessToken->token);
    }

    private function makeSuccessResponse($data, $location, $premiumUser, $accessToken)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'location' => $location,
            'premium_user' => $premiumUser,
            'access_token' => $accessToken,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    private function makeFailureResponse($data, $location, $premiumUser, $accessToken)
    {
        return response()->json([
            'status' => 'failed',
            'data' => '',
            'location' => '',
            'premium_user' => '',
            'access_token' => '',
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    private function getLocationAndPremiumInfo($user)
    {
        return [
            $this->getLocation($user->location_id),
            $this->getPremiumUser($user->id)
        ];
    }

    private function getLocation($locationId)
    {
        $location = Location::find($locationId);

        if (!count($location)) {
            return null;
        }

        return [
            'id' => $location->id,
            'ip' => $location->ip,
            'lat' => $location->lat,
            'lang' => $location->long,
            'area' => $location->area,
            'city' => $location->city,
            'country' => $location->country,
            'location' => $location->location,
            'created_at' => Carbon::parse($location->created_at)->toDateTimeString()
        ];
    }

    private function getPremiumUser($userId)
    {
        $user = PremiumUser::whereUserId($userId)->orderBy('created_at', 'desc')->first();

        return $user ?? null;
    }
}
