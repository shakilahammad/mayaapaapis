<?php
/**
 * Created by PhpStorm.
 * User: itsfaruque
 * Date: 4/16/19
 * Time: 11:44 AM
 */

namespace App\Http\Controllers\Quiz;


use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\User;

class QuizController extends Controller
{
    public function __construct()
    {

    }

    public function createQuiz(){
//        $last_entry = Quiz::orderby('id', 'desc')->take(1)->get(['group_id']);
//        dd($last_entry[0]->group_id);
        $answer[] = [
            'ans' => 'হ্যাঁ',
            'is_right' => 0, // Wrong Answer - 0, right Answer - 1
            'explanation' => 'ক্যান্সার ছোঁয়াচে রোগ নয়।  তবে কিছু ভাইরাস যেমন হিউম্যান প্যাপিলোমা ভাইরাস থেকে ক্যান্সার হতে পারে। এক্ষেত্রে ভাইরাসগুলো ছোঁয়াচে কিন্তু এর দ্বারা যে ক্যান্সার হতে পারে তা ছোঁয়াচে নয়।'
        ];
        $answer[] = [
            'ans' => 'না',
            'is_right' => 1, // Wrong Answer - 0, right Answer - 1
            'explanation' => 'ক্যান্সার ছোঁয়াচে রোগ নয়।  তবে কিছু ভাইরাস যেমন হিউম্যান প্যাপিলোমা ভাইরাস থেকে ক্যান্সার হতে পারে। এক্ষেত্রে ভাইরাসগুলো ছোঁয়াচে কিন্তু এর দ্বারা যে ক্যান্সার হতে পারে তা ছোঁয়াচে নয়।'
        ];

        $data = array(
            'question' => 'ক্যান্সার কি একটি ছোঁয়াচে রোগ',
            'answer' => json_encode($answer),
            'tag_id' => 38,
            'group_id' => 2
        );
        $quiz = Quiz::create($data);
        dd($quiz->id);
        // update Quiz
        $quiz = Quiz::find(5);
//        return json_decode($quiz->answer);
        $ans = json_decode($quiz->answer);
//        dd($ans);
        $ans[0]->ans = $ans[0]->ans1;
        unset($ans[0]->ans1);
        $ans[1]->ans = $ans[1]->ans2;
        unset($ans[1]->ans2);
//        dd($ans);
//        $ans[1]->is_right = 1;
        $quiz->answer = json_encode($ans);
//        $quiz->save();
//        return $quiz;
        dd($quiz);
//        dd(json_decode($data[0]['answer']));
    }
}