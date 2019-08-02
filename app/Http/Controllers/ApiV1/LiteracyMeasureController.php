<?php

namespace App\Http\Controllers\ApiV1;

use App\Models\Question;
use App\Models\QuestionTag;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Literacy\MeasureMCQ;
use App\Http\Controllers\Controller;
use App\Models\Literacy\MeasureResults;
use App\Models\Literacy\MeasureQuestion;
use App\Models\Literacy\MeasureActivity;

class LiteracyMeasureController extends Controller
{
    public function fetchMCQs($userId, $questionId)
    {
        try {
            $question = Question::whereUserId($userId)->whereStatus('answered')->find($questionId);

            if (!count($question)) {
                return $this->makeResponse('failure');
            }

            if (MeasureActivity::whereUserId($userId)->whereQuestionId($questionId)->exists()) {
                return $this->makeResponse('cancel');
            }

            $tags = $this->matchedTags($questionId);

            if (empty($tags)) {
                return $this->makeResponse('failure');
            }

            $postMcqs = $this->checkPostMcqs($tags, $userId, $questionId);

            if (count($postMcqs)){
                $article = MeasureQuestion::whereIn('tag_id', $tags)->whereType('post')->first();

                return $this->makeResponse('success', $postMcqs, $article ?? null, 'post');
            }

            $data = MeasureMCQ::whereDoesntHave('results', function ($query) use ($userId, $questionId) {
                $query->where('user_id', '=', $userId)->where('question_id', '=', $questionId)->where('type', 'pre');
            })->whereIn('tag_id', $tags)->whereType('pre')->get();

            if (!count($data)) {
                return $this->makeResponse('answered');
            }

            $article = MeasureQuestion::whereIn('tag_id', $tags)->whereType('pre')->first();

            return $this->makeResponse('success', $data, $article ?? null);

        } catch (\Exception $exception) {
            return $this->makeResponse('failure');
        }
    }


    private function checkPostMcqs($tags, $userId, $questionId)
    {
        $postNotification = Notification::with(['Message'])->whereNotifiable($userId)->whereQuestionId($questionId)->whereIn('notifications_message_id', [33, 34])->whereIsSeen(0)->first();

        if (!empty($postNotification) && $postNotification->message->type == 'Post-Literacy'){
            return MeasureMCQ::whereDoesntHave('results', function ($query) use ($userId, $questionId) {
                $query->where('user_id', '=', $userId)->where('question_id', '=', $questionId)->where('type', 'post');
            })->whereIn('tag_id', $tags)->whereIn('type', ['pre', 'post'])->get();
        }

        return null;
    }

    public function storeAnswer(Request $request, $userId, $questionId)
    {
        try {
            $params = $request->all();

            if (empty($params['answers'])) {
                return $this->makeResponse('failure');
            }

            foreach ($params['answers'] as $key => $answer) {
                MeasureResults::updateOrCreate([
                    'user_id' => $userId,
                    'question_id' => $questionId,
                    'mcq_id' => $key,
                    'type' => $request->type ?? 'pre'
                ], ['answer' => $answer]);
            }

            return $this->makeResponse('success');

        } catch (\Exception $exception) {
            return $this->makeResponse('failure');
        }
    }

    public function cancel($userId, $questionId, $mcqId = null)
    {
        try {
            MeasureActivity::updateOrCreate(
                ['user_id' => $userId, 'question_id' => $questionId],
                ['mcq_id' => $mcqId]
            );
            return $this->makeResponse('success');
        } catch (\Exception $exception) {
            return $this->makeResponse('failure');
        }
    }

    private function matchedTags($questionId)
    {
        $tags = config('admin.prePostTags');

        $questionTags = QuestionTag::whereQuestionId($questionId)->get();

        if (!count($questionTags)) return false;

        $newTags = $questionTags->pluck('tag_id')->toArray();
        $newTagscompare = array_pluck($tags, 'id');

        return array_intersect($newTags, $newTagscompare);
    }

    private function makeResponse($status, $data = null, $article = null, $type = 'pre')
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'article' => $article,
            'type' => $type,
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

}
