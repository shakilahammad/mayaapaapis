<?php

namespace App\Http\Controllers\ApiV1;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        if (!isset($request['id'])) {
            return response()->json([
                'status' => 'Failed'
            ]);
        }

        $user = User::find($request->id);

        if (count($user)) {
            if (isset($request['dob'])&&!empty($request['dob'])){
                $dob_arr = explode("-",$request['dob']);

                if(strlen($dob_arr[0]) === 4)
                    $user->birthday = Carbon::createFromFormat('Y-m-d', $request['dob']);
                else
                    $user->birthday = Carbon::createFromFormat('m-d-Y', $request['dob']);

            }
            if (isset($request['name'])&&!empty($request['name']))
                $user->f_name = $request->name;
//                if (isset($request['phone']))
//                    $user->phone = $request->phone;
            if (isset($request['gender'])&&!empty($request['gender']))
                $user->gender = $request->gender;
            if (isset($request['marital_status'])&&!empty($request['marital_status']))
                $user->marital_status = $request->marital_status;
            if (isset($request['age'])&&!empty($request['age']))
                $user->age = $request->age;
            $user->save();
            $status =  'Success';
        }else{
            $status =  'Failed';
        }

        return response()->json([
            'status' => $status
        ]);
    }

}
