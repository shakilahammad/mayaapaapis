<?php

namespace App\Http\Controllers\ApiV1;

use Carbon\Carbon;
use App\Models\ScratchCode;
use App\Models\ScratchApplied;
use App\Models\ScratchIndustry;
use App\Http\Controllers\Controller;

class ScratchCardController extends Controller
{
    public function applyScratchCard($card_number, $user_id)
    {
        $code = ScratchCode::where('code', $card_number)->first();
        if ($code != null) {
            $code_id = $code->id;
            $is_applied = ScratchApplied::where('code_id', $code_id)->get()->count();
            if ($is_applied > 0) {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Invalid Card',
                    'error_code' => 0,
                    'error_message' => '',
                ]);
            }

            $applied = ScratchApplied::create([
                'code_id' => $code_id,
                'user_id' => $user_id
            ]);

            return response()->json([
                'status' => 'success',
                'user_id' => $user_id,
                'started_at' => Carbon::parse($applied->created_at)->format('d M Y '),
                'end_date' => Carbon::parse($applied->created_at)->addDay(30)->format('d M Y'),
                'remaining' => 30 - (Carbon::now()->day - Carbon::parse($applied->created_at)->day),
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'message' => 'Invalid Card',
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function insertNumbers($total, $industry_id)
    {
        $industry_code = ScratchIndustry::find($industry_id)->code;
        for ($i = 0; $i < $total; $i++) {
            ScratchCode::create([
                'industry_id' => $industry_id,
                'code' => $industry_code . $this->getRandomNumber(5, 1)
            ]);
        }
    }

    public function getRandomNumber($digits, $industry_id)
    {
        $code = rand(pow(10, $digits - 1) - 1, pow(10, $digits) - 1);
        if (ScratchCode::where('code', $industry_id . $code)->get()->count() > 0) {
            $this->getRandomNumber(5, $industry_id);
        }
        return $code;
    }

    public function checkStatus($user_id)
    {
        $purchase_info = ScratchApplied::whereUserId($user_id)->first();
        if ($purchase_info != null) {
            if ((30 - (Carbon::now()->day - Carbon::parse($purchase_info->created_at)->day)) > -1) {
                return response()->json([
                    'status' => 'success',
                    'user_id' => $user_id,
                    'started_at' => Carbon::parse($purchase_info->created_at)->format('d M Y'),
                    'end_date' => Carbon::parse($purchase_info->created_at)->addDay(30)->format('d M Y'),
                    'remaining' => 30 - (Carbon::now()->day - Carbon::parse($purchase_info->created_at)->day),
                    'error_code' => 0,
                    'error_message' => '',
                ]);
            }

            return response()->json([
                'status' => 'failure',
                'message' => 'Expired',
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'message' => 'not purchased yet',
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function get_payment_history($user_id)
    {
        $purchased_info = ScratchApplied::whereUserId($user_id)->get();
        if ($purchased_info != null) {
            $data = [];
            foreach ($purchased_info as $item) {
                $values = ([
                    'user_id' => $user_id,
                    'started_at' => Carbon::parse($item->created_at)->format('d M Y'),
                    'end_date' => Carbon::parse($item->created_at)->addDay(30)->format('d M Y'),
                    'remaining' => 30 - (Carbon::now()->day - Carbon::parse($item->created_at)->day),
                ]);
                array_push($data, $values);
            }
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'error_code' => 0,
                'error_message' => '',
            ]);
        }

        return response()->json([
            'status' => 'failure',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

}
