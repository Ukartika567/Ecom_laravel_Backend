<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\RequestResponse;
use DB;
use App\Http\Controllers\API\BaseController as BaseController;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\RequestQuote;
use App\Models\Negotiation;
use App\Models\CreditPoints;
use App\Models\AdminCreditPoints;
use App\Models\CreditMapping;
use App\Models\CustOrder;
use Illuminate\Support\Str;
use File;
use Mail;
use App\Mail\CustomerMail;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Mail\TestUserMail;
use App\Mail\NegotiateMail;
use App\Mail\OrderAccept;
use App\Mail\OrderAcceptShipping;
use App\Mail\OrderDelivered;
use App\Models\ShippingAddress;
use App\Models\Feedback;
use App\Models\ProductList;
use App\Models\RequestedQuotes;
use App\Models\UserProfile;
use App\Models\InvoiceDetails;
class RequestResponseController extends BaseController
{
   

public function storeRequestResponse(Request $request): JsonResponse{
    $validator = Validator::make($request->all(), [
        'request_quote_id' => 'required',
        'suplierid' => 'required',
        'customer_id' => 'required',
        'category_id' => 'required',
        'subcategory_id' => 'required',
        'product_id' => 'required',
        'description' => 'required',
        'requiredtime' => 'required',
        'productImage' => 'sometimes|image',
        'unit_of_measurement' => 'required',
        'whole_price_per_unit' => 'required',
        'min_order_qty' => 'required',
        'special_offer_deals' => 'sometimes',
        'packaging_detail' => 'required',
        'qty_per_packet' => 'sometimes',
        'ship_methods' => 'required',
        'estimated_delivery_time' => 'required',
        'tax' => 'sometimes',
        'discount' => 'sometimes',
        'shipping' => 'sometimes',
    ]);
    $image_path = '';
    
    if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
    }

    $requestresponse = new RequestResponse;
    $tickets='WER-';
    $requestresponse->resp_ref_id = $tickets.random_int(1000000, 9999999);
    $requestresponse->request_quote_id = $request->request_quote_id;
    $requestresponse->suplierid = $request->suplierid;
    $requestresponse->customer_id = $request->customer_id;
    $requestresponse->category_id = $request->category_id;
    $requestresponse->subcategory_id = $request->subcategory_id;
    $requestresponse->product_id = $request->product_id;
    $requestresponse->description = $request->description;
    $requestresponse->requiredtime = $request->requiredtime;
    if($request->productImage){
        $image_path = $request->file('productImage')->store('productImages');
    }
    $unitattrs = implode(',',$request->unit_of_measurement);
    $requestresponse->productImage = $image_path;
    $requestresponse->unit_of_measurement = $unitattrs;
    $requestresponse->whole_price_per_unit = $request->whole_price_per_unit;
    $requestresponse->min_order_qty = $request->min_order_qty;
    $requestresponse->special_offer_deals = $request->special_offer_deals;
    $requestresponse->packaging_detail = $request->packaging_detail;
    $requestresponse->qty_per_packet = $request->qty_per_packet;
    $requestresponse->ship_methods = $request->ship_methods;
    $requestresponse->estimated_delivery_time = $request->estimated_delivery_time;
    $requestresponse->tax = $request->tax;
    $requestresponse->discount = $request->discount;
    $requestresponse->shipping = $request->shipping;
    $requestresponse->status = 'InProgress';
    $requestresponse->save();
    $supplierid = $request->suplierid;
    $product = $request->product_id;
    $search = User::where("id", $request->customer_id)->get();
    $suppliers = User::where("id", $supplierid)->get();
    foreach($suppliers as $sup){
        $supplier = $sup->ref_id;
        $productname = ProductList::where('id', '=', $product)->get('name');
        foreach($productname as $p){
            $product = $p->name;
            foreach($search as $s){
                $input['customermail'] = $s->email;
                $name = $s->name;
                $customer = $request->customerid;
                $quantity = $request->min_order_qty;
                $created = $request->requiredtime;
                $responseid =  $requestresponse->resp_ref_id;
            }
        }
    }
    Mail::to($input['customermail'])->send(new CustomerMail($responseid,$name,$supplier,$product,$quantity,$created));
    $user = User::where('role_id', '=', '1')->get();
    foreach($user as $u){
        $emails = $u->email;
        $name = $u->name;
        Mail::to($emails)->send(new CustomerMail($responseid,$name,$supplier,$product,$quantity,$created));
    }
    return response()->json('Successfully send message to customer...');
}

        public function updateResponse(Request $request, $id): JsonResponse
        {
            $requestresp=RequestResponse::where('id',$id)->first();
            if($request->flag == 'cancel'){
                $requestresp->status = 'Cancelled';  
            }else{
                $requestresp->status = 'InProgress';  
            }
            $requestresp->save();
            return $this->sendResponse($requestresp,"Successfully updated the Request Response....");
        }

        public function searchRequestResponse(request $request)
        {  
            DB::enableQueryLog();
            $search_by_category=$request->categoryname;
            $returnData = [];
            $user_id = $request->user_id;
            $search_user = User::where('id', $user_id)->first();
            if($request->search_by_quote_id != '' && $request->flag === 'nego'){
                $result = RequestResponse::with('negotiations')
                ->where("request_quote_id", $request->search_by_quote_id);
                if($search_user->role_id==3){
                    if($request->cancelorder !=''){
                        $result=$result->where('status', '=', 'Cancelled')->get();
                    }
                    else{
                        $result=$result->where("status", 'InProgress')->limit(3)->get();
                    }
                }else if($search_user->role_id==2){
                    $result=$result->where("suplierid", $user_id)->get();
                }
                else{
                    if($request->cancelorder !=''){
                        $result=$result->where('status', '=', 'Cancelled')->get();
                    }
                    else{
                        $result=$result->get();
                    }
                }
            }

            if(($request->search_by_quote_id == '') && ($search_user->role_id==1 || $search_user->role_id==3)){
                if($request->flag == 'noapproveddata' && $search_user->role_id==3){
                    $result = DB::table('request_response')
                    ->select(
                        'request_response.*','productlist.name as productname', 
                        'subcategories.name as subcategory','categories.name as category'
                        )
                    ->join('productlist', 'request_response.product_id', '=', 'productlist.id')
                    ->join('subcategories', 'request_response.subcategory_id', '=', 'subcategories.id')
                    ->join('categories','request_response.category_id','=','categories.id');

                    if($request->flag == 'noapproveddata'){
                        $result=$result->where('status', '!=', 'Approved')->where('status', '!=', 'Closed')->get();
                    }else{
                        $result=$result->get();
                    }
                }
                else{
                    $result = DB::table('request_response')
                    ->select(
                        'request_response.*','productlist.name as productname', 'subcategories.name as subcategory','categories.name as category'
                        )
                    ->join(DB::raw('(SELECT request_quote_id, MAX(id) AS max_id FROM request_response GROUP BY request_quote_id) as sub'), function($join)
                    {
                        $join->on('request_response.request_quote_id', '=', 'sub.request_quote_id');
                        $join->on('request_response.id', '=', 'sub.max_id');
                    })
                    ->join('productlist', 'request_response.product_id', '=', 'productlist.id')
                    ->join('subcategories', 'request_response.subcategory_id', '=', 'subcategories.id')
                    ->join('categories','request_response.category_id','=','categories.id');

                    if($search_user->role_id==1){
                        if($request->flag == 'noapproveddata'){
                            $result=$result->where('status', '!=', 'Approved')->get();
                        }else{
                            $result=$result->get();
                        }
                    }
                    else{
                        $result=$result->get();
                    }
                }
            }

            if($request->search_by_quote_id =="" && $search_user->role_id==2){
                $result = DB::table('request_response')
                ->select(
                    'request_response.*','productlist.name as productname', 
                    'subcategories.name as subcategory','categories.name as category'
                    )
                ->join('productlist', 'request_response.product_id', '=', 'productlist.id')
                ->join('subcategories', 'request_response.subcategory_id', '=', 'subcategories.id')
                ->join('categories','request_response.category_id','=','categories.id')
                ->where('request_response.suplierid','=', $user_id);

                if($request->flag == 'noapproveddata'){
                    $result=$result->where('status', '!=', 'Approved')
                    ->get();
                }else{
                    $result=$result->get();
                }
            }

            if($request->search_by_quote_id!="" && $request->flag != 'nego'){
                if($search_user->role_id==2){
                    $result = DB::table('request_response')
                    ->select('request_response.*','productlist.name as productname', 'subcategories.name as subcategory','categories.name as category'
                        )
                    ->join('productlist', 'request_response.product_id', '=', 'productlist.id')
                    ->join('subcategories', 'request_response.subcategory_id', '=', 'subcategories.id')
                    ->join('categories','request_response.category_id','=','categories.id')
                    ->where('request_response.request_quote_id','=', $request->search_by_quote_id)
                    ->where('request_response.suplierid','=', $user_id);
                }
                else{
                    $result = DB::table('request_response')
                    ->select(
                        'request_response.*','productlist.name as productname', 'subcategories.name as subcategory','categories.name as category'
                        )
                    ->join('productlist', 'request_response.product_id', '=', 'productlist.id')
                    ->join('subcategories', 'request_response.subcategory_id', '=', 'subcategories.id')
                    ->join('categories','request_response.category_id','=','categories.id')
                    ->where('request_response.request_quote_id','=', $request->search_by_quote_id);
                }
                
                if($request->flag !=''){
                    if($request->cancelorder !=''){
                        $result=$result->where('status', '=', 'Cancelled')->get();
                    }
                    else{
                        $result=$result->limit(3)->get();
                    }
                }else{
                    if($request->cancelorder !=''){
                        $result=$result->where('status', '=', 'Cancelled')->get();
                    }
                    else{
                        $result=$result->get();
                    }
                }
            }

            if($request->search_by_resp_id!=""){
                $result = $result->where('id', '=' , $request->search_by_resp_id);
            }
            if($request->productname!=""){
                $result = $result->where('product_id', '=' , $request->productname);
            }
            if($search_by_category!=""){
                $result = $result->where('category_id', '=' , $search_by_category);
            }
            if($request->response_date!=""){
                $result = $result->where('updated_at', 'like', '%' . $request->response_date . '%');
            }

            foreach($result as $row){
                if($request->quoteflag){
                    array_push($returnData, $row);
                }
                else if($row->customer_id == $user_id &&
                 $search_user->role_id==3){
                    array_push($returnData, $row);
                }
                else if($row->suplierid  == $user_id &&  $search_user->role_id==2){
                    array_push($returnData, $row);
                }
                else if(($row->suplierid  != $user_id && $row->customer_id  != $user_id &&  $search_user->role_id==1)){
                    array_push($returnData, $row);
                }   
            }
            $query = DB::getQueryLog();
            $query = end($query);
            return $this->sendResponse($returnData,  'Successfully Fetch...');
        } 

        public function deleteRequestResponse(Request $request, $id): JsonResponse
        {
            $requestresponse = RequestResponse::where("id", $id)->first();
            if ($requestresponse) {
                $requestresponse->delete();
            }
                return $this->sendResponse([], 'deleted.');
            }

            public function getNegotiation(): JsonResponse
            {
                $negotiate = Negotiation::all();
                return $this->sendResponse($negotiate, 'Successfully get all Negotiation.');
            }

        public function storeNegotiation(Request $request): JsonResponse
        {
            $validator = Validator::make($request->all(), [
                'request_id' => 'required',
                'response_id' => 'required',
                'supplier_id' => 'sometimes',
                'customer_id' => 'sometimes',
                'product_id' => 'required',
                'quantity' => 'required',
                'unit_price' => 'required',
            ]);
    
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $negotiation = new Negotiation();
        $negotiation->request_id = $request->request_id;
        $negotiation->response_id = $request->response_id;
        $negotiation->supplier_id = $request->supplier_id;
        $negotiation->customer_id = $request->customer_id;
        $negotiation->product_id = $request->product_id;
        $negotiation->quantity = $request->quantity;
        $negotiation->unit_price = $request->unit_price;
        $negotiation->resp_ref_id = $request->resp_ref_id;
        $negotiation->status = 'InProgress';
        $negotiation->save();
        $supplier = $request->supplier_id;
        $customer = $request->customer_id;
        $responseid = $request->response_id;
        $search = User::where("id", $supplier)->get();
    

        $flag = $request->usertype;
        if( $flag === 'customer'){
            $customers = User::where("id", $customer)->get();
            foreach($customers as $cust){
                $customer = $cust->ref_id;
                $responseid = RequestResponse::where("id", $responseid)->get();
                foreach($responseid as $resp){
    
                $responseid = $resp->resp_ref_id;
                foreach($search as $s)
                {
                $input['suppliermail'] = $s->email;
                $name = $s->name;
                $roleid = $s->role_id;
                $quantity = $request->quantity;
                $created = '24 hr';
              
                // Mail::to($input['suppliermail'])->send(new NegotiateMail($roleid,$name,$customer,$responseid,$created));

                // $user = User::where('role_id', '=', '1')->get();
    
                // foreach($user as $u){
                // $emails = $u->email;
                // $name = $u->name;   
                // $roleid = $u->role_id;
                // Mail::to($emails)->send(new NegotiateMail($roleid,$name,$customer,$responseid,$created));
        
                // }
            }
            }        
           }  
           Mail::to($input['suppliermail'])->send(new NegotiateMail($roleid,$name,$customer,$responseid,$created));
           $user = User::where('role_id', '=', '1')->get();
           foreach($user as $u){
           $emails = $u->email;
           $name = $u->name;   
           $roleid = $u->role_id;
           Mail::to($emails)->send(new NegotiateMail($roleid,$name,$customer,$responseid,$created));
   
           }
        }
        else{
            $customer = $request->customer_id;
            $responseid = $request->response_id;
            $user = User::where('id', '=', $customer)->get();

            $customers = User::where("id", $supplier)->get();
            foreach($customers as $cust){
                $customer = $cust->ref_id;

                $responseid = RequestResponse::where("id", $responseid)->get();
                foreach($responseid as $resp){

                    $responseid = $resp->resp_ref_id;

                    foreach($user as $us){
                        $emails = $us->email;
                        $name = $us->name;   
                        $quantity = $request->quantity;
                        $created = '24 hr';
                        $roleid = $us->role_id;
                        // Mail::to($emails)->send(new NegotiateMail($roleid,$name,$customer,$responseid,$created));

                        // $user = User::where('role_id', '=', '1')->get();
                    
                        // foreach($user as $u){
                        // $emails = $u->email;
                        // $name = $u->name;   
                        // $roleid = $u->role_id;
                        // Mail::to($emails)->send(new NegotiateMail($roleid,$name,$customer,$responseid,$created));
                    
                        // }
                    
                        }  
                }
            }
            Mail::to($emails)->send(new NegotiateMail($roleid,$name,$customer,$responseid,$created));
            $user = User::where('role_id', '=', '1')->get();
            foreach($user as $u){
                $emails = $u->email;
                $name = $u->name;   
                $roleid = $u->role_id;
                Mail::to($emails)->send(new NegotiateMail($roleid,$name,$customer,$responseid,$created));
            }
        }  
        return response()->json('Successfully send message to suppliers...');
    }

    public function updateNegotiation(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'sometimes',
            'supplier_id' => 'sometimes',
            'quantity' => 'sometimes',
            'unit_price' => 'sometimes',
        ]);
    
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $negotiation = Negotiation::where('id','=',$id)->first();
        $negotiation->supplier_id = $request->supplier_id;
        $negotiation->customer_id = $request->customer_id;
        $negotiation->quantity = $request->quantity;
        $negotiation->unit_price = $request->unit_price;
        $negotiation->status = 'InProgress';
        $negotiation->save();
        $success['success'] = true;
        return $this->sendResponse($success, 'Negotiation updated successfully.');
    }

    public function searchNegotiation(request $request)
    {  
        DB::enableQueryLog();
        $search_by_category=$request->responseid;
    
        $result = Negotiation::withWhereHas('requestQuote', function (Builder $query)  use ($search_by_category) {
            $query->where('id', 'like', "%{$search_by_category}%");
        });
    
        if($request->search_by_quote_id!=""){
            $result = $result->where('request_id', 'like', '%' . $request->search_by_quote_id . '%');
        }
        if($request->response_date!=""){
            $result = $result->where('response_id', 'like', '%' . $request->response_date . '%');
        }
       
        $result=$result->get();
        $query = DB::getQueryLog();
        $query = end($query);
        return $this->sendResponse($result,  'Successfully Fetch...');
    }


    public function getQuoteNego(Request $request,$id): JsonResponse
    {
        $nego = RequestResponse::with('negotiations')
        ->where("request_quote_id", $id)->get();
        return $this->sendResponse( $nego  ,  'Successfully Fetch...');
    }

    public function storeOrder(Request $request): JsonResponse
    {
    $validator = Validator::make($request->all(), [
        'request_id' => 'required',
        'response_id' => 'required', 
        'supplier_id' => 'sometimes',
        'customer_id' => 'sometimes',
        'product_id' => 'required',
        'quantity' => 'required',
        'unit_price' => 'required',
    ]);

    if($validator->fails()){
        return $this->sendError('Validation Error.', $validator->errors());       
    }
    $custorder = new CustOrder;

    $resp = RequestResponse::where('id','=',$request->response_id)
    ->where('resp_ref_id','=',$request->resp_ref_id)
    ->first();


    $custorder->request_id = $request->request_id;
    $custorder->response_id = $request->response_id;
    $custorder->supplier_id = $resp->suplierid;
    $custorder->customer_id = $request->customer_id;
    $custorder->product_id = $request->product_id;
    $custorder->quantity = $request->quantity;
    $custorder->unit_price = $request->unit_price;
    $custorder->resp_ref_id = $request->resp_ref_id;
    $custorder->status = 'Approved';
    $custorder->save();
   
    $requestresp = RequestResponse::where('id','=',$request->response_id)
    ->where('resp_ref_id','=',$request->resp_ref_id)->first();
    $requestresp->status='Approved';
    $requestresp->save();

    $respnego = Negotiation::where('response_id','=',$request->response_id)
    ->where('resp_ref_id','=',$request->resp_ref_id)->get();
    foreach($respnego  as $res){
        if($res->supplier_id == $resp->suplierid){
            $res->status='Approved';
        }else{
            $res->status='Negotiated';
        }
        $res->save();
    }
    $requestresp = RequestResponse::where('request_quote_id','=', $request->request_id)
    ->where('status','!=', 'Cancelled')
    ->where('status','!=', 'Approved')
    ->get();
    foreach($requestresp as $resp){
        $resp->status='Closed';
        $resp->save();
    }

    $requestresps = RequestedQuotes::where('id','=',$request->request_id)->first();
    $requestresps->status='Approved';
    $requestresps->save();
    return response()->json('Successfully send message to suppliers...');
}

        public function getOrder(request $request): JsonResponse
        {  
            DB::enableQueryLog();
            $response_id=$request->response_id;
            $user_id = $request->user_id;
            $finalData=[];
            $resultData = array();
            $user = User::where('id',$user_id)->first();
              
            if($response_id){
                $result = CustOrder::withWhereHas('reqResponse', function (Builder $query)
                  use ($response_id) {
                    $query->where('id', 'like', "%{$response_id}%");
                });
            }
            else{
                $result = CustOrder::withWhereHas('reqResponse.productname.subcategoryname.categoryname', function (Builder $query)  use ($response_id) {
                    $query->where('id', 'like', "%{$response_id}%");
                });
            }
            if($request->order_id!=""){
                $result = $result->where('id', 'like', '%' . $request->order_id . '%');
            }
            if($request->user_id && $user->role_id == 2){
                $result = $result->where('supplier_id', '=', $request->user_id);
            }
            if($request->product!=""){
                $result = $result->where('product_id','=', $request->product);
            }
            if($request->category!=""){
            }

            if($request->order_date!=""){
                $result = $result->where('created_at', '=', $request->order_date);
            }
            $result=$result->get();
            $query = DB::getQueryLog();
            $query = end($query);
            return $this->sendResponse($result,  'Successfully Fetch...');
        }

        public function searchOrder(request $request): JsonResponse
        {  
            DB::enableQueryLog();
            $category_id=$request->categoryname;
            $user_id = $request->user_id;
            $user = User::where('id',$user_id)->first();
            $result = CustOrder::withWhereHas('reqResponse.productname.subcategoryname.categoryname', function (Builder $query)  use ($category_id) {
                $query->where('id', 'like', "%{$category_id}%");
            });
            if($request->order_id!=""){
                $result = $result->where('id', '=', $request->order_id);
            }
            if($request->user_id && $user->role_id == 3){
                $result = $result->where('customer_id', '=', $request->user_id);
            }
            if($request->user_id && $user->role_id == 2){
                $result = $result->where('supplier_id', '=', $request->user_id);
            }
            if($request->order_date!=""){
                $result = $result->where('created_at', '=', $request->order_date);
            }
        
            $result=$result->get();
            $query = DB::getQueryLog();
            $query = end($query);
            return $this->sendResponse($result,  'Successfully Fetch...');
        } 

        public function changeOrderStatus(request $request): JsonResponse
        {
            $id = $request->response_id;
            $req_id = $request->req_id;
            $creditpoint = $request->creditpoint;
            $user_id = $request->user_id;
            $flag = $request->statusflag;
            $custorder = CustOrder::where('response_id',$id)->first();
            if( $flag === 'delivered'){
                $custorder->status='Delivered';
                $custorder->save();
                $requestquote = RequestedQuotes::where('id','=',$req_id)->first();
                $requestquote->status='Delivered';
                $requestquote->save();

                $custorders = CustOrder::where('response_id',$id)->get();
                foreach($custorders as $res){              
                    $customerid = $res->customer_id;
                    $supplierid = $res->supplier_id;
                    $responseid = $res->response_id;
                    

                    $locations = ShippingAddress::where('user_id',$customerid)->get();

                    foreach($locations as $loc){                        
                    $location = $loc->address;
                     
                    $supply = User::where("id", $customerid)->get();
                    foreach($supply as $su){
                            $supp = $su->ref_id;

                    $responses = RequestResponse::where('id',$responseid)->get();
 
                    $search = User::where("id", $customerid)->orWhere("id", $supplierid)->get();
                    foreach($search as $s)
                    {
                        $input['customermail'] = $s->email;
                        $name = $s->name;
                       
                        // Mail::to($input['customermail'])->send(new OrderDelivered($name,$supp,$location));

                        // $user = User::where('role_id', '=', '1')->get();

                        // foreach($user as $u){
                        //  $emails = $u->email;
                        //  $name = $u->name;

                        //  Mail::to($emails)->send(new OrderDelivered($name,$supp,$location));
                        // } 
                    }  
                    }     
                    }
                }  
                Mail::to($input['customermail'])->send(new OrderDelivered($name,$supp,$location));

                $user = User::where('role_id', '=', '1')->get();

                foreach($user as $u){
                 $emails = $u->email;
                 $name = $u->name;

                 Mail::to($emails)->send(new OrderDelivered($name,$supp,$location));
                }     
            }
            else{  
                $validator = Validator::make($request->all(), [
                    'order_id'=>'required',
                    'user_id' => 'required',
                    'company_name' => 'required',
                    'address' => 'required',
                    'city' => 'required',
                    'country' => 'required',
                    'zipcode' => 'required',
                    'invoice_no' => 'required',
                    'delivery_date' => 'required',
                    'invoice_due_date' => 'required',
                    'shipvia' => 'required',
                    'shipmethod' => 'required',
                    'shipterms' => 'required',
                ]);
                if($validator->fails()){
                    return $this->sendError('Validation Error.', $validator->error());
                    }
                    $creditpoints = CreditPoints::where('user_id',$user_id)->first();
                    $credit_point = $creditpoints->credit_point;
                    if($credit_point == 0){
                        echo 'You have Zero Credit Points';
                    }
                    else if($credit_point < $creditpoint){
                    echo ('You have Not Required Credit Points');
                    }
                    else{
                        CreditPoints::where('user_id', $user_id)->decrement('credit_point', $creditpoint);
                        UserProfile::where('user_id', $user_id)->decrement('credit_points', $creditpoint);
                        $creditmap = new CreditMapping();
                        $creditmap->credit_point = $credit_point - $creditpoint;
                        $creditmap->supplier_id = $user_id;
                        $creditmap->credit_use = $creditpoint;
                        $creditmap->save();
                    }
                    $order = CustOrder::where('response_id', $id)->first();
                    $order->status = 'InShipping';
                    $order->save();
                    $requestquote = RequestedQuotes::where('id','=',$req_id)->first();
                    $requestquote->status='InShipping';
                    $requestquote->save();
                    $custorders = CustOrder::where('response_id',$id)->get();

                foreach($custorders as $res){              
                    $customerid = $res->customer_id;
                    $supplierid = $res->supplier_id;
                    $responseid = $res->response_id;
                    $quantity = $res->quantity;
                    $product = $res->product_id;
    
                    $supply = User::where("id", $supplierid)->get();
                        foreach($supply as $su){
                            $supp = $su->ref_id;
                            
                    $responses = RequestResponse::where('id',$responseid)->get();
                    foreach($responses as $resp){
                        $responseid = $resp->resp_ref_id;
                        $created = $resp->estimated_delivery_time;
                    $productname = ProductList::where('id', '=', $product)->get('name');
                    foreach($productname as $p){
                        $product = $p->name;
                     
                    $search = User::where("id", $customerid)->get();
                    foreach($search as $s)
                    {
                        $input['customermail'] = $s->email;
                        $name = $s->name;
                       
                        // Mail::to($input['customermail'])->send(new OrderAcceptShipping($name,$supp,$product,$quantity,$responseid,$created));
                      
                        // $user = User::where('role_id', '=', '1')->get();
                        // foreach($user as $u){
                        //  $emails = $u->email;
                        //  $name = $u->name;      
                        //  Mail::to($emails)->send(new OrderAcceptShipping($name,$supp,$product,$quantity,$responseid,$created));
                        // }  
                    }

                $invoicedetails = new InvoiceDetails;
                $invoicedetails->order_id = $request->order_id;
                $invoicedetails->user_id = $request->user_id;
                $invoicedetails->company_name = $request->company_name;
                $invoicedetails->address = $request->address;
                $invoicedetails->city = $request->city;
                $invoicedetails->country = $request->country;
                $invoicedetails->zipcode = $request->zipcode;
                $invoicedetails->invoice_no = $request->invoice_no;
                $invoicedetails->delivery_date = $request->delivery_date;
                $invoicedetails->invoice_due_date = $request->invoice_due_date;
                $invoicedetails->shipvia = $request->shipvia;
                $invoicedetails->shipmethod = $request->shipmethod;
                $invoicedetails->shipterms = $request->shipterms;
                $invoicedetails->save();
                }  
                Mail::to($input['customermail'])->send(new OrderAcceptShipping($name,$supp,$product,$quantity,$responseid,$created));
                      
                $user = User::where('role_id', '=', '1')->get();
                foreach($user as $u){
                 $emails = $u->email;
                 $name = $u->name;      
                 Mail::to($emails)->send(new OrderAcceptShipping($name,$supp,$product,$quantity,$responseid,$created));
                }  
            }       
            }
        }
    
        }
            return response()->json('Successfully Changed Status and deducted credit points ...');
        }
        public function countResp(request $request): JsonResponse
        {
            $countresp = RequestResponse::all();
            return $this->sendResponse($countresp,  'Successfully Count How many response');
        }
        public function getInvoiceDetails(request $request, $order_id): JsonResponse
        {
            $invoicedetails = InvoiceDetails::where('order_id', $order_id)->get();
            return $this->sendResponse($invoicedetails,  'Successfully get invoice details.');
        }

        public function feedback(request $request):JsonResponse
        {
            $validator = Validator::make($request->all(), [
            'order_id' => 'required',
            'price_rating' => 'sometimes',
            'quality_rating' => 'sometimes',
            'time_rating' => 'sometimes',
            ]);
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
            $user = CustOrder::where('id',$request->order_id)->first();
            $feedback = new Feedback;
            $feedback->order_id = $request->order_id;
            $feedback->user_id = $user->supplier_id;
            $feedback->price_rating = $request->price_rating;
            $feedback->quality_rating = $request->quality_rating;
            $feedback->time_rating = $request->time_rating;
            $feedback->save();
            return $this->sendResponse($feedback,'Sent feedback Successfully.....');
        }

        public function getFeedback(request $request):JsonResponse
        {
            $validator = Validator::make($request->all(), [
                'orderid' => 'sometimes',
            ]);
            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());       
            }
            $orderid = $request->orderid;
            $search = Feedback::where("order_id",$orderid)->get();
            return $this->sendResponse($search,'Successfully Fetch...');
        }
        public function getSupplierRating(request $request):JsonResponse
        {
           $supp_id = $request->user_id;
           $feedbackData = [];
           $request_resp = RequestResponse::where('suplierid', $supp_id)->get('request_quote_id');
           foreach($request_resp as $quote_id){
                $orders = CustOrder::where('request_id', $quote_id->request_quote_id)->get();
                foreach($orders as $order){
                    $feedback = Feedback::where('order_id',  $order->id)->get();
                    array_push($feedbackData,$feedback);
                }
            }
           return $this->sendResponse($feedbackData,'Successfully Fetch data..');
        }
} 