<?php

namespace App\Classes;

class MakeResponse
{
    public static function successResponse($data, $status = 'success', $errorCode = 0, $errorMessage = '')
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'error_code' => $errorCode,
            'error_message' => $errorMessage
        ]);
    }

    public static function errorResponse($errorMessage)
    {
        return response()->json([
            'status' => 'failure',
            'data' => null,
            'error_code' => 0,
            'error_message' => $errorMessage
        ]);
    }

    public static function errorResponseOperator($status, $errorMessage)
    {
        return response()->json([
            'status' => $status,
            'data' => null,
            'error_code' => 0,
            'error_message' => $errorMessage
        ]);
    }

}
