<?php
   
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Product;
use App\Models\User;
use App\Models\ProductList;
use App\Models\SupplierProductsMapping;
use App\Models\Category;
use App\Models\SubCategory;
use Validator;
use File;
use Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Models\Catsubcatprodmapping;  
use App\Mail\MailforProduct;
class ProductController extends BaseController
{
    public function index(): JsonResponse
    {
        $products = Product::all();
        return $this->sendResponse($products, 'Successfully get all products.');
    }
    public function store(Request $request): JsonResponse
    {
        if($request->flag !=''){
            $input = $request->all();
            $validator = Validator::make($input, [
                'description' => 'sometimes',
                'unit_of_measurement' => 'required',
                'whole_price_per_unit' => 'required',
                'max_order_qty'=>'required',
                'min_order_qty' => 'required',
                'special_offer_deals' => 'required',
                'packaging_detail' => 'required',
                'ship_methods' => 'required',
                'discount'=>'sometimes',
                'tax'=>'sometimes',
                'shipping'=>'sometimes',
                'estimated_days' => 'required',
            ]);
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
            $productinformation=Product::where('product_id', $request->product_id)->first();
            if(is_null($productinformation)){
                $productinformation=new Product();
            }
            $unitattr = implode(',',$request->unit_of_measurement);
            $productinformation->user_id = $request->suplierid;
            $productinformation->product_id = $request->product_id;
            $productinformation->description=$request->description;
            $productinformation->unit_of_measurement=$unitattr;
            if ($request->hasFile('productImage')) {
                $file = $request->file('productImage');
                $file_name = 'product_'.$request->productname.'.'.$file->getClientOriginalExtension();
                File::copy($file->getPathname(), public_path('productImages/'.$file_name));
                $productinformation->productImage=$file_name;
            }
            $productinformation->whole_price_per_unit = $request->whole_price_per_unit;
            $productinformation->max_order_qty=$request->max_order_qty;
            $productinformation->min_order_qty = $request->min_order_qty;
            $productinformation->special_offer_deals = $request->special_offer_deals;
            $productinformation->packaging_detail = $request->packaging_detail;
            $productinformation->ship_methods = $request->ship_methods;
            $productinformation->discount = $request->discount;
            $productinformation->shipping = $request->shipping;
            $productinformation->tax = $request->tax;
            $productinformation->estimated_days = $request->estimated_days;
            $productinformation->timestamps=false;
            $productinformation->updated_at= now();
            $productinformation->save();
            $success['status'] = true;
            return $this->sendResponse($success, 'Product information added successfully.');
        }
        else{
            $input = $request->all();
            $validator = Validator::make($input, [
                'subcategory_id' => 'required',
                'productname' => 'required',
                'unitattributes' => 'required'
            ]);
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
            $subcategory_id=$request->subcategory_id;
            $productname=collect($request->productname);
            $unitattr = implode(',',$request->unitattributes);

            $productlist=ProductList::selectRaw('count(*) as prodcount, name')
            ->whereIn("name",$productname)
            ->groupBy('name')
            ->get();
            $listData='';
            foreach($productlist as $prod)
            {   
                if($prod->prodcount>0){
                $listData=$prod->name." "."Already Exist.";
                }
            }
            if (!($productlist->isEmpty())) {
                return $this->sendError('error', $listData);
            }

            $productImage=$request->productImage;
            for($i=0;$i<count($productname);$i++)
            {
                $product= new ProductList();
                $product->name=$productname[$i];
                $product->subcategory_id=$subcategory_id;
                $product->unit_attributes=$unitattr;
                if ($request->hasFile('productImage')) {
                    $file = $request->file('productImage');
                    $file_name = 'product_'.$productname[$i].'.'.$file->getClientOriginalExtension();
                    File::copy($file->getPathname(), public_path('productImages/'.$file_name));
                    $product->productImage=$file_name;
                }
                $product->timestamps=false;
                $product->created_at= now();
                $product->save();
                $catsubcatprodmapping = new Catsubcatprodmapping;
                $catsubcatprodmapping->subcategory_id = $product->subcategory_id;
                $catsubcatprodmapping->product_id = $product->id;
                $catsubcatprodmapping->productDesc = '';
                $catsubcatprodmapping->productImage = $file_name;
                $catsubcatprodmapping->timestamps=false;
                $catsubcatprodmapping->created_at = now();
                $catsubcatprodmapping->save();
            }
            $success['status'] = true;
            return $this->sendResponse($success, 'Product added Successfully.');
       }
    }

    public function show($id): JsonResponse
    {
        $products = ProductList::where('id', $id)->get();
        if (is_null($products)) {
            return $this->sendError('Product not found.');
        }
        return $this->sendResponse($products, 'Product retrieved successfully.');
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id){
        if($request->flag){
            $input = $request->all();
            $validator = Validator::make($input, [
                'subcategory_id' => 'required',
                'product' => 'required',
                'unitattributes' => 'required',
            ]);
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
            $subcategory_id=$request->subcategory_id;
            $productname=collect($request->product);
            $productImage=$request->productImage;
            $unitattr = implode(',',$request->unitattributes);
            $productlist=ProductList::selectRaw('count(*) as prodcount, name')
            ->whereIn("name",$productname)
            ->where("id",'<>',$id)
            ->groupBy('name')
            ->get();
            $listData='';
            foreach($productlist as $prod)
            {   
                if($prod->prodcount>0){
                $listData=$prod->name." "."Already Exist.";
                }
            }
            if (!($productlist->isEmpty())) {
                return $this->sendError('error', $listData);
            }
            for($i=0;$i<count($productname);$i++){
                $productlist=ProductList::where('id', $id)->first();
                $productlist->name=$productname[$i];
                $productlist->subcategory_id=$subcategory_id;
                $productlist->unit_attributes=$unitattr;
                if ($request->hasFile('productImage')) {
                $file = $request->file('productImage');
                $file_name = 'product_'.$productname[$i].'.'.$file->getClientOriginalExtension();
                File::copy($file->getPathname(), public_path('productImages/'.$file_name));
                $productlist->productImage=$file_name;
                }
            }
            $productlist->timestamps=false;
            $productlist->updated_at= now();
            $productlist->save();
            $success['status'] = true;
            return $this->sendResponse($success, "product Updated Successfully.");
        }else{
            $input = $request->all();
            $validator = Validator::make($input, [
                'description' => 'sometimes',
                'unit_of_measurement' => 'required',
                'whole_price_per_unit' => 'required',
                'max_order_qty'=>'required',
                'min_order_qty' => 'required',
                'special_offer_deals' => 'required',
                'packaging_detail' => 'required',
                'ship_methods' => 'required',
                'discount'=>'sometimes',
                'tax'=>'sometimes',
                'shipping'=>'sometimes',
                'estimated_days' => 'required',
            ]);
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
            $productinformation=Product::where('product_id', $id)->first();
            if(is_null($productinformation)){
                $productinformation=new Product();
            }
            $unitattr = implode(',',$request->unit_of_measurement);
            $productinformation->user_id = $request->suplierid;
            $productinformation->product_id = $request->product_id;
            $productinformation->description=$request->description;
            $productinformation->unit_of_measurement=$unitattr;
            if ($request->hasFile('productImage')) {
                $file = $request->file('productImage');
                $file_name = 'product_'.$request->productname.'.'.$file->getClientOriginalExtension();
                File::copy($file->getPathname(), public_path('productImages/'.$file_name));
                $productinformation->productImage=$file_name;
            }else{$productinformation->productImage='';}
            $productinformation->whole_price_per_unit = $request->whole_price_per_unit;
            $productinformation->max_order_qty=$request->max_order_qty;
            $productinformation->min_order_qty = $request->min_order_qty;
            $productinformation->special_offer_deals = $request->special_offer_deals;
            $productinformation->packaging_detail = $request->packaging_detail;
            $productinformation->ship_methods = $request->ship_methods;
            $productinformation->discount = $request->discount;
            $productinformation->shipping = $request->shipping;
            $productinformation->tax = $request->tax;
            $productinformation->estimated_days = $request->estimated_days;
            $productinformation->timestamps=false;
            $productinformation->updated_at= now();
            $productinformation->save();
            $success['status'] = true;
        return $this->sendResponse($success, "product Updated Successfully.");
    }
    }
    public function destroy($id): JsonResponse
    {
        $product=ProductList::where("id",$id)->first();
        $result = SupplierProductsMapping::with('usersdata')
        ->where('product_id',$id)->get();
            $prod_name= $product->name;
        foreach($result as $s){ 
        }
        if($product){
            $prodinformation = Product::where('product_id', $id)->get();
            if($prodinformation){
                foreach($prodinformation as $p){
                    $p->delete();
                }
            }
            $result = SupplierProductsMapping::with('usersdata')
            ->where('product_id',$id)->get();
                $prod_name= $product->name;
            foreach($result as $s){    
                foreach($s->usersdata as $i){
                    $input['email'] = $i->email;
                    $input['id'] = $i->id;
                    $name = $i->name;
                    $user = User::where('role_id', '=', '1')->first();
                    Mail::to($input['email'])->send(new MailforProduct($name,$id,$prod_name, $user->name));
                    $user = User::where('role_id', '=', '1')->get();
                    foreach($user as $u){
                        $emails = $u->email;
                        $name = $u->name;
                        Mail::to($emails)->send(new MailforProduct($name,$id,$prod_name));
                    }  
                }
            }
            $mappedData = SupplierProductsMapping::where('product_id', $id)->get();
            if($mappedData){
                foreach($mappedData as $mapdata){
                    $mapdata->delete();
                }
            }
            $product->delete();
        } 
        return $this->sendResponse([], 'Product deleted successfully.');
    }
}