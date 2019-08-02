<?php

namespace App\Http\Controllers;

use App\Classes\Miscellaneous;
use App\Models\Answer;
use App\Models\User;
use Carbon\Carbon;
use Doctrine\DBAL\Query\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ExpertProfileController extends Controller
{
    //

    public function expertProfile($expertId)
    {

        $data = null;
        $meta_links = null;

        try{
            $expert = User::with(['profilePicture', 'specialistProfile'])->whereIn('type', ['specialist', 'admin'])->find($expertId);

            $rating = DB::select("select avg(r.rating) as rating from ratings r, answers a 
                              where r.question_id = a.question_id and a.user_id = $expertId 
                              and a.created_at < NOW() and a.created_at > DATE_ADD(Now(), INTERVAL- 6 MONTH)");
            $star = (int)$rating[0]->rating;


            $chart = DB::select("select l1.name_en as name, count(qt.question_id) as number from layer_one l1, layer_two l2, 
                              questions_tags qt, tags t, answers a where qt.tag_id = t.id and 
                              t.layer_two_id = l2.id and l2.layer_one_id = l1.id and 
                              a.question_id = qt.question_id and a.user_id = $expertId 
                              group by l1.name_en");

            $total_answered = DB::select("select count(*) as total from answers where user_id = $expertId");


            if (count($expert)) {
//                $answers = Answer::with(['question', 'question.likes', 'question.comments'])->where('user_id', $expertId)->orderby('created_at', 'DESC')->paginate(6);
//            $data = $this->formattedQuestionAnswer($answers);
//                $meta_links = $answers->links();
            }

        }catch (Exception $exception){
//            dd($exception);
            $data = [
                'description' => 'Maya is a Bangladesh based technology company dedicated to connecting women with the information they are looking for, when they are looking for it. Maya’s high quality health and social content along with on­demand Q&A service, Maya Apa, helps break down barriers for women.',
                'title' => 'Maya Apa | Doctor Profile',
                'expert' => null,
//            'answers' => $data,
                'meta_links' => null,
                'ratings' => null,
                'charts' => null,
                'totalQuestion' => null,
            ];

            return view('expertProfile')->with($data);
        }

        $data = [
            'description' => 'Maya is a Bangladesh based technology company dedicated to connecting women with the information they are looking for, when they are looking for it. Maya’s high quality health and social content along with on­demand Q&A service, Maya Apa, helps break down barriers for women.',
            'title' => 'Maya Apa | Doctor Profile',
            'expert' => $expert,
//            'answers' => $data,
            'meta_links' => null,
            'ratings' => $star,
            'charts' => $chart,
            'totalQuestion' => $total_answered[0]->total,
        ];

        return view('expertProfile')->with($data);
    }

    public function formattedQuestionAnswer($answers)
    {
        $data = [];
        foreach ($answers as $answer) {
            $question = $answer->question;
            if($question->is_prescription==0)
                $values = [
                    'id' => $question->id,
                    'question_body' => Miscellaneous::getQuestionBody($question),
                    'source' => $question->source,
                    'status' => $question->status,
                    'type' => $question->type,
                    'question_time' => $this->formattedTime($question->created_at),
                    'answer_body' => $answer->body,
                    'answer_time' => $this->formattedTime($answer->created_at),
                    'like_count' => $question->likes->count(),
                    'comment_count' => $question->comments->count(),
                ];

            array_push($data, $values);
        }

        return $data;
    }

    public function formattedTime($time)
    {
        return Carbon::parse($time)->format('d M Y');
    }
}
