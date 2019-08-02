<?php

namespace App\Http\Controllers\APIs\V2;

use App\Models\PremiumUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PremiumController extends Controller
{
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'phone' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'data' => $validator->errors()
            ]);
        }

        $user = PremiumUser::updateOrCreate(
            ['user_id' => $request->user_id],
            $request->only('phone')
        );

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }
}
