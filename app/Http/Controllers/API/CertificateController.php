<?php

namespace App\Http\Controllers\API;

use App\Models\Certificate;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\API\BaseController as BaseController;
use Validator;
use Illuminate\Http\JsonResponse;
use File;
class CertificateController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $certificate = Certificate::all();
       return $this->sendResponse($certificate, 'Successfully get all certificates.'); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   
        public function store(Request $request): JsonResponse
        {
            $validator = Validator::make($request->all(),[
                'suplier_id' => 'required|numeric',
                'certification' => 'required',
                'certificate_image[]' => 'sometimes|image|mimes:jpg,png,jpeg,gif,svg'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error.', $validator->errors());
            } 
           
    if(!$request->hasFile('certificate_image')) {
        return response()->json(['upload_file_not_found'], 400);
    }
 
    $allowedfileExtension=['pdf','jpg','png','jpeg'];
    $files = $request->file('certificate_image'); 
    $errors = [];
 
    foreach ($files as $file) {      
 
        $extension = $file->getClientOriginalExtension();
 
        $check = in_array($extension,$allowedfileExtension);
 
        if($check) {
            foreach($request->certificate_image as $mediaFiles) {
 
                $path = $mediaFiles->store('certificate');
                $name = $mediaFiles->getClientOriginalName();
                $save = new Certificate();
                $save->suplier_id = $request->suplier_id;
                $save->certification = $request->certification;
                $save->certificate_image = $path;
                $save->save();
            }
        } else {
            return response()->json(['invalid_file_format'], 422);
        }
 
        return response()->json(['file_uploaded'], 200);
 
    }
}
 
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),[
            'suplier_id' => 'required|numeric',
            'certification' => 'required',
            'certificate_image' => 'required|image',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $certify=Certificate::where('id',$id)->first();
        $certify->certification=$request->certification;

       if($request->hasfile('certificate_image'))
        {
            $destination = 'certificate_image/'.$certify->categoryImage;
            if(File::exists($destination))
            {
                File::delete($destination);
            }
            $file = $request->file('certificate_image');
            $filename = 'certificate'.time().'_'.$file->getClientOriginalName();
            $file->move('certificate/', $filename);
        }
        $certify->certificate_image=$filename;
        $certify->save();
        

   
        $success['status'] = true;
        return $this->sendResponse($success, "Category Updated Successfully.");
     } 


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Certificate  $certificate
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $certify=Certificate::where("id",$id)->first();
        if($certify){
            $certify->delete();
        }
      
        return $this->sendResponse([], 'Certificate deleted Successfully.');  
    }
}
