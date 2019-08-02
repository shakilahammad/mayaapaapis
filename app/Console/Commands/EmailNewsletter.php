<?php

namespace App\Console\Commands;

use App\Classes\Miscellaneous;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EmailNewsletter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:newsletter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Email Newsletter';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $data = $this->popularQuestion();
//        return view('emails.email_newsletter_questions_bn', $data);

        $users = $this->manualQuery();

//        $users = array('faruque@maya.com.bd', 'fahmidul@maya.com.bd');
//        $users = array('faruque@maya.com.bd');
//        dd($data);
        $last_id = 0;
        if(!empty($users))
            foreach ($users as $user) {
                \Mail::send('emails.email_newsletter_questions_bn', $data, function ($message) use ($data, $user) {
                    $message->to($user['email'])->subject('Weekly Popular Questions!');
                });
            }
    }

    private function popularQuestion(){
        $questions = Question::with(['answer'])
            ->select('id', 'body', 'user_id', 'status', 'type', 'created_at')
//            ->whereFeatured(1)
            ->whereIn('id', array(800273,800432, 800325, 800441, 800444, 800470))
            ->whereStatus('answered')
            ->orderby('updated_at', 'DESC')
//            ->take(10)
            ->get();

        $data = [
            'questions' => $this->getFormattedQuestions($questions)
        ];

        return $data;
    }

    private function manualQuery(){
        $questions = User::leftJoin('questions as q', 'users.id', '=', 'q.user_id')
            ->leftJoin('questions_tags as qt', 'qt.question_id', '=', 'q.id')
            ->select('users.email', 'users.id')
            ->distinct('users.id')
            ->whereRaw('qt.tag_id in (14, 28, 39, 40, 55, 79, 80, 87, 145, 146, 163)')
            ->where('q.status','answered')
            ->where('users.id', '>', 2025)
            ->where('users.id', '<=', 252623)
            ->whereNotNull('users.email')
//            ->orderby('q.updated_at', 'DESC')
            ->take(100)
            ->get();

        $data = [];
        $i = 0;

        foreach ($questions as $key => $question){
            if (is_null($question->email) || strpos($question->email, 'phone.com.bd') || strpos($question->email, 'facebook.com')) {
                unset($questions[$key]);
            }else{
//                if($i>0 && $data[$i-1]['id']!=$question->id){
                    $data[$i]['email'] = $question->email;
                    $data[$i]['id'] = $question->id;
                    $i++;
//                }
//                $data[] = $question->email;
            }
        }

        return $data;

    }

    public function getFormattedQuestions($questions)
    {
        $data = [];

        foreach ($questions as $question) {

            $values = [
                'id' => $question->id,
//                'body' => Miscellaneous::getQuestionBody($question),
                'body' => (mb_detect_encoding($question->body)=='ASCII') ? html_entity_decode(strip_tags($question->body)) : utf8_decode(strip_tags($question->body)),
                'user_id' => $question->answer['user_id'],
                'source' => $question->source,
                'status' => $question->status,
                'type' => $question->type,
                'question_time' => $this->formattedTime($question->created_at),
                'answeredBy' => $this->getAnsweredBy($question->answer['user_id']),
                'answer_body' => substr($question->answer->body, 0, 300),
                'answer_time' => $this->formattedTime(optional($question->answer)->created_at),
                'like_count' => $question->likes->count(),
                'comment_count' => $question->comments->count(),
            ];

            array_push($data, $values);
        }

        return $data;
    }

    public function formattedTime($time)
    {
        return Carbon::parse($time)->diffForHumans();
    }

    public function getAnsweredBy($expertId)
    {
        $expert = User::with('specialistProfile')->whereId($expertId)->get();

        if (isset($expert) && count($expert)) {
            return optional($expert[0]->specialistProfile)->shadow_name;
        }

        return 'Maya Apa';
    }
}