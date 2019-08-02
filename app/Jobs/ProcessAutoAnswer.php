<?php

namespace App\Jobs;

use App\Events\AnswerWasPost;
use App\Models\AiAnswerLog;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessAutoAnswer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public $question, $new;
    /**
     * Create a new job instance.
     *
     * @return void
     */


    public function __construct($question, $new)
    {
        $this->question = $question;
        $this->new = $new;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $ai_answer_log = null;
        $client = new Client();
        $new = $this->new;
        $promise = $client->getAsync('http://52.76.173.213/get_answer_similarity/' . $this->question->id)->then(

            function ($response) use ($new, &$ai_answer_log) {
                $response_data = json_decode($response->getBody());

                if (isset($response_data->data[0]->similarity) && $response_data->data[0]->similarity >= 80) {
                    $ai_answer_log = AiAnswerLog::create([
                        'answer_id' => $new->id,
                        'expert_id' => $new->user_id,
                        'similarity' => $response_data->data[0]->similarity
                    ]);
                }

//                Log::emergency( 'auto answer' . json_encode($new));
            }, function ($exception) {
                    Log::emergency($exception->getMessage() . ' ' . $exception->getRequest());
        }
        );

        $promise->wait();

        event(new AnswerWasPost($this->question, $this->new, 'AnswerAndReferrerNotification'));

    }

    public function failed(\Exception $exception)
    {
        Log::emergency($exception->getMessage() . ' '. $exception->getLine() . ' '. $exception->getTraceAsString());
    }

//    public function autoAnswer($question, $data) {
//
////        dump('2');
//
//        set_time_limit(0);
//
//
//        try{
//
//            $answer_body = $data->answer[0]->body;
//            $answer['body'] = utf8_encode($answer_body);
//            $answer['source'] = 'system';
//            $answer['user_id'] = 25569;
//            $answer['question_id'] = $question->id;
////        $question = Question::find($question_id);
//            $asker = User::find($question->user_id);
//
////        $tags = $answer['tags'];
//
//            if ($asker->is_premium == 0) {
//
//                $new = Answer::create($answer);
//                DB::table('questions_tags')->insert([
//                    'tag_id' => $data->answer[0]->tag_id,
//                    'question_id' => $question->id,
//                    'created_at' => Carbon::now()
//                ]);
//
//                $question->status = 'answered';
//                $question->save();
//
////            dump($question);
//
//                Miscellaneous::UpdateResponseTime($answer['user_id'], $question->id);
//                Miscellaneous::deleteFromLockedQueue($question->id);
//                Miscellaneous::deleteFromDraft($question->id);
//
//
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
//
////                sleep(rand(60,120));
//                event(new AnswerWasPost($question, $new, 'AnswerAndReferrerNotification'));
//
//                $response = [
//                    'status' => 'success',
//                    'data' => $new
//                ];
//            }
//        }catch (\Exception $exception){
////            Log::emergency("auto answer func".' '.json_encode($question) .' '. json_encode($data) .' '.  $exception->getMessage() . ' ' . $exception->getLine() . ' '.
////                $exception->getTraceAsString());
//        }
//
//    }
}
