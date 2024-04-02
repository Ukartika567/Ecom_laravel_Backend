<?php
   
namespace App\Http\Controllers\API;
   
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Product;
use App\Models\ProductList;
use App\Models\UserProfile;
use App\Models\Catsubcatprodmapping;
use App\Models\Category;
use App\Models\SubCategory;
use Validator;
use DB;
use App\Http\Resources\ProductResource;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\SupplierProductsMapping;
/**
 *
 * @OA\get(
 *     path="/api/subt",
 *     @OA\Response(response="200", description="An example endpoint")
 * )
 */   
class CatSubcategoryController extends BaseController
{
    public function index(): JsonResponse
    {
        $products = Category::all();
       return $this->sendResponse($products, 'Successfully.');
    }
    public function catsubdetails(Request $request,$category_id): JsonResponse
    {       
        $subcategory = SubCategory::where("category_id",$category_id)->get();
       return $this->sendResponse($subcategory, 'Successfully get subcategory where category_id is '.$category_id);
    }
    
    public function productdetails(Request $request,$subcategory_id): JsonResponse
    {     
        $subcategory = ProductList::where("subcategory_id",$subcategory_id)->get();
       return $this->sendResponse($subcategory, 'Successfully get subcategory where category_id is '.$subcategory_id);
    }
    public function allCatlist(Request $request)
    {   
        $search = Category::with('subcateDetails.productDetails');
        $search=$search->get();
        return $this->sendResponse($search, 'Successfully Search Category & its Subcategory.');
    }

    public function allCatlistProduct(Request $request)
    {   
        $search = Category::with('subcateDetails.productDetails');
        $search=$search->get();
        return $this->sendResponse($search, 'Successfully Search Category & its Subcategory.');
    }

    public function allProduct(Request $request)
    {   
        $search = DB::table('productlist')->get();
        return $this->sendResponse($search, 'Successfully Search Products');
    }
    public function singleCatlist($id)
    {
        $category = Category::with("subcatedetails.productDetails")
        ->where("id",$id)->get();
        return $this->sendResponse($category, 'Successfully Get the category and its subcategory.');
    }

    public function allCatSubProdlist($user_id){
        $catsubprod = UserProfile::where("user_id",$user_id)->get(['category','subcategory','product_id']);
        return $this->sendResponse($catsubprod, 'Successfully Get the category, subcategory and its Products.');
    }

    public function searchSubcat(request $request)
    {  
        DB::enableQueryLog();
        $search = '';
        $returnData=[];
        $finalData=[];
        $idArray=[];
        $resultData = array();
        $result = Category::withWhereHas('subcategory.productDetails', function (Builder $query)  use ($search)  {
            $query->where('id', 'like', "%{$search}%");
        });
        if($request->category!="" && $request->subcategory == ""){
            $r_ = $result->get();
            foreach($r_ as $r){
                array_push($idArray, $r->id);
            }
            if(in_array($request->category,$idArray)){
              $result = $result->where('id', 'like', '%' . $request->category . '%');
              $result=$result->get();
              $query = DB::getQueryLog();
              $query = end($query);
              return $this->sendResponse($result,  'Successfully Fetch...');
            }else{
                $result = Category::where('id',$request->category);
                $result=$result->get();
                foreach( $result as $r){
                    $resultData['id'] = $r->id;
                    $resultData['name'] = $r->name;
                    $resultData['categoryImage'] = $r->categoryImage;
                    $resultData['created_at'] = $r->created_at;
                    $resultData['updated_at'] = $r->updated_at;
                    $resultData['subcategory'] = [];
                }
                array_push($finalData,  $resultData);
                $query = DB::getQueryLog();
                $query = end($query);
                return $this->sendResponse($finalData,  'Successfully Fetch...');
            }
        }
        else if ($request->category!="" && $request->subcategory != "") {
            $result=$result->get();
            foreach($result as $r){
                foreach($r->subcategory as $subcat){
                    if($subcat->id == $request->subcategory){
                        $resultData['id'] = $r->id;
                        $resultData['name'] = $r->name;
                        $resultData['categoryImage'] = $r->categoryImage;
                        $resultData['created_at'] = $r->created_at;
                        $resultData['updated_at'] = $r->updated_at;
                        $resultData['subcategory'] = [$subcat];
                    }
                }
            }
            array_push($finalData,  $resultData);
            $query = DB::getQueryLog();
            $query = end($query);
            return $this->sendResponse($finalData,  'Successfully Fetch subcat...'); 
        }
    } 
    public function searchCatSubProd($name)
    {
        $result = Category::with('subcatedetails.productDetails')
        ->where('name', 'LIKE', '%'. $name. '%')->get();
        if(count($result)){
         return Response()->json($result);
        }
        else
        {
        return response()->json(['Result' => 'No Data not found'], 404);
      }
    }

    public function getproduct($id): JsonResponse
    {
        $products = ProductList::where('id', $id)->get();
        if (is_null($products)) {
            return $this->sendError('Product not found.');
        }
        return $this->sendResponse($products, 'Product retrieved successfully.');
    }

    public function suppcatlist(Request $request){
        $supplier_id = $request->supplier_id;
        $category_id = $request->category_id;
        $subcategory_id = $request->subcategory_id;
        $product_id = $request->product_id;
        if($request->flag){
            $finaldata = [];
            $result = SupplierProductsMapping::
            where([['supplier_id',$supplier_id],['product_id',$product_id] ]);
            $result=$result->get();
            array_push($finaldata, $result);
            foreach($result as $r){
                $id=$r->supplier_id;
                $r['product_details'] = ProductList::where('id', $product_id)->get();
                $r['product_info'] = Product::where([['product_id','=',$product_id],['user_id','=',$id]])->get();
            }
            return $this->sendResponse($finaldata, 'Successfully Get the category,
            subcategory and its Products.');
        }
        else{
            $suppprod = 
            SupplierProductsMapping::with('productDetails.subcategoryname.categoryname')
            ->where('supplier_id',$supplier_id);
            if($category_id  != ''){
                $suppprod = $suppprod->where('category', $category_id);
            }
            if($subcategory_id  != ''){
                $suppprod = $suppprod->where('subcategory', $subcategory_id);
            }
            if( $product_id != ''){
                $suppprod = $suppprod->where('product_id', $product_id);
            }
            $result = $suppprod->get();
            return $this->sendResponse($result, 'Successfully Get the category,
            subcategory and its Products.');
        }
    }
}
?>