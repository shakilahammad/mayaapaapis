<?php

namespace App\Http\Controllers\Partners;

use App\Classes\Miscellaneous;
use App\Classes\MiscellaneousForApp;
use App\Classes\SetLocation;
use App\Http\Helper;
use App\Models\Block;
use App\Models\PartnerAuth;
use App\Models\TrackDownload;
use App\Models\Question;
use App\Models\Rating;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\AppSubscribers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MayaPartenersController extends Controller
{
    public function login(Request $request)
    {
        if (isset($request->partner_name) && isset($request->password)) {
            $partner_id = $this->getAuthenticPartner($request->partner_name, $request->password);
            if ($partner_id > 0) {
                $request['partner_id'] = $partner_id;
                $user = null;
                if (isset($request->phone) && !empty($request->phone)) {
                    $user_phone = ltrim($request->phone, '+');
                    $user_phone = ltrim($request->phone, ' ');
                    $user = User::where('phone', Helper::maya_encrypt($user_phone))->first();
                    $is_new = 0;
                    if (!$user) {
                        $user = $this->registerWithPhone($request);
                        $is_new = 1;
                    }
                } else if (isset($request->email) && !empty($request->email)) {
                    $user = User::where('email', Helper::maya_encrypt($request->email))->first();
                    $is_new = 0;
                    if (!$user) {
                        $user = $this->registerWithEmail($request);
                        $is_new = 1;
                    }
                } else if (isset($request->fb_id) && !empty($request->fb_id)) {

                    $email = empty($request->fb_email) ? $request->fb_id . '@facebook.com' : $request->fb_email;
                    $user = User::where('email', Helper::maya_encrypt($request->fb_email))->first();
                    $is_new = 0;
                    if (count($user)) {
                        $user->session = 1;
                        $user->registered = 1;
                        $user->fb_id = $request->fb_id;
                        $user->save();
                        $user->is_new = $is_new;
                        $user->location = '';
                        // return json_encode(['sataus' => 'success', 'user' => $user]) ;
                    } else {
                        $is_new = 1;
                        $download_id = 0;
                        if (isset($request->device_id)) {
                            $track_download_id = TrackDownload::whereDeviceId($request->device_id)->first();
                            if (count($track_download_id)) {
                                $download_id = $track_download_id->id;
                                //$createUser->save();
                            }
                        }
                        $location = SetLocation::formattedLocation($request->ip(), $request->lat, $request->long, $user->id);
                        $userData = [
//                            'f_name'   => empty($request->f_name) ? 'Anonymous' : $request->f_name,
//                            'l_name'   => empty($request->l_name) ? '' : $request->l_name,
                            'email' => $email,
                            'fb_id' => $request->fb_id,
                            'gender' => empty($request->gender) ? '' : $request->gender,
                            'birthday' => empty($request->birthday) ? null : $request->birthday,
                            'source' => empty($request->source) ? '' : $request->source,
                            'location_id' => $location->id,
                            'age' => Carbon::parse($request->birthday)->age,
                            'track_download_id' => $download_id,
                            'partner_id' => $request['partner_id'],
                            'registered' => 1,
                            'session' => 1
                        ];

                        $user = User::create($userData);
                        $user = User::find($user->id);
                        $user->is_new = $is_new;
                        $user->location = '';
                        //return json_encode(['sataus' => 'success', 'user'   => $user]) ;
                    }

                } else {
                    return response()->json([
                        'status' => 'failure',
                        'data' => [
                            'error' => "email or phone is mandatory"
                        ]
                    ]);
                }

                try {
                    if ($user) {
                        $user = $this->getFormattedUserData($user);
                        $userToSave = User::whereId($user['user_id'])->first();
                        $userToSave->session = 1;
                        $userToSave->save();
//                         $user->is_premium = AppSubscribers::whereUsersId($user->id)->exists() ? 1 : 0;
                        $user['is_new'] = $is_new;
                        return response()->json([
                            'status' => 'success',
                            'data' => $user
                        ]);
                    }
                } catch (\Exception $exception) {
                    return response()->json([
                        'status' => 'failure',
                        'data' => [
                            'error' => "internal error 1"
                        ]
                    ]);
                }
                return response()->json([
                    'status' => 'failure',
                    'data' => [
                        'error' => "internal error 2"
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'data' => [
                        'error' => "Authentication error"
                    ]
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'data' => [
                    'error' => "Need partner credentials"
                ]
            ]);
        }
    }

    public function facebookLogin(Request $request)
    {
    }

    //Maya Partners registration by email method
    public function registerWithEmail($post_data)
    {
        if (isset($post_data['lat']) && isset($post_data['long']) && $post_data['long'] != 0 && $post_data['lat'] != 0) {
            $lat = $post_data['lat'];
            $long = $post_data['long'];
        } else {
            $lat = 23.991734;
            $long = 90.419588;
        }

        $location = SetLocation::formattedLocation(0, $lat, $long);

        $user_data['email'] = $post_data['email'];
        $user_data['source'] = !(isset($post_data['source'])) ? 'app' : $post_data['source'];
        $user_data['session'] = 1;
        $user_data['registered'] = 1;
        $user_data['location_id'] = $location->id;
        $user_data['partner_id'] = $post_data['partner_id'];

        try {
            if (isset($post_data['device_id'])) {
                $track_download_id = TrackDownload::whereDeviceId($post_data['device_id'])->first();
                if (count($track_download_id)) {
                    $user_data['track_download_id'] = $track_download_id->id;
                    //$createUser->save();
                }
            }
            $createUser = User::create($user_data);
            $location->user_id = $createUser->id;
            $location->save();
            $createUser->location = '';
            return $createUser;

        } catch (\Exception $exception) {
            return null;
        }
    }

    //Maya Partners registration by phone number method
    public function registerWithPhone($post_data)
    {

        if (isset($post_data['lat']) && isset($post_data['long']) && $post_data['long'] != 0 && $post_data['lat'] != 0) {
            $lat = $post_data['lat'];
            $long = $post_data['long'];
        } else {
            $lat = 23.991734;
            $long = 90.419588;
        }
        $location = SetLocation::formattedLocation(0, $lat, $long);
        $phone_number = ltrim($post_data['phone'], '+');
        $phone_number = ltrim($post_data['phone'], ' ');

        $user_data['phone'] = $phone_number;
        $user_data['email'] = $phone_number . '@phone.com.bd';
        $user_data['source'] = !(isset($post_data['source'])) ? 'app' : $post_data['source'];
        $user_data['session'] = 1;
        $user_data['registered'] = 1;
        $user_data['location_id'] = $location->id;
        $user_data['partner_id'] = $post_data['partner_id'];


        try {
            if (isset($post_data['device_id'])) {
                $track_download_id = TrackDownload::whereDeviceId($post_data['device_id'])->first();
                if (count($track_download_id)) {
                    $user_data['track_download_id'] = $track_download_id->id;
                    //$createUser->save();
                }
            }
            $createUser = User::create($user_data);
            $location->user_id = $createUser->id;
            $location->save();
            $createUser->location = '';
            return $createUser;

        } catch (\Exception $exception) {
            //dd($exception->getFile(),$exception->getLine(),$exception->getMessage());
            return null;
        }
    }

    //getAuthenticated PartnerID
    public function getAuthenticPartner($user_id, $password)
    {
        $partner = PartnerAuth::whereUserId($user_id)->wherePassword($password)->first();
        if (count($partner)) {
            $partner = $partner->id;
            return $partner;
        } else {
            return -1;
        }
    }

    public function getFormattedLocationArray(Request $request)
    {
        if (isset($request['lat']) && isset($request['long'])) {
            $lat = $request['lat'];
            $long = $request['long'];
        } else {
            $lat = 23.991734;
            $long = 90.419588;
        }

        $url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' . trim($lat) . ',' . trim($long) . '&sensor=false';
        $json = @file_get_contents($url);
        $data = json_decode($json);

        if ($data->results) {
            $array = $data->results[0];
            if (isset($array)) {
                $response = array();
                foreach ($array->address_components as $addressComponet) {
                    if (in_array('political', $addressComponet->types)) {
                        $response[$addressComponet->types[0]] = $addressComponet->long_name;
                    }
                }
            }

            if (isset($response['neighborhood'])) {
                $area = $response['neighborhood'];
            } elseif (isset($response['locality'])) {
                $area = $response['locality'];
            } else {
                $area = $response['administrative_area_level_2'];
            }

            $city = isset($response['administrative_area_level_2']) ? $response['administrative_area_level_2'] : $response['administrative_area_level_1'];
            $country = isset($response['country']) ? $response['country'] : 'Bangladesh';
            $location = $array->formatted_address;
            return $array = [
                "lat" => $lat,
                "long" => $long,
                "area" => $area,
                "city" => $city,
                "country" => $country,
                "location" => $location
            ];
        } else {
            return $array = [
                "lat" => 0.0,
                "long" => 0.0,
                "area" => "unknown",
                "city" => "unknown",
                "country" => "unknown",
                "location" => "unknown"
            ];
        }
    }

    public function getFormattedUserData($user)
    {
        $userInfo = [
            "user_id" => $user->id,
            "created_at" => $user->created_at
        ];
        return $userInfo;
    }

    public function postQuestion(Request $request)
    {
        if (isset($request->partner_name) && isset($request->password)) {
            $partner_id = $this->getAuthenticPartner($request->partner_name, $request->password);
            if ($partner_id > 0) {
                $question = [
                    'body' => $request->body,
                    'user_id' => $request->user_id,
                    'source' => $request->source,
                    'is_premium' => 1
                ];
                $validator = Validator::make($question, [
                    'body' => 'required|max:5000|min:5',
                    'user_id' => 'required',
                    'source' => 'required',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'failure',
                        'data' => [
                            $validator->errors()
                        ]
                    ]);
                } else {

                    $lat = 0;
                    $long = 0;


                    $user = User::find($request->user_id);
                    $location = SetLocation::formattedLocation($request->ip(), $lat, $long, $user->id);
                    try {
                        if (AppSubscribers::where('users_id', $request->question['user_id'])->exists()) {
                            $question['is_premium'] = 1;
                        }
                    } catch (\Exception $exception) {
                    }

                    $question['location_id'] = $location->id;
                    $question['body'] = utf8_encode($question['body']);
                    $question['type'] = 'text';
                    if (count($user)) {

                        if ($user->blocked == 1) {
                            $checkBlock = $this->checkBlockedUser($user);
                            // If true it means user is unblocked
                            if (!empty($checkBlock)) {
                                return response()->json(['status' => 'blocked', 'data' => 'you spammed more than five times']);
                            }
                        }
                        try {
                            Question::create($question);
                            return response()->json([
                                'status' => 'success',
                            ]);
                        } catch (\Exception $exception) {
                            return response()->json([
                                'status' => 'failure',
                            ]);
                        }
                    } else {
                        return response()->json([
                            'status' => 'failure',
                            'data' => [
                                'user not found'
                            ]
                        ]);
                    }
                }
            } else {
                return response()->json([
                    'status' => 'failure',
                    'data' => [
                        'error' => "Authentication error"
                    ]
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'data' => [
                    'error' => "Need partner credentials"
                ]
            ]);
        }
    }

    public function fetchQuestions(Request $request, $offset = 0, $limit = 0)
    {
        if (isset($request->partner_name) && isset($request->password)) {
            $partner_id = $this->getAuthenticPartner($request->partner_name, $request->password);
            if ($partner_id > 0) {
                $questions = Question::where('id', $offset > 0 ? '<' : '>', $offset)
                    ->where('status', 'answered')
                    ->where('type', 'text')
                    ->limit($limit)
                    ->orderBy('id', 'desc')
                    ->get();
                return response()->json([
                    'status' => 'success',
                    'data' => $this->getFormattedQuestions($questions)
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'data' => [
                        'error' => "Authentication error"
                    ]
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'data' => [
                    'error' => "Need partner credentials"
                ]
            ]);
        }
    }

    public function fetchMyQuestions(Request $request, $user_id = 0, $offset = 0, $limit = 0)
    {
        if (isset($request->partner_name) && isset($request->password)) {
            $partner_id = $this->getAuthenticPartner($request->partner_name, $request->password);
            if ($partner_id > 0) {
                $user = User::find($user_id);
                if (count($user)) {
                    $questions = Question::where('id', $offset > 0 ? '<' : '>', $offset)
                        ->where('type', 'text')
                        ->where('user_id', $user_id)
                        ->limit($limit)
                        ->orderBy('id', 'desc')
                        ->get();
                    return response()->json([
                        'status' => 'success',
                        'data' => $this->getFormattedQuestions($questions)

                    ]);
                } else {
                    return response()->json([
                        'status' => 'failure',
                        'data' => [
                            'error' => "Invalid user_id"
                        ]
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failure',
                    'data' => [
                        'error' => "Authentication error"
                    ]
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'data' => [
                    'error' => "Need partner credentials"
                ]
            ]);
        }
    }

    public function checkBlockedUser($user)
    {
        try {
            $blockUser = Block::whereUserId($user->id)->first();
            if (count($blockUser)) {
                if ($blockUser->is_permanent == 0) {
                    $timeDifference = $blockUser->created_at->addHours(48)->diffForHumans();
                    return "You spammed more than five times.You are temporarily blocked from maya apa services.You can ask question again after " . $timeDifference;
                } else {
                    return 'You are permanently blocked from maya apa services!';
                }
            }
            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function getFormattedQuestions($questions)
    {
        $data = [];
        foreach ($questions as $question) {
            list($area, $city, $country, $address) = MiscellaneousForApp::getFormattedLocation($question);
            $values = [
                'question_id' => $question->id,
                'body' => Miscellaneous::getQuestionBody($question),
                'location' => $city == null ? " " : $city . $country == null ? " " : $country,
                'user_id' => $question->user_id,
                'likes' => count($question->Likes),
                'created_at' => $question->created_at->toDateTimeString()
            ];
            array_push($data, $values);
        }

        return $data;
    }

    public function getQuestionDetails(Request $request, $question_id = 0)
    {
        if (isset($request->partner_name) && isset($request->password)) {
            $partner_id = $this->getAuthenticPartner($request->partner_name, $request->password);
            if ($partner_id > 0) {
                $question = Question::where('id', $question_id)->with('Answer')->get();
                if (count($question)) {
                    $user_id = User::whereEmail($question[0]->email)->first();
                    $user_id = count($user_id) ? $user_id->id : 0;
                    $answer = $question[0]->Answer;
                    if ($answer != null) {
                        $answer_body = $question[0]->Answer->body;
                        $anwser_id = $question[0]->Answer->id;
                        $answer_updated_at = $question[0]->Answer->updated_at->toDateTimeString();
                        $result = $this->getFormattedQuestions($question);
                        return response()->json([
                            'status' => 'success',
                            'question' => $result[0],
                            'answer' => [
                                'body' => $this->getAnswerBody($answer_body),
                                'answer_time' => $answer_updated_at,
                                'rate' => $this->getRate($anwser_id, $user_id)
                            ]
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'success',
                            'question' => $this->getFormattedQuestions($question)[0],
                            'answer' => [
                                'body' => 'Not Answered Yet!',
                                'answer_time' => null,
                                'rate' => '0'
                            ]
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 'failure',
                        'data' => [
                            'error' => "Invalid Question Id"
                        ]
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failure',
                    'data' => [
                        'error' => "Authentication error"
                    ]
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'data' => [
                    'error' => "Need partner credentials"
                ]
            ]);
        }
    }

    public function getRate($answer_id, $user_id)
    {
        $rate = Rating::whereAnswerId($answer_id)->whereUserId($user_id)->first();
        if (count($rate)) {
            return $rate->rating;
        } else return 0;
    }

    public function getAnswerBody($body)
    {
        return strip_tags($body);
    }

    public function rateAnswer(Request $request, $question_id = 0, $user_id = 0)
    {
        if (isset($request->partner_name) && isset($request->password)) {
            $partner_id = $this->getAuthenticPartner($request->partner_name, $request->password);
            if ($partner_id > 0) {
                $question = Question::where('id', $question_id)->with('Answer')->first();
                if (count($question)) {
                    $user = User::find($question->user_id);
                    if (count($user)) {
                        if ($user->id == $user_id) {
                            $rating = new Rating();
                            $rating->user_id = $user_id;
                            $rating->question_id = $question_id;
                            $rating->rating = $request->rate;
                            $rating->save();
//                             $rating->updateOrCreate(
//                                 ['user_id'=>$user_id],
//                                 ['answer_id'=>$question->Answer->id],
//                                 ['question_id'=>$question_id],
//                                 ['rating'=>$request->rate]
//                             );
                            return response()->json([
                                'status' => 'success',
                            ]);
                        }
                    }
                    return response()->json([
                        'status' => 'failure',
                        'data' => [
                            'error' => "Invalid User"
                        ]
                    ]);
                } else {
                    return response()->json([
                        'status' => 'failure',
                        'data' => [
                            'error' => "Invalid Question Id"
                        ]
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'failure',
                    'data' => [
                        'error' => "Authentication error"
                    ]
                ]);
            }

        } else {
            return response()->json([
                'status' => 'failure',
                'data' => [
                    'error' => "Need partner credentials"
                ]
            ]);
        }
    }

    public function fetchArticle(Request $request, $language = 'en', $offset = 0, $limit = 0)
    {
        if (isset($request->partner_name) && isset($request->password)) {
            $partner_id = $this->getAuthenticPartner($request->partner_name, $request->password);
            if ($language == 'en') {
                $lang = 'REGEXP';
            } else if ($language == 'bn') {
                $lang = 'NOT REGEXP';
            } else {
                return response()->json([
                    'status' => 'failure',
                    'data' => [
                        'error' => "Language not supported"
                    ]
                ]);
            }
            if ($partner_id > 0) {
                $article = DB::table('wp_posts')
                    ->where('post_status', 'publish')
                    ->where('post_parent', 0)
                    ->where('post_title', $lang, '[a-z]')
                    ->whereRaw('LENGTH(post_content) > ?', [100])
                    ->skip($offset)
                    ->take($limit)
                    ->inRandomOrder()
                    //->orderBy('ID', 'desc')
                    ->get(['ID', 'post_title', 'post_content', 'post_modified_gmt']);

                return response()->json([
                    'status' => 'success',
                    'data' => $this->getFormattedArticle($article)
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'data' => [
                        'error' => "Authentication error"
                    ]
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'data' => [
                    'error' => "Need partner credentials"
                ]
            ]);
        }
    }

    public function getFormattedArticle($articles)
    {
        $data = [];
        foreach ($articles as $article) {
            $image = DB::table('wp_posts')
                ->where('post_parent', $article->ID)
                ->where('post_type', 'attachment')
                ->first(['guid']);
            $image_source = '';
            if (count($image) > 0) {
                $image_source = $image->guid;
            }
            $values = [
                'id'=>$article->ID,
                'post_title' => $article->post_title,
                'image_source' => $image_source,
                'post_content' => $article->post_content,
                'created_at' => $article->post_modified_gmt,
            ];
            array_push($data, $values);
        }
        return $data;
    }
}
