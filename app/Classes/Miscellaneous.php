<?php

namespace App\Classes;

use App\Events\CreatePointTransaction;
use App\Http\Helper;
use App\Models\ActiveAppUser;
use App\Models\AIResponseLog;
use App\Models\FollowUp;
use App\Models\Group;
use App\Models\Location;
use App\Models\Question;
use App\Models\Refer;
use App\Models\User;
use App\Models\Answer;
use App\Models\Medium;
use App\point_transactions;
use App\user_points;
use Carbon\Carbon;
use App\Models\Spam;
use App\Models\Block;
use App\Models\LockQueue;
use App\Models\SendSMSLog;
use App\Models\DraftAnswer;
use App\Models\Subscribers;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;
use App\Models\ResponseTime;
use App\Models\QuestionSource;
use App\Events\PremiumQuestion;
use Illuminate\Support\Facades\DB;
use App\Models\NotificationMessage;

class Miscellaneous
{

    protected static $automationUrl = "http://52.76.173.213/spam/";

    /**
     * Get formatted questions
     *
     * @param array $questions
     * @return array
     */
    public static function getFormattedUnAnsweredQuestions($questions)
    {
        $data = [];
        foreach ($questions as $question) {

            $question_body = self::getQuestionBody($question);

            // Formatted location
            list($area, $city, $country, $address) = self::getFormattedLocation($question);

            // Referred info
            list($referred_to, $referred_by, $referred_time) = self::referrerInfo($question);

            // Get tags
            $tags = self::getTags($question);

            $question_time = Carbon::parse($question->created_at)->format('d M Y   D g:i A');

            $isSendSms = self::isSendSms($question);

            $ai_response = AIResponseLog::where('question_id', $question->id)->first();

            $values = [
                'id' => $question->id,
                'question_body' => $question_body,
                'source' => $question->source,
                'status' => $question->status,
                'user_id' => isset($question->user_id) ? $question->user_id : 0,
                'specialist_id' => isset($question->specialist_id) ? $question->specialist_id : 0,
                'parent_id' => $question->parent_id,
                'city' => $city,
                'area' => $area,
                'country' => $country,
                'address' => $address,
                'featured' => isset($question->featured) ? $question->featured : 0,
                'resolved' => isset($question->resolved) ? $question->resolved : 0,
                'type' => $question->type,
                'media_id' => $question->media_id,
                'is_premium' => isset($question->is_premium) ? $question->is_premium : 0,
                'tags' => $tags,
                'service_holder' => $question->service_holder,
                'question_time' => $question_time,
                'referred_to' => $referred_to,
                'referred_by' => $referred_by,
                'referred_time' => $referred_time,
                'isSendSms' => $isSendSms,
                'is_complex' => $question->is_complex,
                'is_urgent' => $question->is_urgent,
                'suggested_answer' => empty($ai_response) ? '' : $ai_response->suggested_answer,
                'has_suggestion' =>  empty($ai_response->suggested_answer) ? false : true
            ];

            array_push($data, $values);
        }

        return $data;
    }

    /**
     * Get formatted answered question
     *
     * @param $questions
     * @return array
     */
    public static function getFormattedAnsweredQuestions($questions)
    {
        $data = [];
        foreach ($questions as $question) {

            $question_body = self::getQuestionBody($question);

            // Formatted location
            list($area, $city, $country, $address) = self::getFormattedLocation($question);

            // Referred info
            list($referred_to, $referred_by, $referred_time) = self::referrerInfo($question);

            // Get answered info
            list($answer_body, $answer_time, $answered_by, $drafted, $updated) = self::getAnsweredInfo($question);

            // Get tags
            $tags = self::getTags($question);

            // Get images of the question
            $image = Medium::whereId($question->media_id)->get();

            $question_time = Carbon::parse($question->created_at)->format('d M Y   D g:i A');

            $isSendSms = self::isSendSms($question);

            $ai_response = AIResponseLog::where('question_id', $question->id)->first();

            $values = [
                'id' => $question->id,
                'question_body' => $question_body,
                'source' => $question->source,
                'status' => $question->status,
                'user_id' => isset($question->user_id) ? $question->user_id : 0,
                'specialist_id' => isset($question->specialist_id) ? $question->specialist_id : 0,
                'parent_id' => $question->parent_id,
                'city' => $city,
                'area' => $area,
                'country' => $country,
                'address' => $address,
                'featured' => isset($question->featured) ? $question->featured : 0,
                'resolved' => isset($question->resolved) ? $question->resolved : 0,
                'type' => $question->type,
                'media_id' => $question->media_id,
                'media' => !empty($image) ? $image : 0,
                'is_premium' => isset($question->is_premium) ? $question->is_premium : 0,
                'tags' => $tags,
                'question_time' => $question_time,
                'referred_to' => $referred_to,
                'referred_by' => $referred_by,
                'referred_time' => $referred_time,
                'drafted' => isset($drafted) ? $drafted : 0,
                'updated' => isset($updated) ? $updated : 0,
                'answer_body' => isset($answer_body) ? $answer_body : null,
                'answered_by' => $answered_by,
                'answer_time' => isset($answer_time) ? $answer_time : null,
                'isSendSms' => $isSendSms,
                'is_complex' => $question->is_complex,
                'is_urgent' => $question->is_urgent,
                'suggested_answer' => empty($ai_response) ? '' : $ai_response->suggested_answer,
                'has_suggestion' =>  empty($ai_response->suggested_answer) ? false : true
            ];
            
            array_push($data, $values);
        }

        return $data;
    }

    public static function getFormattedQuestionsForListing($questions)
    {
        $data = [];
        foreach ($questions as $question) {
            $question_body = self::getQuestionBody($question);
            list($referred_to, $referred_by, $referred_time) = self::referrerInfo($question);
            $values = [
                'id' => $question->id,
                'question_body' => $question_body,
                'source' => $question->source,
                'status' => $question->status,
                'specialist_id' => $question->specialist_id,
                'type' => $question->type,
                'question_time' => Carbon::parse($question->created_at)->format('d M Y   D g:i A'),
                'referred_to' => $referred_to,
                'referred_by' => $referred_by,
                'referred_time' => $referred_time,
                'is_premium' => $question->is_premium,
                'is_prescription' => $question->is_prescription,
                'is_complex' => $question->is_complex,
                'is_urgent' => $question->is_urgent,
            ];

            array_push($data, $values);
        }

        return $data;
    }

    /**
     * Update Response Time
     *
     * @param $user_id
     * @param $question_id
     */
    public static function UpdateResponseTime($user_id, $question_id)
    {
        $updateResponseTime = ResponseTime::whereUserId($user_id)->whereQuestionId($question_id)->orderBy('created_at', 'desc')->first();
        if (count($updateResponseTime)) {
            $updateResponseTime->update([
                'end' => Carbon::now()
            ]);
        };
    }


    /**
     * Delete response time
     *
     * @param $question_id
     * @param $specialist_id
     */
    public static function deleteResponseTime($question_id, $specialist_id)
    {
        $expert = is_string($specialist_id) ? (int) $specialist_id : $specialist_id;
        DB::table('response_time')->where('user_id', $expert)->where('question_id', $question_id)->delete();
    }

    /**
     * Get question body
     *
     * @param $question
     * @return string
     */
    public static function getQuestionBody($question)
    {
        if (mb_detect_encoding($question->body) == "ASCII") {
            return utf8_decode(strip_tags($question->body));
        }elseif (mb_detect_encoding($question->body) == 'UTF-8'){
            return htmlspecialchars(utf8_decode(strip_tags($question->body)), ENT_IGNORE);
        }else {
            return htmlspecialchars(utf8_decode(strip_tags($question->body)), ENT_IGNORE);
        }
    }

    /**
     * Get Referred info
     *
     * @param $question
     * @return array
     */
    public static function referrerInfo($question)
    {
        $referred_to = 0; $referred_by = 0; $referred_time = 0;
        try {
            if (isset($question->refer)) {
                list($referred_to, $referred_by, $referred_time) = self::referInfo($question->refer->referred_to, $question->refer->referred_by, $question->refer->created_at);
                return [$referred_to, $referred_by, $referred_time];
            }else if($question->specialist_id > 0) {
                $referredByAndReferredTime = Refer::whereQuestionId($question->id)->first();
                list($referred_to, $referred_by, $referred_time) = self::referInfo($referredByAndReferredTime->referred_to, $referredByAndReferredTime->referred_by, $referredByAndReferredTime->created_at);
                return [$referred_to, $referred_by, $referred_time];
            }else{
                return [$referred_to, $referred_by, $referred_time];
            }
        }catch (\Exception $exception){
            return [$referred_to, $referred_by, $referred_time];
        }
    }

    public static function referInfo($referred_to, $referred_by, $referrred_time)
    {
        $specialist = User::find($referred_to);
        $referredTo = $specialist->f_name . ' ' . $specialist->l_name;
        $referredBy = User::find($referred_by);
        $referredBy = $referredBy->f_name . ' ' . $referredBy->l_name;
        $referredTime = Carbon::parse($referrred_time)->format('d M Y  D g:i A');

        return [
            $referredTo,
            $referredBy,
            $referredTime
        ];
    }

    /**
     * Get answered info
     *
     * @param $question
     * @return array
     */
    public static function getAnsweredInfo($question)
    {
        $answer_body = null; $answer_time = null; $answered_by = null;
        $answerDetails = Answer::whereQuestionId($question->id)->first();

        if (count($answerDetails)) {
            $answer_body = ($question->source == 'robi') ? strip_tags($answerDetails->body) : $answerDetails->body;
            $answer_time = Carbon::parse($answerDetails->updated_at)->format('d M Y   D g:i A');
            $user = User::find($answerDetails->user_id);
            $answered_by = !count($user) ? "Maya Team" : $user->f_name . ' ' . $user->l_name;
            $updated = $question->status == 'answered' ? 0 : 1;
            return [$answer_body, $answer_time, $answered_by, 0, $updated];
        }

        if ($question->status == 'pending') {
            $draftAnswer = DraftAnswer::whereQuestionId($question->id)->first();
            if (count($draftAnswer)) {
                $answer_body = $draftAnswer->body;
                $answer_time = Carbon::parse($draftAnswer->updated_at)->format('d M Y   D g:i A');
                $user = User::find($draftAnswer->specialist_id);
                $answered_by = $user->f_name . ' ' . $user->l_name;
                return [$answer_body, $answer_time, $answered_by, 1, 0];
            }
        }

        return [$answer_body, $answer_time, $answered_by, 0, 0];
    }

    /**
     * Get Tags
     *
     * @param $question
     * @return mixed
     */
    public static function getTags($question)
    {
        $tags = DB::select(DB::raw('select tags.id as id, tags.name_en as name_en, tags.name_bn as name_bn from tags join questions_tags on tags.id = questions_tags.tag_id where questions_tags.question_id = ' . $question->id));
        return $tags;
    }

    /**
     * Get formatted question location
     *
     * @param $question
     * @return array
     */
    public static function getFormattedLocation($question)
    {
        $location = Location::find($question->location_id);

        if (!count($location)){
            return [null, null, null, null];
        }

        return [
            $location->area,
            $location->city,
            $location->country,
            $location->area .' '. $location->city .' '. $location->country
        ];
    }

    /**
     * Check send sms
     *
     * @param $question
     * @return int
     */
    public static function isSendSms($question)
    {
        if ($question->is_premium == 1) {
            return isset($question->sendSmsLogs) ? 1 : 0;
        }

        return 0;
    }

    /**
     * Create Send sms log for robi
     *
     * @param $question
     * @param $specialist_id
     * @param $type
     * @return boolean
     */
    public static function createSendSmsLog($question, $specialist_id, $type)
    {
        if (!SendSMSLog::whereQuestionId($question->id)->exists()) {
            Miscellaneous::callApiForSendingSMS($question, $type);
            SendSMSLog::create([
                'question_id' => $question->id,
                'specialist_id' => $specialist_id,
                'type' => $type
            ]);

            return true;
        }

        return false;
    }

    /**
     * create Spam Entity for further tracking
     *
     * @param $question
     * @param $expert_id
     * @param $type
     * @param $source
     */
    public static function createSpamEntity($question, $expert_id, $type, $source)
    {
        Spam::updateOrCreate(
            ['question_id' => $question->id],
            ['expert_id' => $expert_id, 'type' => $type, 'source' => $source]
        );
    }

    /**
     * create Spam Entity for further tracking
     *
     * @param $user_id
     */
    public static function createBlockEntity($user_id)
    {
        if (!Block::whereUserId($user_id)->exists()) {
            Block::create(['user_id' => $user_id]);
        }
    }

    /**
     * Trigger pusher
     *
     * @param $question
     * @param $type
     */
    public static function realtimeUpdate($question, $type, $is_paid = false)
    {

        try{
            $expert = auth()->user();

            $options = [
                'cluster' => 'ap1',
                'encrypted' => true
            ];

            $pusher = new \Pusher(
                config('admin.pusher.public_key'),
                config('admin.pusher.secret_key'),
                config('admin.pusher.app_id'), $options
            );

//        dump($pusher);
//        dump('pusher');

            $questionData = Miscellaneous::getFormattedQuestionsForListing($question);

            $data['message'] = [
                'type' => $type,
                'question' => $questionData,
                'is_paid' => $is_paid
            ];

//        Log::emergency(isset($expert) && ($expert->isAdmin() || $expert->isFTE()) . ' ' . isset($expert) .' ' . ' ' .$expert->isAdmin() .' '. $expert->isFTE());

//        Log::emergency(isset($expert) && ($expert->isAdmin() || $expert->isFTE()) . ' ' . isset($expert) .' '.  $expert->isAdmin() .' '. $expert->isFTE() );

//        if(isset($question->is_prescription) && $question->is_prescription == 1){
//            if (isset($expert) && ($expert->isAdmin() || $expert->isFTE())){
//                Log::emergency('mayaapi pusher');
////                $pusher->trigger(['new-question'], PremiumQuestion::class, $data);
//            }
//        }else{
//            Log::emergency('mayaapi pusher else'. json_encode($expert));
            $pusher->trigger(['new-question'], PremiumQuestion::class, $data);
//            Log::emergency("new-question channel testing" . json_encode($data));
//        }
        } catch (\Exception $exception){
//            Log::emergency($exception->getMessage() .' '. $exception->getFile() .' '. $exception->getLine());
        }


    }

    public static function realTimeUpdateForReferredQuestions()
    {
        $refers = Refer::displayableQuestions(auth()->user()->id)->get();
        $referred_questions = [];
        foreach ($refers as $refer) {
            if(!empty($refer->question) && $refer->question->status == 'pending' && $refer->question->is_premium == 1){
                array_push($referred_questions, $refer->question);
            }
        }

        $myQuestion = count($referred_questions) ? Miscellaneous::getFormattedUnAnsweredQuestions($referred_questions) : null;

        $options = [
            'cluster' => 'ap1',
            'encrypted' => true
        ];

        $pusher = new \Pusher(env('PUSHER_KEY'), env('PUSHER_SECRET'), env('PUSHER_APP_ID'), $options);

        $data = [
            'status' => 'success',
            'myQuestion' => $myQuestion
        ];

        $pusher->trigger(['my-question'], PremiumQuestion::class, $data);
    }

    public static function realTimeUpdateForLockedQuestion()
    {
        $answeringNow = LockQueue::getPremiumAnsweringNow();

        $options = [
            'cluster' => 'ap1',
            'encrypted' => true
        ];

        $pusher = new \Pusher(env('PUSHER_KEY'), env('PUSHER_SECRET'), env('PUSHER_APP_ID'), $options);

        $data = [
            'status' => 'success',
            'answeringNow' => $answeringNow
        ];

        $pusher->trigger(['answering-now'], PremiumQuestion::class, $data);
    }

    public static function checking_badge_point($user_id, $next_batch_id)
    {
        $user_data = user_points::where("user_id", "=", $user_id)->first();
        $user_total_points = $user_data->total_points;

        $user_badge = DB::table("point_badges")
            ->where("id", "=", $next_batch_id)
            ->first();
//        dd($user_badge);
        $required_points = $user_badge->required_points;
        if ($user_total_points >= $required_points) {
            return 1;
        } else {
            return 0;
        }
    }



    public static function  chacking_badge_criteria($user_id, $next_batch_id){
        $criterion_for_badge = DB::table("point_badge_criterion")->where("badge_id","=",$next_batch_id)->get();
        $criterion_data = array();
        $user_data = array();

        foreach ($criterion_for_badge as $value){
            $source_id= $value->source_id ;
            $num_of_transaction=$value->num_of_transaction;
            $criterion_data[$source_id]=$num_of_transaction;
            $user_data[$source_id] = 0;
        }

        $user_transaction_entry = DB::table("point_transactions")
            ->select(DB::raw('count(*) as user_count, source_id'))
            ->where("user_id","=",$user_id)
            ->groupBy("source_id")
            ->get();

        foreach ($user_transaction_entry as $value){
            $source_id = $value->source_id;
            $num_of_transaction = $value->user_count;
            $user_data[$source_id] = $num_of_transaction;
        }
        #code update

        $return_data=1;
        foreach ($criterion_data as $key => $value) {
            $user_transactions = $user_data[$key];
            if($user_transactions < $value){
                $return_data =0;
                break;
            }

        }

        return $return_data;
    }


    /**
     * Delete from draft table
     *
     * @param $question
     */
    public static function deleteFromDraft($question)
    {
        if(isset($question->draft_answer) && count($question->draft_answer)) {
            $question->draft_answer->delete();
        }
    }

    /**
     * Delete From Locked Queue
     *
     * @param $question_id
     */
    public static function deleteFromLockedQueue($question_id)
    {
        $lockedQueue = LockQueue::whereQuestionId($question_id)->first();
        if ($lockedQueue){
            $lockedQueue->delete();
        }
    }

    /**
     * Show tips subscription info in answer panel
     *
     * @param $emailOrPhone
     * @return null
     */
    public static function tipsInfo($emailOrPhone)
    {
        $array = config('config.product_id');
        $subscriber = Subscribers::where('phone_number', $emailOrPhone)->first();
        $data = [];
        if ($subscriber){
            if (count($subscriber->tips_subscribers)){
                $tips = $subscriber->tips_subscribers;
                foreach ($tips as $tip){
                    if(array_key_exists($tip->product_id, $array)){
                        $tipsInfo = $array[$tip->product_id];
                        array_push($data, $tipsInfo);
                    }
                }
            }
            return $data;
        }

        return null;
    }


    /**
     * Get notification id
     *
     * @param $type
     * @return null
     */
    public static function getNotificationMessageId($type)
    {
        $notificationType = NotificationMessage::whereType($type)->first();

        if (count($notificationType)){
            return $notificationType->id;
        }

        return null;
    }

    /**
     * Create Or Update Refer
     *
     * @param $question_id
     * @param $referred_to
     * * @param $referred_by
     */
    public static function createOrUpdateRefer($question_id, $referred_to, $referred_by)
    {
        $refer = Refer::whereQuestionId($question_id)->first();
        if (count($refer)) {
            $refer->update(['referred_to' => $referred_to, 'referred_by' => $referred_by]);
        } else {
            Refer::create(['question_id' => $question_id, 'referred_to' => $referred_to, 'referred_by' => $referred_by]);
        }
    }

    /**
     * Get psychosocial refer questions
     *
     * @param $specialist_id
     * @return null
     */
    public static function getGroupExpert($specialist_id)
    {
        try{
            $expert = User::find($specialist_id);
            $group = Group::find($expert->expertGroup->group_id);
            switch ($group){
                case $group->name == 'Psychosocial':
                    return User::select('id')->whereEmail(Helper::maya_encrypt('psychosocial@maya.com.bd'))->first();
                case $group->name == 'Legal':
                    return User::select('id')->whereEmail(Helper::maya_encrypt('legal@maya.com.bd'))->first();
                case $group->name == 'Public Health':
                    return User::select('id')->whereEmail(Helper::maya_encrypt('public-health@maya.com.bd'))->first();
                case $group->name == 'Medical':
                    return User::select('id')->whereEmail(Helper::maya_encrypt('medical@maya.com.bd'))->first();
                case $group->name == 'Beauty':
                    return User::select('id')->whereEmail(Helper::maya_encrypt('beauty@maya.com.bd'))->first();
                case $group->name == 'Pediatric':
                    return User::select('id')->whereEmail(Helper::maya_encrypt('pediatric@maya.com.bd'))->first();
                case $group->name == 'Gynecology':
                    return User::select('id')->whereEmail(Helper::maya_encrypt('gynecology@maya.com.bd'))->first();
                case $group->name == 'Tech':
                    return User::select('id')->whereEmail(Helper::maya_encrypt('tech@maya.com.bd'))->first();
                case $group->name == 'Dental':
                    return User::select('id')->whereEmail(Helper::maya_encrypt('dental@maya.com.bd'))->first();
                default:
                    return null;
            }
        }catch (\Exception $exception){
            return null;
        }
    }

    /**
     * Create Follow Up
     *
     * @param $question
     * @param $answer
     */
    public static function createFollowUp($question, $answer)
    {
        FollowUp::create([
            'question_id' => $question->id,
            'specialist_id' => $answer['user_id'],
            'notify_at' => Carbon::now()->addDays($answer['followup_time']),
            'specialist_is_notified' => false
        ]);
    }

    /**
     * Track users source
     *
     * @param $data
     * @return boolean
     */
    public static function trackSource($data)
    {
        try {
            $agent = new Agent();
            $browser = $agent->browser();
            $browserVersion = $agent->version($browser);
            $platform = $agent->platform();
            $platformVersion = $agent->version($platform);

            QuestionSource::create([
                'question_id' => $data->id,
                'device_name' => $agent->device(),
                'platform' => $platform,
                'platform_version' => $platformVersion,
                'browser' => $browser,
                'browser_version' => $browserVersion,
                'is_desktop' => $agent->isDesktop(),
                'is_tablet' => $agent->isTablet(),
                'is_mobile' => $agent->isMobile(),
                'is_phone' => $agent->isPhone(),
                'is_robot' => $agent->isRobot(),
            ]);

            return true;

        }catch (\Exception $exception){
            return false;
        }
    }

    public static function storeActiveUser($user_id, $version = '')
    {
        $records = ActiveAppUser::whereUserId($user_id)->whereBetween('created_at', [Carbon::now()->startOfDay(), Carbon::now()->endOfDay()])->first();
        if (count($records)) {
            $records->update([
                'updated_at' => Carbon::now(),
                'version' => $version
            ]);
        } else {
            ActiveAppUser::create([
                'user_id' => $user_id,
                'version' => $version
            ]);
        }
    }

    public static function callAIApi($questionId)
    {
        $url = self::$automationUrl. $questionId;

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_URL => $url
        ]);
        $results = curl_exec($curl);
        curl_close ($curl);

        return $results;
    }


    /**
     * Send Answer to IOD user
     *
     * @param $question
     * @param $answer
     */
    public static function sendAnswerToIODUser($question, $answer)
    {
        if ($question->source == 'robi' || $question->source == 'airtel') {
            $url = "http://43.240.103.21/robi/send/sms";
            $answerBodyWithoutTags = strip_tags($answer->body);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['question_id' => $question->id, 'answer' => $answerBodyWithoutTags]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close ($ch);
        }
    }

    public static function createAutomaticFollowUp($question, $answer)
    {
        try{
//            $ques = Question::findOrFail($question->id);
            $parent_followup = Question::whereId($question->id)->where('parent_id', '!=', 0)->first();

            if(!count($parent_followup)){
                FollowUp::create([
                    'question_id' => $question->id,
                    'specialist_id' => $answer['user_id'],
                    'notify_at' => Carbon::now()->addDays(1),
                    'specialist_is_notified' => false
                ]);
            }
        }catch (\Exception $exception){
//            Log::emergency($exception->getMessage() . $exception->getFile() . $exception->getLine());
        }


    }

    public static function create_transaction($user_id, $source_id){

//        $user_id = $user_id;
//        $source_id = $source_id;

        $get_source_detail =DB::table("point_sources")
            ->where('id','=', $source_id)
            ->get();

        $title_en = $get_source_detail[0]->title_en;
        $title_bn = $get_source_detail[0]->title_bn;
        $message_en = $get_source_detail[0]->message_en;
        $message_bn = $get_source_detail[0]->message_bn;
        $sub_title_en = $get_source_detail[0]->sub_title_en;
        $sub_title_bn = $get_source_detail[0]->sub_title_bn;
        $type = $get_source_detail[0]->type;
        $point = $get_source_detail[0]->point;
        $action_type = $get_source_detail[0]->action_type;

        $user_data = user_points::where('user_id', $user_id)->first();  //getting user data

        $total_points =$point;
        if ($user_data !=null){
            $total_points=$user_data->total_points + $total_points;
            $user_data->total_points = $total_points;
            $user_data->save();
            #send_push()

        }else{
            $user_points = new user_points;
            $user_points->user_id= $user_id;
            $user_points->total_points = $total_points;
            $user_points->save();
            #send_push()
        }

        $point_transactions = new point_transactions();
        $point_transactions->user_id = $user_id;
        $point_transactions->source_id = $source_id;
        $point_transactions->save();

        $user_badge = DB::table("point_user_badges")
            ->where("user_id", $user_id)
            ->get();

        $next_upper_badge =0;
        $current_badge_id = 5;


        if($user_badge->count() == 0) {

            $current_badge_id = 5;
            $next_upper_badge = $current_badge_id - 1;

        }else{
            $current_badge_id=0;
            $current_badge_id = $user_badge[0]->badge_id;

            if ($current_badge_id == 1){

                $transaction = ['status' => "success", "source_title_en"=> $title_en,
                    "source_title_bn"=> $title_bn,
                    "source_sub_title_en"=>$sub_title_en,
                    "source_sub_title_bn"=>$sub_title_bn,
                    "message_en" =>$message_en,
                    "message_bn" =>$message_bn,
                    "source_type"=>$type,
                    "action_type" => $action_type , "earned_point_for_the_action"=>$point,
                    "total_point"=>$total_points, "current_batch"=>$current_badge_id ,
                    "is_badge_just_upgraded"=>false, "next_upper_badge"=>0
                ];

                event(new CreatePointTransaction($user_id, $source_id, $transaction));

//                return response()
//                    ->json(['status' => "success", "source_title"=> $title,
//                        "source_sub_title"=>$sub_title,"source_type"=>$type,
//                        "action_type" => $action_type , "earned_point_for_the_action"=>$point,
//                        "total_point"=>$total_points, "current_batch"=>$current_badge_id ,
//                        "is_badge_just_upgraded"=>false, "next_upper_badge"=>0
//                    ]);

                #no upper badge
            }
            else {
                $next_upper_badge = $current_badge_id - 1;
            }
        }


        $badge_criteria=Miscellaneous::chacking_badge_criteria($user_id, $next_upper_badge);
        $point_criteria = Miscellaneous::checking_badge_point($user_id,$next_upper_badge);
        $is_badge_just_upgraded = false;

        if ($badge_criteria  ==1 & $point_criteria ==1){
            $is_badge_just_upgraded = true;
            $current_time = date("Y:m:d h:i:s");
            DB::table('point_user_badges')
                ->updateOrInsert(
                    ['user_id' => $user_id],
                    ['badge_id' => $next_upper_badge, 'created_at'=>$current_time, 'updated_at'=> $current_time]
                );
            $current_badge_id = $next_upper_badge;

            #send push that he just upgade to new label
            #pending for push notifications
        }

        $transaction =  ['status' => "success", "source_title_en"=> $title_en,
            "source_title_bn"=> $title_bn,
            "source_sub_title_en"=>$sub_title_en,
            "source_sub_title_bn"=>$sub_title_bn,
            "message_en" =>$message_en,
            "message_bn" =>$message_bn,
            "source_type"=>$type,
            "action_type" => $action_type , "earned_point_for_the_action"=>$point,
            "total_point"=>$total_points, "current_batch"=>$current_badge_id ,
            "is_badge_just_upgraded"=>$is_badge_just_upgraded
        ];

        event(new CreatePointTransaction($user_id, $source_id, $transaction));

//        return response()
//            ->json(['status' => "success", "source_title"=> $title,
//                "source_sub_title"=>$sub_title,"source_type"=>$type,
//                "action_type" => $action_type , "earned_point_for_the_action"=>$point,
//                "total_point"=>$total_points, "current_batch"=>$current_badge_id ,
//                "is_badge_just_upgraded"=>$is_badge_just_upgraded
//            ]);
    }

    /**
     * Calling api for sending sms to robi user
     *
     * @param $question
     * @param $type
     */
    public static function callApiForSendingSMS($question, $type)
    {
        switch ($type){
            case 'refer':
                $url = "http://43.240.103.21/robi/send/sms/refer";
                break;
            case 'spam':
                $url = "http://43.240.103.21/robi/send/sms/spam";
                break;
            case 'delay':
                $url = "http://43.240.103.21/robi/send/sms/delay";
                break;
            default:
                $url = null;
        }

        if (!empty($url)) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_GET, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['question_id' => $question->id]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_exec($ch);
            curl_close($ch);
        }
    }

    /**
     * Calling api for sending answer to mesenger
     *
     * @param Answer $answer
     * @param $question
     */
    public static function sendAnswerToMessenger(Answer $answer, $question)
    {
        $url = "https://m.maya.com.bd/bot/answer";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['api_token' => config('config.messenger.api_token'), 'body' => $answer->body, 'user_id' => $question->user_id ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close ($ch);
    }

}
