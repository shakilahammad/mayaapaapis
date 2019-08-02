<?php

namespace App\Listeners\Question;

use App\Events\AnswerWasPost;
use App\Events\CreatePointTransaction;
use App\Http\Helper;
use App\Jobs\ProcessAutoAnswer;
use App\Models\AiAnswerLog;
use App\Models\Answer;
use App\Models\Question;
use App\Classes\DitectThread;
use App\Models\AIResponseLog;
use App\Classes\Miscellaneous;
use App\Jobs\UserNotification;
use App\Jobs\ReferNotificationJob;
use App\Events\QuestionWasCreated;
use App\Models\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class AutomationWork
{
    public function handle(QuestionWasCreated $event)
    {
        try {
            $question = Question::find($event->question->id);
            $updatedQuestion = $this->aiResponse($question, $event->system);

//            if ($updatedQuestion->status != 'spam') {
////                DitectThread::threadCalculation($updatedQuestion);
//            }


        }catch (\Exception $exception){
//            Log::emergency($question->id . ' ' . $exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }
    }

    public function aiResponse($question, $system)
    {
        try{
            $result = Miscellaneous::callAIApi($question->id);
            $data = json_decode($result);

            if(isset($data) && $data->reason == 'repeat' && $question->parent_id == 0){

                $question->update([
                    'status' => 'spam'
                ]);
                Miscellaneous::createSpamEntity($question, $system->id, $data->reason, $question->source);

            } else if (isset($data) && $data->spam == true && $question->is_premium == 0 && $question->parent_id == 0) {
                $question->update([
                    'status' => 'spam'
                ]);
                Miscellaneous::createSpamEntity($question, $system->id, $data->reason, $question->source);

            }else if (isset($data) && isset($data->answer) && $data->spam == false && $data->answer[0]->probability*100 > 80) {
                $this->autoAnswer($question, $data);

                $this->createAiResponseLog($question->id, $data);
            }else{

                $this->autoRefer($question, $system, $data);
                $this->createAiResponseLog($question->id, $data);

            }

            if(isset($data) && $data->reason === 'predict')
                event(new CreatePointTransaction($question->user_id, 3));
            else
                event(new CreatePointTransaction($question->user_id, 1));

            return $question;
        }catch (\Exception $exception){
//            Log::emergency(json_encode($data) .' '. $exception->getMessage() . $exception->getFile() . $exception->getLine() . ' '.
//            $exception->getTraceAsString());
        }

    }

    public function autoAnswer($question, $data) {

//        dump('2');

        set_time_limit(0);


        try{

            $answer_body = $data->answer[0]->body;
            $answer['body'] = utf8_encode($answer_body);
            $answer['source'] = 'system';
            $answer['user_id'] = 25569;
            $answer['question_id'] = $question->id;
//        $question = Question::find($question_id);
            $asker = User::find($question->user_id);

//        $tags = $answer['tags'];

            if ($asker->is_premium == 0) {

                $new = Answer::create($answer);
                DB::table('questions_tags')->insert([
                    'tag_id' => $data->answer[0]->tag_id,
                    'question_id' => $question->id,
                    'created_at' => Carbon::now()
                ]);

                $question->status = 'answered';
                $question->save();


//            dump($question);

                Miscellaneous::UpdateResponseTime($answer['user_id'], $question->id);
                Miscellaneous::deleteFromLockedQueue($question->id);
                Miscellaneous::deleteFromDraft($question->id);

                ProcessAutoAnswer::dispatch($question, $new)->onConnection('database')->delay(now()->addMinutes(rand(3,5)));
//                ->delay(now()->addMinutes(rand(1,2)));

//                $ai_answer_log = null;
//                $client = new Client();
//                $promise = $client->getAsync('http://52.76.173.213/get_answer_similarity/' . $question->id)->then(
//
//                    function ($response) use ($new, &$ai_answer_log) {
//                        $response_data = json_decode($response->getBody());
//
//                        if (isset($response_data->data[0]->similarity) && $response_data->data[0]->similarity >= 80) {
//                            $ai_answer_log = AiAnswerLog::create([
//                                'answer_id' => $new->id,
//                                'expert_id' => $new->user_id,
//                                'similarity' => $response_data->data[0]->similarity
//                            ]);
//                        }
//                    }, function ($exception) {
////                    Log::emergency($exception->getMessage() . '' . $exception->getRequest());
//                }
//                );
//
//                $promise->wait();

//                sleep(rand(60,120));
//                event(new AnswerWasPost($question, $new, 'AnswerAndReferrerNotification'));

                $response = [
                    'status' => 'success',
                    'data' => $new
                ];
            }
        }catch (\Exception $exception){
//            Log::emergency("auto answer func".' '.json_encode($question) .' '. json_encode($data) .' '.  $exception->getMessage() . ' ' . $exception->getLine() . ' '.
//                $exception->getTraceAsString());
        }

    }

    private function createAiResponseLog($questionId, $data)
    {
        AIResponseLog::create([
            'question_id' => $questionId,
            'is_complex' => isset($data->is_complex) ? $data->is_complex : 0,
            'is_urgent' => isset($data->is_urgent) ? $data->is_urgent : 0,
            'refer' => isset($data->refer) ? $data->refer : null,
            'suggested_answer' => isset($data->answer) ? $data->answer : null,
            'tag_id' => isset($data->answer[0]->tag_id)? $data->answer[0]->tag_id :  null,
            'cluster_id' => isset($data->answer[0]->cluster_id) ? $data->answer[0]->cluster_id : null
        ]);
    }

    public function warningsCount($question, $system, $data)
    {
        $user = User::find($question->user_id);
        if (count($user)) {
            if ($user->warnings < 5) {
                $user->warnings = $user->warnings + 1;
                Miscellaneous::createSpamEntity($question, $system->id, $data->reason, $question->source);
                $reason = 'Spam';
            }else{
                $user->blocked = 1;
                Miscellaneous::createBlockEntity($user->id);
                $reason = 'Block';
            }

            dispatch(new UserNotification($question, $user->id, $system->id, $reason));

            $user->save();
        }
    }

    public function autoRefer($question, $system, $data)
    {
        $referred_to = null;
        $notification_type = 'ODE';

        if ($question->is_premium == 0){
            $referToReply = \DB::select(\DB::raw("SELECT user_id FROM answers WHERE question_id = (SELECT c.question_id FROM comments c JOIN comment_questions cq On c.id = cq.comment_id WHERE cq.question_id = {$question->id})"));
            if (!empty($referToReply)){
                $referred_to = $referToReply[0]->user_id;
                $notification_type = 'Auto Refer';
            }elseif (!empty($question->parent_id)){
                $answeredBY = Answer::where('question_id', $question->parent_id)->first();
                $referred_to = $answeredBY->user_id;
                $notification_type = 'Auto Refer';
            } elseif (!empty($data->refer) && isset($data->refer)){
                $topic = str_slug($data->refer, '-');
                $email = $topic . '@maya.com.bd';
                $referredTo = User::whereEmail(Helper::maya_encrypt($email))->first();
                $question->specialist_id = $referredTo->id;
                $question->save();
                $referred_to = $referredTo->id;
                $notification_type = 'No Auto Refer';
            }
        }else if($question->is_premium == 1){
            $referred_to = null;
            $notification_type = 'FTE+ODE';
        }

        dispatch(new ReferNotificationJob($question, $referred_to, $system->id, $notification_type));
    }

}
