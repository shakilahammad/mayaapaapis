<?php

namespace App\Http\Controllers\ApiV1;

use App\Models\User;
use App\Models\Reply;
use App\Models\Comment;
use App\Models\Question;
use Illuminate\Http\Request;
use App\Models\CommentQuestion;
use App\Http\Controllers\Controller;
use App\Http\Controllers\APIs\V3\QuestionController;
use Illuminate\Support\Facades\Validator;

class CommentAndReplyController extends Controller
{
    public function fetchCommentAndReply($question_id)
    {
        try {
            $comments = Comment::whereQuestionId($question_id)->where('status', '!=', 'spam')->take(100)->orderby('id', 'desc')->get();

            $commentsCount = Comment::whereQuestionId($question_id)->count();

            $allComments = [];
            foreach ($comments as $comment) {
                $reply = $comment->reply()->take(1)->get();
                $replyCount = Reply::whereCommentId($comment->id)->count();
                $data = [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'source' => $comment->source,
                    'status' => $comment->status,
                    'privacy' => $comment->privacy,
                    'article_id' => $comment->article_id,
                    'question_id' => $comment->question_id,
                    'user_id' => $comment->user_id,
                    'who' => $comment->who,
                    'created_at' => $comment->created_at->diffForHumans(),
                    'updated_at' => $comment->updated_at->diffForHumans(),
                    'reply' => $reply,
                    'replyCount' => $replyCount,
                ];
                array_push($allComments, $data);
            }

            return response()->json([
                'status' => 'success',
                'commentCount' => $commentsCount,
                'comments' => $allComments,
                'error_code' => 0,
                'error_message' => ''
            ]);

        }catch (\Exception $exception){
            return $this->errorResponse();
        }
    }

    public function postCommentAndReply(Request $request)
    {
        list($who, $user) = $this->checkWho($request);
        if (isset($request->comment_id) || !empty($request->comment_id)) {
            $validation = Validator::make($request->all(), [
                'user_id' => 'required',
                'body' => 'required',
                'comment_id' => 'required',
            ]);
            if ($validation->fails()) {
                return $this->errorResponse('error', $validation->errors());
            }

            return $this->postReply($request, $who);
        } else {
            $validation = Validator::make($request->all(), [
                'user_id' => 'required',
                'body' => 'required',
                'question_id' => 'required'
            ]);
            if ($validation->fails()) {
                return $this->errorResponse('error', $validation->errors());
            }

            return $this->postComment($request, $who, $user);
        }
    }

    private function checkWho(Request $request)
    {
        $user = User::find($request->user_id);
        if (count($user) && $user->type != 'user') {
            return ['Maya Apa', $user];
        }

        $checkAsker = Question::whereId($request->question_id)->whereUserId($request->user_id)->exists();
        if ($checkAsker) {
            return ['Asker', $user];
        }

        return ['Anonymous', $user];
    }

    public function postReply(Request $request, $who)
    {
        Reply::create([
            'user_id' => $request->user_id,
            'body' => $request->body,
            'who' => $who,
            'comment_id' => $request->comment_id,
            'source' => 'app'
        ]);

        return $this->successResponse('success', null);
    }

    public function postComment($request, $who, $user)
    {
        $comment = Comment::create([
            'user_id' => $request->user_id,
            'comment' => $request->body,
            'who' => $who,
            'question_id' => $request->question_id,
            'source' => 'app',
        ]);

        $this->storeCommentQuestion($who, $user, $request, $comment);

        return $this->successResponse('success', null);
    }

    public function fetchReply($comment_id)
    {
        $reply = Reply::whereCommentId($comment_id)->get();
        if (count($reply) > 0) {
            return $this->successResponse('success', $reply);
        }

        return $this->errorResponse('failure', []);
    }

    public function commentDelete(Request $request)
    {
        $comment = Comment::find($request->comment_id);
        $comment->delete();

        return $this->successResponse('success', null);
    }

    public function questionCommentsEdit(Request $request)
    {
        $questionComment = Comment::find($request->id);

        if (!count($questionComment)) {
            return $this->errorResponse('Failed', null);
        }

        $questionComment->comment = $request->body;
        $questionComment->save();

        return $this->errorResponse('Success', null);
    }

    public function spamComment($userId, $commentId)
    {
        try {
            $comment = Comment::find($commentId);

            if ($this->isQuestionAsker($comment, $userId)) {
                $comment->update([
                    'status' => 'spam'
                ]);

                return $this->errorResponse('success', null);
            }

            $errorMessage = 'Something went wrong!';

        } catch (\Exception $exception) {
            $errorMessage = 'Something went wrong!';
        }

        return response()->json([
            'status' => 'failure',
            'data' => [
                'error' => $errorMessage
            ],
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

    public function isQuestionAsker($comment, $userId)
    {
        $question = Question::find($comment->question_id);

        return $question->user_id == $userId;
    }

    private function errorResponse($status = 'failure', $data = null)
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

    private function successResponse($status = 'success', $data = null)
    {
        return response()->json([
            'status' => $status,
            'data' => $data,
            'error_code' => 0,
            'error_message' => ''
        ]);
    }

    private function storeCommentQuestion($who, $user, $request, $comment): void
    {
        if ($who == 'Asker') {
            $question['body'] = $request->body;
            $question['user_id'] = $request->user_id;
            $question['source'] = 'app';
            $question['lat'] = '';
            $question['long'] = '';
            $question['type'] = 'text';
            $question['comment_id'] = $comment->id;
            $request->question = $question;

            $storeQuestion = new QuestionController();
            $newQuestion = $storeQuestion->storeQuestion($request);
            $responseData = $newQuestion->getData();


            if ($responseData->status == 'success') {
                CommentQuestion::create([
                    'question_id' => $responseData->data->id,
                    'comment_id' => $comment->id
                ]);
            }
        }
    }
}
