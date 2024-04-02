<?php

namespace App\Http\Controllers\API;

use App\Models\Questionnaire;
use Illuminate\Http\Request;
use App\Models\RequestResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use File;
class QuestionnaireController extends BaseController
{
   
    public function index()
    {
        $questionnaire = Questionnaire::all();
       return $this->sendResponse($questionnaire, 'Successfully get all Questionnaires.');
    }
   
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'resp_ref_id' => 'sometimes',
            'parent_ques_id'=> 'sometimes',
            'ques_ans_array' => 'required',
            'questionnaireType'=> 'sometimes',
            'quote_id' => 'sometimes',
            'supplier_id' => 'sometimes',
            'customer_id' => 'sometimes'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $ques_ans_array=collect($request->ques_ans_array);
        for($i=0;$i<count($ques_ans_array);$i++)
        {
            $resultdata = json_decode($ques_ans_array[$i]);
            $questionnaire = new Questionnaire();
            if(gettype($resultdata->a) === 'array'){
                $a = implode(', ',$resultdata->a);
                $questionnaire->ans = $a;
            }else{
                $questionnaire->ans = $resultdata->a;
            }
            $questionnaire->parent_ques_id = $request->parent_ques_id;
            $questionnaire->question = $resultdata->q;
            $questionnaire->questionnaireType = $request->questionnaireType;
            $questionnaire->customer_id = $request->customer_id;
            $questionnaire->save();
        }
        if($request->resp_ref_id != ''){
            $requestresp = RequestResponse::where('resp_ref_id','=',$request->resp_ref_id)->first();
            $requestresp->status='Cancelled';
            $requestresp->save();
        }
        return $this->sendResponse( $questionnaire, 'Questionnaires Submitted Succesfully');
    }
    public function update(Request $request,$id){
        $user_id = $request->customer_id;
        $ques_ans_array=collect($request->ques_ans_array);
        for($i=0;$i<count($ques_ans_array);$i++)
        {
            $questionaire =  Questionnaire::where('parent_ques_id',$id)
            ->where('customer_id', $user_id)
            ->get();
                $resultdata = json_decode($ques_ans_array[$i]);
                foreach($questionaire as $q){
                    if($q->question == $resultdata->q){
                        if(gettype($resultdata->a) === 'array'){
                            $a = implode(',',$resultdata->a);
                            $q->ans = $a;
                        }else{
                            $q->ans = $resultdata->a;
                        }
                    }
                    $q->save();
                }
            }
        $success['status'] = true;
        return $this->sendResponse($success, "Questionaire Updated Successfully.");
    } 
    public function show($user_id): JsonResponse
    {
        $questions = Questionnaire::where('customer_id', $user_id)->get();
        return $this->sendResponse($questions, 'Successfully get all the questions.');
    }
   
    public function destroy($questionid)
    {
        $questionnaire=Questionnaire::where("id",$questionid)->first();
        if($questionnaire){
            $questionnaire->delete();
        }  
        return $this->sendResponse([],'questionnaire deleted Successfully.');
    }
}
