<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use Validator;
use Illuminate\Http\JsonResponse;
use File;
use Illuminate\Support\Str;
use App\Models\Catsubcatprodmapping;
class SubcategoryController extends BaseController
{


    public function index(): JsonResponse
    {
        $subcategory = SubCategory::all();
       return $this->sendResponse($subcategory, 'Successfully get all subcategories.');
    }
    
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
        'category_id' => 'required',
        'subcategory'=>'required'
        ]);
        if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
        }
        $category_id=$request->category_id;
        $subcategory=collect($request->subcategory);
        $subcategoryImage=$request->subcategoryImage;
        $subcategorylist=SubCategory::selectRaw('count(*) as subcatcount, name')
        ->whereIn("name",$subcategory)
        ->groupBy('name')
        ->get();
        $listData='';
        foreach($subcategorylist as $subcat)
        {   
            if($subcat->subcatcount >0){
            $listData=$subcat->subcategory." "."Already Exist.";
            }
        }
        if (!($subcategorylist->isEmpty())) {
            return $this->sendError('error', $listData);
        }
        for($i=0;$i<$subcategory->count();$i++)
        {
            $subcategoryObj=new SubCategory();
            $subcategoryObj->name=$subcategory[$i];
            $subcategoryObj->category_id=$category_id;
            if ($request->hasFile('subcategoryImage')) {
                $file = $request->file('subcategoryImage');
                $file_name = 'subcategory_'.$subcategory[$i].'.'.$file->getClientOriginalExtension();
                File::copy($file->getPathname(), public_path('subcategoryImages/'.$file_name));
                $subcategoryObj->subcategoryImage=$file_name;
              }
              $subcategoryObj->timestamps=false;
              $subcategoryObj->created_at= now();
              $subcategoryObj->save();
        }
        
        $success['status'] = true;
        return $this->sendResponse($success, 'SubCategory Created Successfully.');
    }

    public function show(): JsonResponse
    {
        $subcategory = SubCategory::all();
        return $this->sendResponse($subcategory, 'Successfully get all subcategories.');
    }

    public function update(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'subcategory'=>'required'
             ]);
             if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
            $category_id=$request->category_id;
            $subcategory=collect($request->subcategory);
            $subcategoryImage=$request->subcategoryImage;
            $subcategorylist=SubCategory::selectRaw('count(*) as subcatcount, name')
            ->where("name",$subcategory)
            ->where("id",'<>',$id)
            ->groupBy('name')
            ->get();
            $listData='';
            foreach($subcategorylist as $subcat)
            {   
                if($subcat->subcatcount >0){
                $listData=$subcat->subcategory." "."Already Exist.";
                }
            }
            if (!($subcategorylist->isEmpty())) {
                return $this->sendError('error', $listData);
            }
            for($i=0;$i<count($subcategory);$i++){
                $subcategoryObj=SubCategory::where('id',$id)->first();
                $subcategoryObj->name=$subcategory[$i];
                $subcategoryObj->category_id=$category_id;
                if ($request->hasFile('subcategoryImage')) {
                    $file = $request->file('subcategoryImage');
                    $file_name = 'subcategory_'.$subcategory[$i].'.'.$file->getClientOriginalExtension();
                    File::copy($file->getPathname(), public_path('subcategoryImages/'.$file_name));
                    $subcategoryObj->subcategoryImage=$file_name;
                }
            }
            $subcategoryObj->updated_at= now();
            $subcategoryObj->save();
            $success['status'] = true;
            return $this->sendResponse($success, "SubCategory Updated Successfully.");
    } 
    public function destroy($id){
        $subcategory=SubCategory::where("id",$id)->first();
        if($subcategory){
            $subcategory->delete();
        }  
        return $this->sendResponse([],'Subcategory deleted Successfully.');
    }
}

?>