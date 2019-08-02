<?php


namespace App\Http\Controllers\APIs\V4;


use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{

    public function getPrescription(Request $request, $question_id){
        $prescription = Prescription::with(['specialist.specialistProfile', 'user:id,gender'])->where('question_id', $question_id)->orderBy('created_at','desc') ->first();
//        dd($prescription->specialist->specialistProfile);

        if($prescription !== null){

            $data = [
              'status' => 'success',
              'prescription' => $prescription,
            ];

            return response()->json($data);

        }

        $data = [
            'status' => 'failure',
            'prescription' => null,
        ];

        return response()->json($data);

    }

}