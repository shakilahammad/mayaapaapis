<?php

namespace App\Classes;

use App\Models\AIResponseLog;
use App\Models\DraftAnswer;
use App\Models\FollowUp;
use App\Models\Location;
use App\Models\LockQueue;
use App\Models\Refer;
use App\Models\User;
use App\Models\Answer;
use App\Models\Medium;
use App\Models\Question;
use Carbon\Carbon;
use App\Models\ResponseTime;
use Illuminate\Support\Facades\DB;

/**
 * Class MiscellaneousForApp
 * @package app\Classes
 */
class MiscellaneousForApp
{
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

            list($area, $city, $country, $address) = self::getFormattedLocation($question);

            list($referred_to, $referred_by, $referred_time) = self::getReferrerInfo($question);

            $tags = self::getTags($question);

            $question_time = is_string($question->created_at) ? Carbon::parse($question->created_at)->diffForHumans() : $question->created_at->diffForHumans();

            $isSendSms = self::isSendSms($question);

            $aiResponseLog = AIResponseLog::whereQuestionId($question->id)->first();

            $values = [
                'id' => $question->id,
                'question_body' => $question_body,
                'source' => $question->source,
                'status' => $question->status,
                'user_id' => $question->user_id,
                'specialist_id' => isset($question->specialist_id) ? $question->specialist_id : null,
                'parent_id' => $question->parent_id,
                'city' => $city,
                'area' => $area,
                'country' => $country,
                'location' => $address,
                'featured' => isset($question->featured) ? $question->featured : null,
                'resolved' => isset($question->resolved) ? $question->resolved : null,
                'type' => $question->type,
                'media_id' => $question->media_id,
                'is_premium' => $question->is_premium,
                'tags' => $tags,
                'service_holder' => $question->service_holder,
                'question_created_time' => $question_time,
                'referred_to' => $referred_to,
                'referred_by' => $referred_by,
                'referred_time' => $referred_time,
                'isSendSms' => $isSendSms,
                'is_urgent' => $question->is_urgent,
                'is_complex' => $question->is_complex,
                'suggested_answer' => empty($aiResponseLog->suggested_answer) ? [] : $aiResponseLog->suggested_answer,
            ];
            array_push($data, $values);
        }
        return $data;
    }

    /**
     * Get formatted Answered Question
     *
     * @param $questions
     *
     * @return array
     */
    public static function getFormattedAnsweredQuestions($questions)
    {
        $data = [];
        foreach ($questions as $question) {
            $question_body = self::getQuestionBody($question);
            list($area, $city, $country, $address) = self::getFormattedLocation($question);

            list($referred_to, $referred_by, $referred_time) = self::getReferrerInfo($question);

            list($answer_body, $answer_time, $answered_by, $drafted, $updated) = self::getAnsweredInfo($question);

            $tags = self::getTags($question);

            $user = self::getUserInfo($question->user_id);

            $isSendSms = self::isSendSms($question);

            $media = Medium::whereId($question->media_id)->get();
            $question_time = is_string($question->created_at) ? Carbon::parse($question->created_at)->diffForHumans() : $question->created_at->diffForHumans();

            $aiResponseLog = AIResponseLog::whereQuestionId($question->id)->first();

            $values = [
                'id' => $question->id,
                'question_body' => $question_body,
                'source' => $question->source,
                'status' => $question->status,
                'user_id' => $question->user_id,
                'specialist_id' => isset($question->specialist_id) ? $question->specialist_id : null,
                'parent_id' => $question->parent_id,
                'city' => $city,
                'area' => $area,
                'country' => $country,
                'location' => $address,
                'featured' => isset($question->featured) ? $question->featured : null,
                'resolved' => isset($question->resolved) ? $question->resolved : null,
                'type' => $question->type,
                'media_id' => $question->media_id,
                'media' => !empty($media) ? $media : null,
                'is_premium' => $question->is_premium,
                'tags' => $tags,
                'drafted' => isset($drafted) ? $drafted : 0,
                'updated' => isset($updated) ? $updated : 0,
                'service_holder' => $question->service_holder,
                'question_created_time' => $question_time,
                'referred_to' => $referred_to,
                'referred_by' => $referred_by,
                'referred_time' => $referred_time,
                'answer_body' => isset($answer_body) ? $answer_body : null,
                'answered_by' => $answered_by,
                'answer_time' => isset($answer_time) ? $answer_time : null,
                'isSendSms' => $isSendSms,
                'is_urgent' => $question->is_urgent,
                'is_complex' => $question->is_complex,
                'suggested_answer' => empty($aiResponseLog->suggested_answer) ? [] : $aiResponseLog->suggested_answer,
            ];
            array_push($data, $values);
        }
        return [$data, $user];
    }

    /**
     * Get formateed notification
     *
     * @param $notifications
     * @return array
     */
    public static function getFormattedNotifications($notifications)
    {
        $data = [];
        foreach ($notifications as $notification) {
            $is_premium = Question::find($notification->question_id) == null ? 0 : Question::find($notification->question_id)->is_premium;
            $values = [
                'id' => $notification->id,
                'question_id' => $notification->question_id,
                'title' => $notification->Message->title,
                'details' => $notification->Message->title,
                'type' => $notification->Message->type,
                'seen' => $notification->seen,
                'created_at' => $notification->created_at,
                'updated_at' => $notification->updated_at,
                'is_premium' => $is_premium
            ];

            array_push($data, $values);
        }

        return $data;
    }

    /**
     * Delete question from lock queue
     *
     * @param $question_id
     */
    public static function deleteFromLockQueue($question_id)
    {
        $lockedQueue = LockQueue::whereQuestionId($question_id)->first();
        if ($lockedQueue) {
            $lockedQueue->delete();
        }
    }

    /**
     * @param $question
     * @return int
     */
    public static function isSendSms($question)
    {
        if ($question->is_premium == 1) {
            $delayed = isset($question->sendSmsLogs) ? 1 : 0;
            return $delayed;
        }

        return 0;
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
        $expert = is_string($specialist_id) ? (int)$specialist_id : $specialist_id;
        Db::table('response_time')->where('user_id', $expert)->where('question_id', $question_id)->delete();
    }

    /**
     * Get question body
     *
     * @param $question
     * @return string
     */
    public static function getQuestionBody($question)
    {
        if ($question->source == "app" && mb_detect_encoding($question->body) == "ASCII") {
            $question_body = html_entity_decode(strip_tags($question->body), ENT_IGNORE);
            return $question_body;
        } elseif ($question->source == "app") {
            $question_body = utf8_decode(strip_tags($question->body));
            return $question_body;
        } elseif (mb_detect_encoding($question->body) == "ASCII") {
            $question_body = html_entity_decode(strip_tags($question->body), ENT_IGNORE);
            return $question_body;
        } else {
            $question_body = utf8_decode(strip_tags($question->body));
            return $question_body;
        }
    }

    /**
     * Get formatted location
     *
     * @param $question
     * @return array
     */
    public static function getFormattedLocation($question)
    {
        $area = null;
        $city = null;
        $country = null;
        $address = null;

        $location = Location::find($question->location_id);
        if (!$location->exists()) {
            return [$area, $city, $country, $address];
        }
        $area = $location->area;
        $city = $location->city;
        $country = $location->country;
        $address = $city . ' , ' . $country;

        return [$area, $city, $country, $address];
    }

    /**
     * Get referred info
     *
     * @param $question
     * @return array
     */
    public static function getReferrerInfo($question)
    {
        $referred_to = 0;
        $referred_by = 0;
        $referred_time = 0;
        if ($question->specialist_id > 0) {
            try {
                $referredByAndReferredTime = Refer::whereQuestionId($question->id)->first();
                $specialist = User::find($referredByAndReferredTime->referred_to);
                $referred_to = $specialist->f_name . ' ' . $specialist->l_name;
                $referredBy = User::find($referredByAndReferredTime->referred_by);
                $referred_by = $referredBy->f_name . ' ' . $referredBy->l_name;
                $referred_time = Carbon::parse($referredByAndReferredTime->created_at)->format('d M Y  D g:i A');
            } catch (\Exception $exception) {
                return [$referred_to, $referred_by, $referred_time];
            }
        }

        return [$referred_to, $referred_by, $referred_time];
    }

    /**
     * Get answered info
     *
     * @param $question
     * @return array
     */
    public static function getAnsweredInfo($question)
    {
        $answer_body = null;
        $answer_time = null;
        $answered_by = null;
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
     * Get user info
     *
     * @param $user_id
     * @return array
     */
    public static function getUserInfo($user_id)
    {
        $profile = User::find($user_id);
        if (count($profile)) {
            $profile->registerStatus = $profile->registered == 1 ? 'registered' : 'non-registered';
            $profile->age = !empty($profile->birthday) ? Carbon::parse($profile->birthday)->age : 0;
        } else {
            $profile = ['f_name' => '', 'l_name' => '', 'email' => '', 'type' => '', 'birthday' => '', 'gender' => '', 'phone' => '', 'created_at' => '', 'source' => '', 'registerStatus' => 'non-registered', 'age' => ''];
        }

        $userArray = [];
        $engaged = self::checkEngagedUser($user_id);
        $engaged_user = !empty($engaged) ? $engaged['e_name'] : 0;
        $percentage = !empty($engaged) ? $engaged['engaged'] : 0;
        $profile['e_name'] = $engaged_user;
        $profile['percentage'] = $percentage;
        $profile['total'] = Question::whereUserId($user_id)->get()->count();
        array_push($userArray, $profile);
        return $userArray;
    }


    /**
     * Check engaged
     *
     * @param  $user_id
     * @return response
     */
    public static function checkEngagedUser($user_id)
    {
        if (isset($user_id)) {
            $questions = Question::whereUserId($user_id)->whereStatus('answered')->get();
            $array = [];
            if (count($questions)) {
                for ($i = 0; $i < count($questions); $i++) {
                    $answer = $questions[$i]->Answer;
                    if (count($answer) && $answer->user_id != Null) {
                        $user_id = $answer->user_id;
                        array_push($array, $user_id);
                    }
                }

                if (!empty($array)) {
                    $c = array_count_values($array);
                    $val = array_search(max($c), $c);
                    if (isset($val)) {
                        $user = User::whereId($val)->select('f_name', 'l_name')->get()->first();
                    }
                    $percentage = (max($c) * 100) / count($questions);
                    $value = [
                        'e_name' => $user->f_name . ' ' . $user->l_name,
                        'engaged' => number_format($percentage, 2)
                    ];
                } else {
                    $value = [
                        'e_name' => 0,
                        'engaged' => 0
                    ];
                }
            } else {
                $value = [
                    'e_name' => 0,
                    'engaged' => 0
                ];
            }
            return $value;
        } else {
            return false;
        }
    }

    /**
     * Get tags
     *
     * @param $question
     * @return mixed
     */
    public static function getTags($question)
    {
        $tags = DB::select(DB::raw('select tags.id as id, tags.name_en as name_en, tags.name_bn as name_bn from tags join questions_tags on tags.id = questions_tags.tag_id where questions_tags.question_id = ' . $question->id));
        return $tags;
    }

    public static function createFollowUp($question, $answer)
    {
        FollowUp::create([
            'question_id' => $question->id,
            'specialist_id' => $answer['user_id'],
            'notify_at' => Carbon::now()->addDays($answer['followup_time']),
            'specialist_is_notified' => false
        ]);
    }

}
