<?php
/**
 * Created by PhpStorm.
 * User: razib
 * Date: 2019-02-26
 * Time: 12:11
 */

namespace App\Http\Controllers;


use App\Models\Question;

class PrescriptionController extends Controller
{
    public function getPrescription($question_id)
    {
//        dd($question_id);

        $result = Question::find($question_id)->prescription()->first();

//        dd($result);
        return view('prescription')->with('data', $result);
    }
}