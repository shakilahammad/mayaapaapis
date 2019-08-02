<?php

namespace App\Http\Controllers\ApiV1;

use App\Events\CreatePointTransaction;
use App\Models\Like;
use App\Http\Controllers\Controller;

class LikeController extends Controller
{
    public function like($questionId, $userId)
    {
        $like = Like::whereQuestionId($questionId)->whereUserId($userId)->withTrashed()->first();

        if (!count($like)) {
            Like::create([
                'question_id' => $questionId,
                'user_id' => $userId
            ]);

//            event(new CreatePointTransaction($userId, 4));
            return $this->makeResponse('success', 'Thanks for like!');
        }elseif (!$like->trashed()) {
            $like->delete();
            return $this->makeResponse('success', 'Thanks for unlike!');
        }else if ($like->trashed()){
            $like->restore();
            return $this->makeResponse('success', 'Thanks for like!');
        }

        return $this->makeResponse('failure', '');
    }

    private function makeResponse($status, $data)
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'error_code' => 0,
            'error_message' => '',
        ]);
    }
}
