<?php
   
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Validator;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Models\Catsubcatprodmapping;
use File;
class CategoryController extends BaseController
{
    public function index(): JsonResponse
    {
        $category = Category::all();
       return $this->sendResponse($category, 'Successfully get all categories.');
    }

    public function show(): JsonResponse
    {
        $category = Category::all();
       return $this->sendResponse($category, 'Successfully get all categories.');
    }
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
                 'category' => 'required',
             ]);
        if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
        }
        $category = $request->category;
        $newcate=collect($category);
        $categorylist=Category::selectRaw('count(*) as catcount, name')
        ->whereIn("name",$newcate)
        ->groupBy('name')
        ->get();
        $listData='';
        foreach($categorylist as $cat)
        {   
            if($cat->catcount >0){
                $listData=$cat->category.""."Already Exist.";
            }
        }
        if (!($categorylist->isEmpty())) {
            return $this->sendError('error', $listData);      
        }
        for($i=0;$i<count($newcate);$i++)
        {
            $categoryObj=new Category();
            $categoryObj->name=$newcate[$i];
            if($request->hasfile('categoryImage'))
            {
                $imageName = 'category_'.$newcate[0].'.'.$request->file('categoryImage')->extension();  
                $request->file('categoryImage')->move(public_path('categoryImages'), $imageName);
                $categoryObj->categoryImage=$imageName;
            }
            $categoryObj->timestamps=false;
            $categoryObj->created_at= now();
            $categoryObj->save();
            $catsubcatprodmapping = new Catsubcatprodmapping;
            $catsubcatprodmapping->category_id = $categoryObj->id;
            $catsubcatprodmapping->timestamps=false;
            $catsubcatprodmapping->created_at = now();
            $catsubcatprodmapping->save();
        }
        $success['status'] = true;
        return $this->sendResponse($success, "Category Created Successfully.");
    } 
    public function update(Request $request,$id){
        $validator = Validator::make($request->all(), [
                 'category' => 'required',
             ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $newcate=collect($request->category);
        $categorylist=Category::selectRaw('count(*) as catcount, name')
        ->where("name",$newcate)
        ->where('id',"<>",$id)
        ->groupBy('name')
        ->get();
        $listData='';
        foreach($categorylist as $cat)
        {   
            if($cat->catcount >0){
            $listData=$cat->category.""."Already Exist.";
            }
        }
        if (!($categorylist->isEmpty())) {
            return $this->sendError('error', $listData); 
        }

        for($i=0;$i<count($newcate);$i++){
            $categoryObj=Category::where('id',$id)->first();
            $categoryObj->name=$newcate[$i];
    
           if($request->hasfile('categoryImage'))
            {
                $destination = 'categoryImages/'.$categoryObj->categoryImage;
                if(File::exists($destination))
                {
                    File::delete($destination);
                }
                $file = $request->file('categoryImage');
                $filename = 'category_'.$newcate[$i].'_'.$file->getClientOriginalName();
                $file->move('categoryImages/', $filename);
                $categoryObj->categoryImage=$filename;
            }
        }
        $categoryObj->save();
        $success['status'] = true;
        return $this->sendResponse($success, "Category Updated Successfully.");
     } 
    public function destroy($id){
        $subcategory=SubCategory::where("category_id",$id)->first();
        if($subcategory){
            $subcategory->delete();
        }
        $category = Category::where("id",$id)->first();
        $category->delete();
        return $this->sendResponse([], 'Category & its subCategory deleted Successfully.');  
    }  
    
}