<?php

namespace App\Http\Controllers\ApiV1;

use App\Models\Advertisement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdvertisementController extends Controller
{
    public function getAdvertisements($type)
    {
        $adds = Advertisement::whereType($type)->get();
        $banner_all = Advertisement::whereType($type)->wherePage(1)->get();
        if (count($adds) > 0) {
            return response()->json([
                'status' => 'success',
                'version' => $adds[sizeof($adds) - 1]->version,
                'data' => $adds,
                'all_page' => $banner_all,
                'error_code' => 0,
                'error_message' => ''
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

    public function store_adds(Request $request)
    {
        $lastVersion = 0;
        $adds = Advertisement::all();
        if (count($adds) > 0) {
            $lastVersion = $adds[sizeof($adds) - 1]->version + 1;
        }
        $s3 = \Storage::disk('s3');
        $imageDestination = 'images/maya_adds/';
        if ($request->hasFile('add')) {
            if ($request->add->isValid()) {
                $fileName = time() . '' . rand(1, 1000) . '.' . $request->add->guessExtension();
                $s3->put($imageDestination . '/' . $fileName, file_get_contents($request->add));
                $addvertise = new Advertisement();
                $addvertise->url = $fileName;
                $addvertise->version = $lastVersion;
                $addvertise->type = $request->type;
                $addvertise->priority = $request->priority;
                $addvertise->save();
                return 'success';
            }
        }

        return 'failure';
    }
}
