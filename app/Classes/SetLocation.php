<?php

namespace App\Classes;

use App\Models\Location;

class SetLocation
{
    public static function formattedLocation($ip, $lat = 0, $long = 0, $user_id = null)
    {
        try {
            if(empty($lat) && empty($long)) {
                $userIp = json_decode(file_get_contents('http://api.ipinfodb.com/v3/ip-city/?key=72932e8578deabbdd3cda65836f5b960d05c3ef59b4dc33c6ccd5f86bba59e69&format=json&ip=' . $ip));
                if (isset($userIp->latitude) && isset($userIp->longitude)) {
                    $lat = $userIp->latitude;
                    $long = $userIp->longitude;
                }
            }

            $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($long) . '&sensor=false';
            $json = @file_get_contents($url);
            $data_json = json_decode($json);

            $array = $data_json->results[0];

            if (isset($array)) {
                $response = array();
                foreach ($array->address_components as $addressComponet) {
                    if (in_array('political', $addressComponet->types)) {
                        $response[$addressComponet->types[0]] = $addressComponet->long_name;
                    }
                }
            }

            if (isset($response['neighborhood'])) {
                $area = $response['neighborhood'];
            } elseif (isset($response['locality'])) {
                $area = $response['locality'];
            } else {
                $area = $response['administrative_area_level_2'];
            }

            $city = isset($response['administrative_area_level_2']) ? $response['administrative_area_level_2'] : $response['administrative_area_level_1'];
            $country = isset($response['country']) ? $response['country'] : 'Bangladesh';
            $location = $array->formatted_address;
            $userLocation = Location::firstorcreate([
                'user_id' => $user_id,
                'ip' => $ip,
                'lat' => $lat,
                'long' => $long,
                'area' => $area,
                'city' => $city,
                'country' => $country,
                'location' => $location
            ]);

            return $userLocation;

        }catch (\Exception $exception){
            $userLocation = Location::create([
                'user_id' => $user_id,
                'ip' => $ip,
                'lat' => $lat,
                'long' => $long
            ]);
        }

        return $userLocation;
    }

}
