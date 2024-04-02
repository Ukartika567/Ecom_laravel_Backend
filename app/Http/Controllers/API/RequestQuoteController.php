<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Product;
use App\Models\ProductList;
use App\Models\SupplierProductsMapping;
use Validator;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\RequestQuote;
use App\Models\Measurement;
use Illuminate\Support\Facades\Auth;
use Otp;
use DB;
use App\Notifications\RequestQuoteMail;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\RequestEmail;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Mail;
use App\Mail\TestUserMail;
use App\Mail\SupplierMail;
use App\Mail\AdminSupplierMail;
use Illuminate\Support\Str;
use App\Models\RequestedQuotes;
use App\Models\RequestResponse;
use App\Models\CancelledQuotes;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
class RequestQuoteController extends BaseController
{
   
    public function regRequestQuote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'product' => 'required',
            'qty' => 'required|numeric',
            'customerid' => 'required',
            'requiredtime' => 'required',
            'category' => 'required',
            'subcategory' => 'required',
            'companyname' => 'required',
            'unit_of_measurement' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }
        $customer_id = $request->customerid;
      
        $supp_id_list=[];
        $int_supp_id=[];
        $finaldata = [];
        if($request->supplierid){
            $supp_ids= json_decode($request->supplierid);
        }
        $unitattr = implode(',',$request->unit_of_measurement);

        if((int)$customer_id || (int)$supp_id){
        }
        else{
            if(gettype($customer_id)=='string'){
                $cust_id = User::where('id',  $customer_id )->get('id');
                $customer_id = $cust_id[0]->id;
            }
        }
        // if($request->supplierid){
        //     // $supp_id_list_ = explode(',', $supp_id_list);
        //     foreach($supp_id_list as $s){
        //     //   echo '$s-'.$s;
        //       if(gettype($s)=='string'){
        //         $sup_id = User::where('id',  $s)->get('id');
        //         array_push($int_supp_id, $sup_id[0]->id);
        //         // $supplier_id = $sup_id[0]->id;
        //         echo 'count- '.count($int_supp_id);
        //     }
        //     else{
        //         echo 'not string...';
        //     }
        // } }
     
        if($request->supplierid){
            foreach ($supp_ids as $suppid) {
                $requestquote = new RequestQuote;
                $tickets='WEQ-';
                $requestquote->quote_ref_id = $tickets.random_int(1000000, 9999999);
                $requestquote->product = $request->product;
                $requestquote->unit_of_measurement = $unitattr;
                $requestquote->customerid = $customer_id;
                $requestquote->supplierid = $suppid;
                $requestquote->qty = $request->qty;
                $requestquote->requiredtime = $request->requiredtime;
                $requestquote->category = $request->category;
                $requestquote->subcategory = $request->subcategory;
                $requestquote->companyname = $request->companyname;
                $requestquote->status = 'New';
                $requestquote->timestamps=false;
                $requestquote->created_at= now();
                $requestquote->save();
                $requestquotes = new RequestedQuotes;
                $requestquotes->quote_ref_id = $requestquote->quote_ref_id;
                $requestquotes->customerid = $customer_id;
                $requestquotes->supplierid = $suppid;
                $requestquotes->product = $requestquote->product;
                $requestquotes->unit_of_measurement = $unitattr;
                $requestquotes->qty = $requestquote->qty;
                $requestquotes->requiredtime = $requestquote->requiredtime;
                $requestquotes->category = $requestquote->category;
                $requestquotes->subcategory = $requestquote->subcategory;
                $requestquotes->companyname = $requestquote->companyname;
                $requestquotes->status = 'New';
                $requestquotes->save();
                $product = $request->product;
                $search = SupplierProductsMapping::with('usersdata')
                ->where('product_id',$product)->get();
                $productname = ProductList::where('id', '=', $product)->get('name');
                foreach($productname as $p){
                    $product = $p->name;
                    $number = 0;
                    foreach($search as $s){    
                        foreach($s->usersdata as $i){
                            $input['email'] = $i->email;
                            $input['id'] = $i->id;
                            $input['name'] = $i->name;
                            $name = $input['name'];
                            $requestid =  $ref = $requestquote->quote_ref_id;
                            $customer = $request->customerid;
                            $quantity = $request->qty;
                            $created = $request->requiredtime;
                            Mail::to($input['email'])->send(new SupplierMail($requestid,$name,$customer,$product,$quantity,$created));
                            $user = User::where('role_id', '=', '1')->get();
                            foreach($user as $u){
                            $emails = $u->email;
                            $name = $u->name;
                            Mail::to($emails)->send(new SupplierMail($requestid,$name,$customer,$product,$quantity,$created));
                            }  
                            $reqemail = new RequestEmail;
                            $reqemail->req_id = $requestquote->id;
                            $reqemail->supplier_id = $input['id'];
                            $reqemail->email_status = 'Yes';
                            $reqemail->save();
                        }
                    }
                }
            }
        }
        else{
            $requestquote = new RequestQuote;
            $tickets='WEQ-';
            $requestquote->quote_ref_id = $tickets.random_int(1000000, 9999999);
            $requestquote->product = $request->product;
            $requestquote->unit_of_measurement = $unitattr;
            $requestquote->customerid = $customer_id;
            // $requestquote->supplierid = $suppid;
            $requestquote->qty = $request->qty;
            $requestquote->requiredtime = $request->requiredtime;
            $requestquote->category = $request->category;
            $requestquote->subcategory = $request->subcategory;
            $requestquote->companyname = $request->companyname;
            $requestquote->status = 'New';
            $requestquote->timestamps=false;
            $requestquote->created_at= now();
            $requestquote->save();
            $requestquotes = new RequestedQuotes;
            $requestquotes->quote_ref_id = $requestquote->quote_ref_id;
            $requestquotes->customerid = $customer_id;
            // $requestquotes->supplierid = $supplier_id;
            $requestquotes->product = $requestquote->product;
            $requestquotes->unit_of_measurement = $unitattr;
            $requestquotes->qty = $requestquote->qty;
            $requestquotes->requiredtime = $requestquote->requiredtime;
            $requestquotes->category = $requestquote->category;
            $requestquotes->subcategory = $requestquote->subcategory;
            $requestquotes->companyname = $requestquote->companyname;
            $requestquotes->status = 'New';
            $requestquotes->save();
            $product = $request->product;
            $search = SupplierProductsMapping::with('usersdata')
            ->where('product_id',$product)->get();
            $productname = ProductList::where('id', '=', $product)->get('name');
            foreach($search as $r){
                $id=$r->supplier_id;
                $product_info = Product::where([['product_id','=',$product],['user_id','=',$id]])->get();
                foreach($r->usersdata as $user){
                    $approval_status = $user->userprofile->approval_status;
                    if($approval_status !== 'Disapproved'){
                        $r['product_info'] = $product_info;
                        array_push($finaldata, $r);
                    }
                }
            }

            foreach($productname as $p){
                $product = $p->name;
                $number = 0;
                foreach($finaldata as $s){
                    echo $s; 
                    foreach($s->usersdata as $i){
                        $input['email'] = $i->email;
                        $input['id'] = $i->id;
                        $input['name'] = $i->name;
                        $name = $input['name'];
                        $requestid =  $ref = $requestquote->quote_ref_id;
                        $customer = $request->customerid;
                        $quantity = $request->qty;
                        $created = $request->requiredtime;
                        // Mail::to($input['email'])->send(new SupplierMail($requestid,$name,$customer,$product,$quantity,$created));
                        // $user = User::where('role_id', '=', '1')->get();
                        // foreach($user as $u){
                        // $emails = $u->email;
                        // $name = $u->name;
                        // Mail::to($emails)->send(new SupplierMail($requestid,$name,$customer,$product,$quantity,$created));
                        // }  
                        // $reqemail = new RequestEmail;
                        // $reqemail->req_id = $requestquote->id;
                        // $reqemail->supplier_id = $input['id'];
                        // $reqemail->email_status = 'Yes';
                        // $reqemail->save();
                    }
                }
            }
            Mail::to($input['email'])->send(new SupplierMail($requestid,$name,$customer,$product,$quantity,$created));
            $user = User::where('role_id', '=', '1')->get();
            foreach($user as $u){
            $emails = $u->email;
            $name = $u->name;
            Mail::to($emails)->send(new SupplierMail($requestid,$name,$customer,$product,$quantity,$created));
            }  
            $reqemail = new RequestEmail;
            $reqemail->req_id = $requestquote->id;
            $reqemail->supplier_id = $input['id'];
            $reqemail->email_status = 'Yes';
            $reqemail->save();
        }
        
        return response()->json('Successfully send message to suppliers and admin...');
}

    public function cancelQuote(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(),[
            'quoteid' => 'required',
            'userid' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }
        $user_id = $request->userid;
        $quote_id = $request->quoteid;
        $cancequote = new CancelledQuotes();
        $cancequote->quote_id = $quote_id;
        $cancequote->user_id = $user_id;
        $cancequote->status = 'Cancelled';
        $cancequote->save();
       return response()->json('Successfully cancel quote...');
    }
    
    public function getCancelQuote(Request $request,$user_id):JsonResponse
    {
        $cancelquotes = CancelledQuotes::with('requestedquote')
        ->where('user_id', '=', $user_id)->get();
        return $this->sendResponse($cancelquotes, 'Successfully Cancel Quote Fetched');
    }

    public function getReqMail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'requestid' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        $requestquote = RequestQuote::all();
        $requestquote = $request->requestid;
        $search = RequestQuote::where("id",$requestquote)->get();
        foreach($search as $s){
            $products = $s->product;
            $search = SupplierProductsMapping::with('usersdata')
                    ->where('product_id',$products)->get('supplier_id');
            echo ' $search-- '.$search;
            foreach($search as $s){    
                foreach($s->usersdata as $i){
                    $user_id= $i->id;
                    if(RequestEmail::where('supplier_id',$user_id)->exists()){
                    $requestemail = RequestEmail::with('userdata')->where('supplier_id',$user_id)->get('supplier_id');
                        return $this->sendResponse('Successfully Fetch...',$requestemail);
                    }
                    else{
                        echo "no match";
                        $requestemail = User::withWhereHas('userprofile')->where('id',$user_id)->get(['id','name','email']); 
                        foreach($requestemail as $req){
                            $input['email'] = $req->email;
                            $id = $req->id;
                            $input['name'] = $req->name;
                            $name = $input['name'];
                            Mail::to($input['email'])->send(new AdminSupplierMail( $input['name']));
                        
                        } 
                        $requestemail = new RequestEmail();
                        $requestemail->req_id = $requestquote;
                        $requestemail->supplier_id = $id;
                        $requestemail->email_status = 'Yes'; 
                        $requestemail->save();  
                        return $this->sendResponse('Successfully Fetch...',$requestemail);               
                    }  
                }
            }
        }
    }

    public function getRequestQuote(request $request)
    {  
        DB::enableQueryLog();
        $search_by_category=$request->categoryname;
        $result = RequestQuote::withWhereHas('quoteproductname.quotesubcategoryname
        .categoryname', function (Builder $query)  use ($search_by_category) {
            $query->where('name', 'like', "%{$search_by_category}%");
        });
        if($request->search_by_quote_id!=""){
            $result = $result->where('id', 'like', '%' . $request->search_by_quote_id . '%');
        }
        if($request->reqdate!=""){
            $result = $result->where('created_at', 'like', '%' . $request->reqdate . '%');
        }
        $result=$result->get();
        $query = DB::getQueryLog();
        $query = end($query);
        return $this->sendResponse($result,  'Successfully Fetch...');
    } 


    public function getRequestedQuotes(request $request)
    {  
        DB::enableQueryLog();
        $user_id = $request->user_id;
        $search_by_category=$request->categoryname;
        $returnData = [];
        $usertype = $request->usertype;
        if($usertype == 'Customer' || $usertype == 'Admin'){
            $search_user = UserProfile::with(['usersdata'])->where('user_id', $user_id);
        }else{
            $search_user = SupplierProductsMapping::with('usersdata')
            ->where('supplier_id',$user_id);
        }
        $quote_id = $request->search_by_quote_id;
        $request_response = '';
        if($usertype == 'Supplier'){
            $request_response = RequestResponse::where([['suplierid', $user_id]])->get();
        }
        else if($usertype == 'Customer'){
            $request_response = RequestResponse::where([['customer_id', $user_id]])->get();
        }
        else{
            $request_response = RequestResponse::get();
        }
        $result = RequestedQuotes::withWhereHas('quoteproductname.quotesubcategoryname.categoryname', function (Builder $query)  use ($search_by_category) {
            $query->where('id', 'like', "%{$search_by_category}%");
        });
        if($quote_id!=""){
            $result = $result->where('id', 'like', '%' . $quote_id . '%');
        }
        if($request->reqdate!=""){
            $result = $result->where('created_at', 'like', '%' . $request->reqdate . '%');
        }
        if($request->product != ""){
            $result = $result->where('product', 'like', '%' . $request->product . '%');
        }
        if($request->categoryname != ""){
            $result = $result->where('category', 'like', '%' . $request->categoryname . '%');
        }
        $quoteId=[];
        $result=$result->get();
        $users= $search_user->get();
        foreach($result as $row){
            foreach($users as $user){
                    foreach($user->usersdata as $userdata){
                        if($user->product_id == $row->product && $userdata->role_id==2){
                            if(count($request_response)>0 && !$request->quoteflag){
                                foreach($request_response as $reqresp){
                                    array_push($quoteId,$reqresp->request_quote_id);
                                    if(!(in_array($row->id,$quoteId))){
                                        if($request->quoteflag){
                                            if(!(in_array($row, $returnData))){
                                                array_push($returnData, $row);
                                            }
                                        }
                                        else{
                                            if($row->status=='New'){
                                                if($row->supplierid != ''){
                                                    if($row->supplierid == $user_id){
                                                        if(!(in_array($row, $returnData))){
                                                            array_push($returnData, $row);
                                                        }
                                                    }
                                                }
                                                else{
                                                    if(!(in_array($row, $returnData))){
                                                        array_push($returnData, $row);
                                                    }
                                                } 
                                            }
                                        }
                                    }
                                }
                            }else{
                                if($request->quoteflag){
                                    if(!(in_array($row, $returnData))){
                                        array_push($returnData, $row);
                                    }
                                }
                                else{
                                    if($row->status=='New'){
                                        if($row->supplierid != ''){
                                            if($row->supplierid == $user_id){
                                                if(!(in_array($row, $returnData))){
                                                    array_push($returnData, $row);
                                                }
                                            }
                                        }
                                        else{
                                            if(!(in_array($row, $returnData))){
                                                array_push($returnData, $row);
                                            }
                                        } 
                                    }
                                }
                            }
                        }
                        else{
                            if($user_id == $row->customerid && $userdata->role_id==3){
                                if(count($request_response)>0 && !$request->quoteflag){
                                    foreach($request_response as $reqresp){
                                        array_push($quoteId,$reqresp->request_quote_id);
                                        if(!(in_array($row->id,$quoteId))){
                                            if($request->quoteflag){
                                                if(!(in_array($row, $returnData))){
                                                    array_push($returnData, $row);
                                                }
                                            }
                                            else{
                                                if(!(in_array($row, $returnData))){
                                                    array_push($returnData, $row);
                                                }
                                            }
                                        }
                                    }
                                }else{
                                    if($request->quoteflag){
                                        if(!(in_array($row, $returnData))){
                                            array_push($returnData, $row);
                                        }
                                    }
                                    else{
                                        if($row->status=='New'){
                                            if(!(in_array($row, $returnData))){
                                                array_push($returnData, $row);
                                            }
                                        }
                                    }
                                }
                                
                            }elseif($userdata->role_id==1){
                                if(count($request_response)>0 && !$request->quoteflag){
                                    foreach($request_response as $reqresp){
                                        array_push($quoteId,$reqresp->request_quote_id);
                                        if(!(in_array($row->id,$quoteId))){
                                            if($request->quoteflag){
                                                if(!(in_array($row, $returnData))){
                                                    array_push($returnData, $row);
                                                }
                                            }
                                            else{
                                                if(!(in_array($row, $returnData))){
                                                    array_push($returnData, $row);
                                                }
                                            }
                                        }
                                    }
                                }else{
                                    if($request->quoteflag){
                                        if(!(in_array($row, $returnData))){
                                            array_push($returnData, $row);
                                        }
                                    }
                                    else{
                                        if(!(in_array($row, $returnData))){
                                            array_push($returnData, $row);
                                        }
                                    }
                                }

                            }
                        }
                }
            } 
        }
        $query = DB::getQueryLog();
        $query = end($query);
        return $this->sendResponse($returnData,  'Successfully Fetch...');
    } 


    public function updateRequestQuote(Request $request, $id): JsonResponse
    {
        $input = $request->all();
        $requestquote->product = $request->product;
        $requestquote->qty = $request->qty;
        $requestquote->customerid = $request->customerid;
        $requestquote->requiredtime = $request->requiredtime;
        $requestquote->category = $request->category;
        $requestquote->subcategory = $request->subcategory;
        $requestquote->companyname = $request->companyname;
        $requestquote->save();
        return $this->sendResponse($requestquote, 'Request Quote updated successfully.');
    }

    public function  catSupplierList(Request $request, $product)
    {
        $search = SupplierProductsMapping::with('usersdata')
        ->where('product_id',$product)->get();
        return $this->sendResponse($search, 'Successfully Supplier Fetched');
    }

    public function deleteRequestQuote(Request $request, $id): JsonResponse
    {
        $requestquote = RequestedQuotes::where("id", $id)->first();
        if ($requestquote) {
            $requestquote->delete();
        }
        return $this->sendResponse([], 'deleted.');
    }

    public function getProdReq(Request $request,$id):JsonResponse
    {
        $prodreq =  Userprofile::with(['requestQuoteData'])->where("product_id", $id)->get();
        return $this->sendResponse($prodreq, 'Successfully Request Quote Fetched');
    }

    public function getMeasurement(Request $request):JsonResponse
    {
        $measure = Measurement::select('id','title','attribute')->get();
        return $this->sendResponse($measure, 'Successfully Measurement Fetched....');
    }
    }