<?php

namespace App\Http\Controllers\ApiV1;

use App\Models\Feedback;
use Illuminate\Http\Request;
use App\Models\FeedbackMessage;
use App\Http\Controllers\Controller;

class UserFeedbackController extends Controller
{
    public function getFeedbackMessage($Id, $userId = null)
    {
        try {
            $data = FeedbackMessage::first();

            return $this->sendSuccessResponse($data);

        }catch (\Exception $exception){
            return $this->sendFailureResponse(null);
        }
    }

    public function postFeedback(Request $request, $questionId, $userId)
    {
        $validator = \Validator::make($request->all(), [
            'is_helpfull' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->sendFailureResponse($validator->errors());
        }

        try{
            $data = Feedback::updateOrCreate(
                ['question_id' => $questionId, 'user_id' => $userId],
                [
                    'is_helpfull' => $request->is_helpfull,
                    'feedback_message_id' => $request->feedback_message_id
                ]
            );

            return $this->sendSuccessResponse($data);

        }catch (\Exception $exception){
            return $this->sendFailureResponse(null);
        }
    }

    public function sendSuccessResponse($data)
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

    public function sendFailureResponse($data)
    {
        return response()->json([
            'status' => 'failure',
            'data' => $data,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }

}
