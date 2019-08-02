<?php

namespace App\Http\Controllers;

use App\Classes\MiscellaneousForApp;
use App\Models\ActiveAppUser;
use App\Models\Question;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{

    private function storeActivity($user_id)
    {
        try {
            $user = User::find($user_id);
            $user->update([
                'session' => 1
            ]);
        } catch (\Exception $exception) {

        }

        $records = ActiveAppUser::whereUserId($user_id)->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])->first();
        if (count($records)) {
            $records->update([
                'updated_at' => Carbon::now()
            ]);
        } else {
            ActiveAppUser::create([
                'user_id' => $user_id
            ]);
        }
    }

    public function fetchQuestionStream(Request $request, $type='pending', $offset = 0, $limit = 10, $direction = 1, $order = 'DESC', $status = null, $user_id = 0)
    {
        try{
            if (isset($user_id) && !empty($user_id)) {
                $this->storeActivity($user_id);
            }
//            dd('fetch'.ucfirst($type).'Stream');
            $ft = 'fetch'.ucfirst($type).'Stream';
            return response()->json($this->$ft($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id));
//            switch ($request->type) {
//                case 'answered':
//                    if ($status == 'default') {
//                        if (isset($request['text'])) {
//                            return response()->json($this->fetchSearchedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['text']));
//                        } else if (isset($request['date'])) {
//                            return response()->json($this->fetchDatedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['date']));
//                        } else if (isset($request['tag'])) {
//                            return response()->json($this->fetchTaggedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['tag']));
//                        } else {
//                            return response()->json($this->fetchAnsweredStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id));
//                        }
//                    } else {
//                        return response()->json($this->fetchPopularStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id));
//                    }
//                    break;
//                case 'pending':
//                    if ($status == 'default') {
//                        if (isset($request['text'])) {
//                            return response()->json($this->fetchSearchedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['text']));
//                        } else if (isset($request['date'])) {
//                            return response()->json($this->fetchDatedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['date']));
//                        } else if (isset($request['tag'])) {
//                            return response()->json($this->fetchTaggedStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id, $request['tag']));
//                        } else {
//                            return response()->json($this->fetchPendingStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id));
//                        }
//                    } else {
//                        return response()->json($this->fetchPopularStream($offset, $limit, ($direction == 1) ? '>' : '<', $order, $user_id));
//                    }
//                    break;
//            }
        }catch (\Exception $exception){

            $data = [
                'status' => 'failure',
                'message' => $exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine()
            ];

            return response()->json($data);
        }
    }

    private function fetchSearchedStream($offset, $limit, $direction, $order, $user_id, $word)
    {
//        $query = "select q.*,
//                       (select count(*) from likes where q.id=likes.question_id) as like_count,
//                            (select count(*) from comments where q.id=comments.question_id) as
//                                  comment_count,(select count(*) from likes where
//                                        likes.user_id = $user_id and likes.question_id = q.id) as is_liked
//                                              from questions as q where q.id not in (select question_id from question_hides where user_id = $user_id)
//                                              and  q.id $direction $offset and
//                                               q.status = 'answered' and q.body like '%{$word}%' order by q.id $order limit $limit";

//        $questions = DB::select(DB::raw($query));

//        $questions = User::modelsFromRawResults($questions);

        $questions = Question::with(['tags'])
            ->withCount(['likes as like_count', 'comments as comment_count'])
            ->selectRaw('(select count(*) from likes where likes.user_id = ' . $user_id . ' and likes.question_id = questions.id ) as is_liked')
//            ->where('questions.id', $direction, $offset)
//            ->whereRaw('questions.id NOT IN (SELECT distinct question_id FROM question_hides WHERE user_id = ' . $user_id . ')')
            ->whereNotExists(function ($query) use ($user_id){
                $query->select(DB::raw(1))
                    ->from('question_hides')
                    ->where('user_id', $user_id)
                    ->where('question_id', '=', 'questions.id');
            })
            ->where('questions.status', '=', 'answered' )
            ->where('questions.body', 'LIKE', '%'.$word.'%')
            ->orderBy('questions.id', $order)
            ->take($limit)
            ->get();


//        dd($questions);

        if (count($questions)) {
            return [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
        }

        return [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
    }

    private function fetchDatedStream($offset, $limit, $direction, $order, $user_id, $date)
    {
        $query = "select q.*,
                       (select count(*) from likes where q.id=likes.question_id) as like_count,
                            (select count(*) from comments where q.id=comments.question_id) as 
                                  comment_count,(select count(*) from likes where 
                                        likes.user_id = $user_id and likes.question_id = q.id) as is_liked 
                                              from questions as q where q.id $direction $offset and q.status = 'answered'
						and q.created_at > DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)
                                                and q.id not in (select question_id from question_hides where user_id = $user_id)
                                                and q.created_at like '%{$date}%' order by q.id $order limit $limit";
        $questions = DB::select(DB::raw($query));
        if (count($questions)) {
            return [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
        }

        return [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
    }

    private function fetchTaggedStream($offset, $limit, $direction, $order, $user_id, $tag)
    {
        $tag_names = $this->getTagNames($tag);
        $query1 = "select id from tags where name_en regexp $tag_names";
        $tags = DB::select(DB::raw($query1));
        $tag_ids = '';
        for ($i = 0; $i < count($tags); $i++) {
            if ($i != count($tags) - 1) {
                $tag_ids .= $tags[$i]->id . ',';
            } else {
                $tag_ids .= $tags[$i]->id;
            }
        }

        $query = "select q.*,
                       (select count(*) from likes where q.id=likes.question_id) as like_count,
                            (select count(*) from comments where q.id=comments.question_id) as 
                                  comment_count,(select count(*) from likes where 
                                        likes.user_id = 0 and likes.question_id = q.id) as is_liked 
                                              from questions as q where q.id $direction $offset and
                                                                 q.id not in (select question_id from question_hides where user_id = $user_id) 
                                                              and q.id in (select question_id from questions_tags where tag_id in ($tag_ids))
                                                              
                                                              order by q.id desc limit $limit";
        $questions = DB::select(DB::raw($query));
        if (count($questions)) {
            return [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
        }
        return [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
    }

    private function getTagNames($name)
    {
        switch ($name) {
            case 'General Health':
                return '"' . "Breast Diseases|Before Pregnancy|Women's Health|Health|Nutrition|Men's Health|Mental Health|Teen Health|Cancer & Terminal Diseases" . '"';
                break;
            case 'Sex Education':
                return '"' . "Sex Education|Contraception & Family Planning|Menstruation|STIs" . '"';
                break;
            case 'Pregnancy':
                return '"' . "Getting Pregnant|Pregnancy|Post Pregnency" . '"';
                break;
            case 'Lifestyle':
                return '"' . "Beauty & Skin Care|Fitness & Well-being|Dermatology" . '"';
                break;
            case 'Parenting Children':
                return '"' . "Baby care & Vaccination|Parenting & Breastfeeding|Parenting & Parenthood" . '"';
                break;
            case 'Social Issues':
                return '"' . "Sexual Harrassment at Workplace|Social Issues|Psychosocial|Legal|Domestic Violence/ Violence at Workplace|Divorce & Child Support|Psychosocial|Drugs & Substance Abuse" . '"';
                break;
            case'Others':
                return '"' . "Other|General Info" . '"';
                break;
        }
    }

    private function fetchAnsweredStream($offset, $limit, $direction, $order, $user_id)
    {
        $questions = Question::with(['Tags'])->selectRaw('questions.*, (select count(*) from likes where likes.question_id = id) as like_count, (select count(*) from comments where comments.question_id = id) as comment_count, (select count(*) from likes where likes.user_id = ' . $user_id . ' and likes.question_id = id) as is_liked')
            ->where('status', 'answered')
            ->where('id', $direction, $offset)
            ->where('source', '<>', 'robi')
            ->whereRaw('created_at > DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)')
            ->whereRaw('questions.id NOT IN (SELECT question_id FROM question_hides WHERE user_id = ' . $user_id . ')')
            ->take($limit)
            ->orderBy('id', $order)
//            ->toSql();
            ->get();

//        dd($questions);

        if (count($questions)) {
            $response = [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
            return $response;
        } else {
            $response = [
                'status' => 'failed',
                'data' => [],
                'error_code' => 0,
                'error_message' => '',
            ];
            return $response;
        }
    }

    private function fetchPopularStream($offset, $limit, $direction, $order, $user_id)
    {
        $questions = Question::with(['tags','answer'])
            ->withCount(['likes as like_count', 'comments as comment_count'])
            ->selectRaw('(select count(*) from likes where likes.user_id = ' . $user_id . ' and likes.question_id = questions.id ) as is_liked')
            ->where('featured', 1)
            ->where('questions.id', $direction, $offset)
            ->take($limit)
            ->whereRaw('questions.id NOT IN (SELECT question_id FROM question_hides WHERE user_id = ' . $user_id . ')')
            ->orderBy('updated_at', $order)
            ->get();
//        dd($questions);

        if (count($questions)) {
            $response = [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
            return $response;
        }
        $response = [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
        return $response;
    }

    private function fetchPendingStream($offset, $limit, $direction, $order, $user_id)
    {
        $query = "select q.*,
                       (select count(*) from likes where q.id=likes.question_id) as like_count,
                            (select count(*) from comments where q.id=comments.question_id) as 
                                  comment_count,(select count(*) from likes where 
                                        likes.user_id = $user_id and likes.question_id = q.id) as is_liked 
                                              from questions as q where q.id $direction $offset
                                              and q.id not in (select question_id from question_hides where user_id = $user_id) and
                                              q.status = 'pending' order by q.id $order limit $limit ";
        $questions = DB::select(DB::raw($query));

        if (count($questions)) {
            return [
                'status' => 'success',
                'data' => $this->fetchRequiredData($questions),
                'error_code' => 0,
                'error_message' => '',
            ];
        }

        return [
            'status' => 'failed',
            'data' => [],
            'error_code' => 0,
            'error_message' => '',
        ];
    }

    private function fetchRequiredData($questions)
    {
        $data = [];
        foreach ($questions as $question) {

//            dd($question->tags);
            list($area, $city, $country, $address) = MiscellaneousForApp::getFormattedLocation($question);

            if (empty($country) || $country == 'Bangladesh'){
                $country = '';
            }

            $values = [
                'id' => $question->id,
                'body' => html_entity_decode(utf8_decode(strip_tags($question->body))),
                'source' => $question->source == null ? 0 : $question->source,
                'status' => $question->status == null ? 0 : $question->status,
                'user_id' => $question->user_id,
                'type' => $question->type == null ? 0 : $question->type,
                'tags' => isset($question->tags) ? $question->tags : null,
//                'city' => $city == null ? '' : $city,
//                'country' => $country == null ? '' : $country,
                'city' => $city == null ? '' : trim(str_replace('District', '', $city)),
                'country' => $country,
                'is_liked' => $question->is_liked,
                'is_premium' => $question->is_premium,
                'media_id' => $question->media_id,
                'question_created_at' => Carbon::parse($question->created_at)->diffForHumans(),
                'like_count' => $question->like_count,
                'comment_count' => $question->comment_count,
                'question_theme_type' => $question->theme_type ?? 0

            ];
            array_push($data, $values);
        }

        return $data;
    }
}
