<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserProfile;
use App\Models\UserBankDetails;
use App\Models\ShippingAddress;
use App\Models\CompanyInfo;
use App\Models\CreditPoints;
use App\Models\ProductList;
use App\Models\Product;
use App\Models\Questionnaire;
use Illuminate\Validation\Rule;
 use Illuminate\Support\Facades\Auth;
use Validator;
use Otp;
use App\Notifications\ResetPasswordVerificationNotification;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Support\Facades\DB;
use File;
use Illuminate\Http\JsonResponse;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\Token;
use App\Models\TicketSupport;
use App\Models\CutomerSupport;
use App\Models\SupplierProductsMapping;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Mail;
use App\Mail\UserApprovalMail;
class UserController extends BaseController
{
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
  
  
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'role_id'  => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user = new User;
        $user->role_id = $request->role_id;
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->password_txt = $request->password;
        $user->password = bcrypt($request->password);
        $role_id = $request->role_id;
        $rolee = (int)$role_id;
        if($rolee == 2){
        $tickets='WSES-';
            $user->ref_id = $tickets.random_int(100000, 999999);
        }
        elseif($rolee == 3){
            $ticketss='WSEC-';
            $user->ref_id = $ticketss.random_int(100000, 999999);
        }
        $user->save();

        $userprofile = new UserProfile;
        $userbankdetails = new UserBankDetails;
        $shippingaddress = new ShippingAddress;
        $user->role_id = $request->role_id;
        $companyinfo = new CompanyInfo;
        $role_id = $request->role_id;
       $rolee = (int)$role_id;
        if($rolee == 2){
            $companyinfo->user_id = $user->id;
            $companyinfo->save();
        }

        $userprofile->user_id = $user->id;
        $userprofile->approval_status = 'Disapproved';
        $userbankdetails->user_id = $user->id;
        $shippingaddress->user_id = $user->id;
     
        $userprofile->save();
        $userbankdetails->save();
        $shippingaddress->save();
        $user->role_id = $request->role_id;

        $creditpoint = new CreditPoints;
        $role_id = $request->role_id;
        $rolee = (int)$role_id;
        if($rolee == 2){
            $creditpoint->user_id = $user->id;
            $creditpoint->save();
        }

        $custsupport = new CutomerSupport;
        if($rolee == 2){
            $custsupport->user_id = $user->id;
            $custsupport->save();
        }

        return $this->sendResponse($user, 'Registration is Successfully Done.');
    }

    public function login(Request $request): JsonResponse
    {
    
        if(JWTAuth::attempt(['email' => $request->usernameoremail, 'password' => $request->password])){
            $credentials = JWTAuth::attempt(['email' => $request->usernameoremail, 'password' => $request->password]);
            $user = JWTAuth::user();
        }else if(JWTAuth::attempt(['username' => $request->usernameoremail, 'password' => $request->password])){
            $credentials = JWTAuth::attempt(['username' => $request->usernameoremail, 'password' => $request->password]);
            $user = JWTAuth::user();
        }
        else{
            return $this->sendError('Login credentials are invalid.', ['error' => 'Unauthorised']);
        }
        try {
            if (!$token = $credentials) {
                return response()->json([
                 'success' => false,
                 'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                 'success' => false,
                 'message' => 'Could not create token.',
                ], 500);
        }
        return response()->json([
            'user' => $user,
            'success' => true,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 1,
            'usertype' =>  UserRole::where("id",$user->role_id)->get('type'),
            'message' => 'User login Successfully.'
        ]);
    

    }
    
    public function userRole(Request $request): JsonResponse
    {
        $roles = UserRole::all();
        return Response::json($roles, 200);
    }
    public function sendotp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user = User::where('email', '=', $request->email)->first();
        $user->notify(new ResetPasswordVerificationNotification());
        $success['success'] = true;
        return response()->json($success, 200);
    }
    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|max:6',
            'password' => 'required|min:6',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $this->otp = new Otp;
        $otp2 = $this->otp->validate($request->email, $request->otp);
        if (!$otp2->status) {
            return response()->json($otp2, 401);
        }
        $user = User::where('email', $request->email)->first();
        $user->update(['password_txt' => $request->password]);
        $user->update(['password' => bcrypt($request->password)]);
        $success['success'] = true;
        return response()->json($success, 200);
    }

    public function logout(Request $request): JsonResponse
    {
        $forever = true;
        JWTAuth::parseToken()->invalidate( $forever );
        return response()->json('Successfully logged out',200);
    }

    public function getUserProfile($user_id): JsonResponse
    {
        $user = User::with('userprofile.userbankdetails.usershippingaddress')
        ->where("id", $user_id)->get();
        if ($user->isEmpty()) {
            return response()->json('User Not Found Please Register User...', 404);
        } 
            
        else {
            $data = compact('user');
            return response()->json($data, 200);
        }
    }

    public function updateUserProfile(Request $request, $user_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'username' => 'required|unique:users,username,'.$user_id.',id',
            'email' => 'required|email|unique:users,email,'.$user_id.',id',
            'gender' => 'required|in:M,F,O',
            'date_of_birth' => 'required|date_format:Y-m-d',
            'mobile' => 'required|numeric|unique:userprofiles,mobile,'.$user_id.',user_id',
            'address' => 'required',
            'zipcode' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $user = User::find($user_id);        
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $userprofile = UserProfile::where('user_id','=',$user_id)->first();
        if($request->hasfile('profile_picture'))
        {
            $destination = 'uploads/profiles_pictures/'.$userprofile->profile_picture;
            if(File::exists($destination))
            {
                File::delete($destination);
            }
            $file = $request->file('profile_picture');
            $filename = $file->getClientOriginalName();
            $file->move('uploads/profiles_pictures/', $filename);
            $userprofile->profile_picture = $filename;
        }
        $userprofile->mobile = $request->mobile;
        $userprofile->gender = $request->gender;
        $userprofile->date_of_birth = $request->date_of_birth;
        $userprofile->address = $request->address;
        $userprofile->zipcode = $request->zipcode;
        $userprofile->city = $request->city;
        $userprofile->state = $request->state;
        $userprofile->country = $request->country;
        $user->save();
        $userprofile->save();
        $success['user'] = $user;
        $success['success'] = true;
        return $this->sendResponse($success, 'Profile updated successfully.');
    }

    public function getquestions(Request $request): JsonResponse
    {
        $parent_ques_id =  $request->parent_ques_id;
        $user_id = $request->user_id;
        $questions = Questionnaire::where('parent_ques_id', $parent_ques_id)
        ->where('customer_id', $user_id)
        ->get();
        return $this->sendResponse($questions, 'Successfully get all the questions.');  
    }

    public function addProduct(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id'=>'required',
            'category' => 'required',
            'subcategory' => 'required',
            'product' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $product=$request->product;
        $user_id =$request->user_id;
        $recordlist=SupplierProductsMapping::where("supplier_id", $user_id)->get();
        $listData='';
        $prodnamelist=[];
        $ids =[];
        foreach($recordlist as $record)
        {   
            if(in_array($record->product_id, $request->product)){
                $id= $record->product_id;
                if(!(in_array($id ,$ids))){
                    array_push($ids, $id);
                }
                $prod_name = ProductList::whereIn('id',$ids)->get();
                foreach($prod_name as $p){
                    $name = $p->name; 
                    if(!(in_array( $name ,$prodnamelist ))){
                        array_push($prodnamelist,  $p->name);
                    }  
                }
                $prodslist= implode(',',$prodnamelist);
                 $listData =  $prodslist." "."Already Exist.";
            }
        }
        if ($listData !='') {
            return $this->sendError($listData);
        }
        $product = collect($request->product);
        for($i=0;$i<$product->count();$i++)
        {
            $suppliermapping = new SupplierProductsMapping;
            $suppliermapping->supplier_id = $request->user_id;
            $suppliermapping->category = $request->category;
            $suppliermapping->subcategory = $request->subcategory;
            $suppliermapping->product_id = $product[$i];
            $suppliermapping->save();
        }
        $success['success'] = true;
        return $this->sendResponse($success, 'Product added successfully.');
    }
    
    public function updateUserBankDetails(Request $request, $user_id):JsonResponse
    {
   
        $validator = Validator::make($request->all(),[
          'ifsc_code'=> 'required|numeric',
          'account_type' =>'required',
          'account_number' =>'required|numeric',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }
        $userbankdetail = UserBankDetails::where('user_id','=',$user_id)->first();
        $userbankdetail->ifsc_code = $request->ifsc_code;
        $userbankdetail->account_type = $request->account_type;
        $userbankdetail->account_number = $request->account_number;
        $userbankdetail->save();
        $success['userbankdetail'] = $userbankdetail;
        $success['success'] = true;
        return $this->sendResponse($success, 'Bank Details Updated successfully.');
    }

    public function updateUserShippingAddress(Request $request, $user_id):JsonResponse
    {
        $validator = Validator::make($request->all(),[
           'address'=> 'required',
           'zipcode'=>'required|numeric',
           'city'=>'required',
           'state'=>'required',
           'country'=>'required'  
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error', $validator->errors());
        }
        $shippingaddr = ShippingAddress::where('user_id','=',$user_id)->first();
        $shippingaddr->address = $request->address;
        $shippingaddr->zipcode = $request->zipcode;
        $shippingaddr->city = $request->city; 
        $shippingaddr->state = $request->state;
        $shippingaddr->country = $request->country;
        $shippingaddr->save();
        $success['shippingaddr'] = $shippingaddr;
        $success['success']=true;
        return $this->sendResponse($success, 'ShippingAddress Updated Successfully.');
    }

        public function getCompanyInfo($user_id): JsonResponse
        {
            $user = DB::TABLE('users')
                ->where('users.id', $user_id)
                ->JOIN('company_infos', 'users.id', '=', 'company_infos.user_id')
                ->JOIN('customer_support', 'users.id', '=', 'customer_support.user_id')
                ->get();
            if ($user->isEmpty()) {

                return response()->json('cannot get data...', 401);
            } 
            else {
                $data = compact('user');
                return response()->json($data, 200);
            }
            
        }
    public function updateCompanyInfo(Request $request, $user_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'companyname' => 'sometimes',
            'contactpersonname' => 'sometimes|max:100',
            'companyaddr' => 'sometimes',
            'companyemail' => 'sometimes',
            'companyphone' => 'sometimes',
            'businessname' => 'sometimes',
            'businesstype' => 'sometimes',
            'businessregnum' => 'sometimes',
            'taxidentifynum' => 'sometimes',
            'contactname' => 'sometimes',
            'contactemail' => 'sometimes',
            'contactmobile' => 'sometimes',
            'businessregcertificate' => 'sometimes',
            'license' => 'sometimes',
            'financialstability' => 'sometimes',
            'bankletter' => 'sometimes',
            'businessperformancemetric' => 'sometimes',
            'businessref' => 'sometimes'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $companyinfo = CompanyInfo::where('user_id','=',$user_id)->first();
        $companyinfo->companyName = $request->companyname;
        $companyinfo->contactpersonname = $request->contactpersonname;
        $companyinfo->companyaddr = $request->companyaddr;
        $companyinfo->companyemail = $request->companyemail;
        $companyinfo->companyphone = $request->companyphone;
        $companyinfo->businessname = $request->businessname;
        $companyinfo->businesstype = $request->businesstype;
        $companyinfo->businessregnum = $request->businessregnum;
        $companyinfo->taxIdentifynum = $request->taxidentifynum;
        $companyinfo->contactname = $request->contactname;
        $companyinfo->contactemail = $request->contactemail;
        $companyinfo->contactmobile = $request->contactmobile;
        $companyinfo->businessperform = $request->businessperformancemetric;

        if($request->hasfile('businessregcertificate')){
            $destination = 'businessRegCertificate/'. $companyinfo->businessregcertificate;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('businessregcertificate');
            $filename = 'businessreg'.'_'.$file->getClientOriginalName();
            $file->move('businessRegCertificate/', $filename);
            $companyinfo->businessregcertificate =  $filename;
        }
        if($request->hasfile('license')){
            $destination = 'licenseCertificate/'. $companyinfo->licensecertificate;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('license');
            $filename = 'license'.'_'.$file->getClientOriginalName();
            $file->move('licenseCertificate/', $filename);
            $companyinfo->licensecertificate =  $filename;
        }

        if($request->hasfile('financialstability')){
            $destination = 'financialStability/'. $companyinfo->financialstability;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('financialstability');
            $filename = 'financial'.'_'.$file->getClientOriginalName();
            $file->move('financialStability/', $filename);
            $companyinfo->financialstability =  $filename;
        }

        if($request->hasfile('bankletter')){
            $destination = 'bankLetter/'. $companyinfo->bankletter;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('bankletter');
            $filename = 'bankletter'.'_'.$file->getClientOriginalName();
            $file->move('bankLetter/', $filename);
            $companyinfo->bankletter =  $filename;
        }

        if($request->hasfile('businessref')){
            $destination = 'businessRef/'. $companyinfo->businessref;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('businessref');
            $filename = 'businessref'.'_'.$file->getClientOriginalName();
            $file->move('businessRef/', $filename);
            $companyinfo->businessref =  $filename;
        }

        $companyinfo->save();
        $success['companyinfo'] = $companyinfo;
        $success['success']=true;
        return $this->sendResponse($success, 'Successfully Supplier Company Information Updated');
    }
    public function updateQltyStandard(Request $request, $user_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qltystandard' => 'sometimes',
            'qltycertificate' => 'sometimes|max:100',
            'financialstatement' => 'sometimes',
            'qltystandrdfile' => 'sometimes',
            'qltycertificatefile' => 'sometimes',
            'qltyfinancialfile' => 'sometimes',
            'refundpolicy'=> 'sometimes'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $companyinfo = CompanyInfo::where('user_id','=',$user_id)->first();
        $companyinfo->qltystandard = $request->qltystandard;
        $companyinfo->qltycertificate = $request->certificate;
        $companyinfo->financialstatement = $request->financialstatement;
        $companyinfo->refundpolicy = $request->refundpolicy;
        

        $companyinfo->qltystandrdfile = $request->qltystandrdfile;
        $companyinfo->qltycertificatefile = $request->qltycertificate;
        $companyinfo->qltyfinancialfile = $request->qltyfinancialfile;
     

        if($request->hasfile('qltystandrdfile')){
            $destination = 'qltyStandrdfile/'. $companyinfo->qltystandrdfile;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('qltystandrdfile');
            $filename = 'qltystandrd'.'_'.$file->getClientOriginalName();
            $file->move('qltyStandrdfile/', $filename);
            $companyinfo->qltystandrdfile =  $filename;
        }
        if($request->hasfile('qltycertificate')){
            $destination = 'qltyCertificatefile/'. $companyinfo->qltycertificatefile;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('qltycertificate');
            $filename = 'qltycertificate'.'_'.$file->getClientOriginalName();
            $file->move('qltyCertificatefile/', $filename);
            $companyinfo->qltycertificatefile =  $filename;
        }

        if($request->hasfile('qltyfinancialfile')){
            $destination = 'qltyFinancialfile/'. $companyinfo->qltyfinancialfile;
            if(File::exists($destination)){
               File::delete($destination);
            }
            $file = $request->file('qltyfinancialfile');
            $filename = 'qltyfinancial'.'_'.$file->getClientOriginalName();
            $file->move('qltyFinancialfile/', $filename);
            $companyinfo->qltyfinancialfile =  $filename;
        }

        $companyinfo->save();
        $user = UserProfile::where('user_id', $user_id)->first();
        if($companyinfo->suppagreement === 'agreed' && $companyinfo->termandcondition === 'agreed' && $companyinfo->refundpolicy === 'agreed'){
            $user->approval_status = 'Approved';
        }
        else{
            $user->approval_status = 'Disapproved';
        }
        $user->save();
        $success['companyinfo'] = $companyinfo;
        $success['success']=true;
        return $this->sendResponse($success, 'Successfully Supplier Quality Standard Updated');
    }
    
    public function updateSuppExperience(Request $request, $user_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'aboutbusiness' => 'sometimes',
            'custserved' => 'sometimes|max:100',
            'testimonials' => 'sometimes',
            'suppagreement' => 'sometimes',
            'termandcondition' => 'sometimes'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $companyinfo = CompanyInfo::where('user_id','=',$user_id)->first();
        if($request->aboutbusiness){
            $companyinfo->aboutbusiness = $request->aboutbusiness;
        }
        if($request->custserved){
            $companyinfo->customerserved = $request->custserved;
        }
        if($request->testimonials){
            $companyinfo->testimonialsref = $request->testimonials;
        }
        if($request->suppagreement){
            $companyinfo->aggreement = $request->suppagreement;
        }
        if($request->termandcondition){
            $companyinfo->termandcondition = $request->termandcondition;
        }

        $companyinfo->save();
        $user = UserProfile::where('user_id', $user_id)->first();
        if($companyinfo->suppagreement == 'agreed' && $companyinfo->termandcondition === 'agreed' && $companyinfo->refundpolicy === 'agreed'){
            $user->approval_status = 'Approved';
        }
        else{
            $user->approval_status = 'Disapproved';
        }
        $user->save();
        $success['companyinfo'] = $companyinfo;
        $success['success']=true;
        return $this->sendResponse($success, 'Successfully Supplier Experience Updated');
    }

    public function customerSupport(Request $request, $user_id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supporthr' => 'sometimes',
            'supportaddr' => 'sometimes',
            'mobile' => 'sometimes',
            'email' => 'sometimes',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $custsupport = CutomerSupport::where('user_id','=',$user_id)->first();
        $custsupport->phoneno = $request->mobile;
        $custsupport->supporthr = $request->supporthr;
        $custsupport->email = $request->email;
        $custsupport->supportaddr = $request->supportaddr;

        $custsupport->save();
        $success['customersupport'] = $custsupport;
        $success['success']=true;
        return $this->sendResponse($success, 'Successfully Customer Support Updated');
    }

    
    public function getcustomerSupport(Request $request, $user_id): JsonResponse
    {
       
        $custsupport = CutomerSupport::where('user_id','=',$user_id)->first();
        if(is_null($custsupport)){
            return $this->sendError('Customer not found.');
        }
        $success['customersupport'] = $custsupport;
        $success['success']=true;
        return $this->sendResponse($success, 'Successfully Customer Support Updated');
    }

    public function CustomerList(Request $request)
    {  
        DB::enableQueryLog();
        $customername = $request->customername;
        $customeremail = $request->customeremail;
        $result = UserProfile::withWhereHas('customerlist', function (Builder $query) 
         use ($customername,$customeremail) {
            $query->where("role_id", 3)->
            where('name', 'like', "%{$customername}%")
            ->where('email', 'like', "%{$customeremail}%");
        });

        if($request->phoneNo!=""){
            $result = $result->where('mobile', 'like', '%' . $request->phoneNo . '%');
        }
        $result=$result->get();
        $query = DB::getQueryLog();
        $query = end($query);
        return $this->sendResponse($result,  'Successfully Fetch...');
    } 

    public function SupplierList(Request $request)
    {  
        DB::enableQueryLog();
        $suppliername = $request->suppliername;
        $supplieremail = $request->supplieremail;

        if($request->flag){
            $result = UserProfile::withWhereHas('supplierlist', function (Builder $query) 
            use ($suppliername,$supplieremail) {
               $query->where("role_id", 2)->
               where('name', 'like', "%{$suppliername}%")->where('email', 'like', "%{$supplieremail}%");
           });

            $suppprod = SupplierProductsMapping::with('usersdata.userprofile','usersdata.orderrating')
            ->where('product_id',$prod_id);
            $suppprod=$suppprod->get();

        }
        else{
            $result = UserProfile::withWhereHas('supplierlist', function (Builder $query) 
            use ($suppliername,$supplieremail) {
               $query->where("role_id", 2)->
               where('name', 'like', "%{$suppliername}%")->where('email', 'like', "%{$supplieremail}%");
           });
        }
        if($request->phoneno!=""){
            $result = $result->where('mobile', 'like', '%' . $request->phoneno . '%');
        }
        $result=$result->get();
        $query = DB::getQueryLog();
        $query = end($query);
        return $this->sendResponse($result,  'Successfully Fetch...');
    } 

    public function SearchSupplier(Request $request)
    {  
        DB::enableQueryLog();
        $prod_id = $request->prod_id;
        $finaldata = [];
        $result = SupplierProductsMapping::with('usersdata.userprofile','usersdata.orderrating')
        ->where('product_id',$prod_id);
        $result=$result->get();
       
        foreach($result as $r){
            $id=$r->supplier_id;
            $product_info = Product::where([['product_id','=',$prod_id],['user_id','=',$id]])->get();
            foreach($r->usersdata as $user){
                $approval_status = $user->userprofile->approval_status;
                if($approval_status !== 'Disapproved'){
                    $r['product_info'] = $product_info;
                    array_push($finaldata, $r);
                }
            }
        }
        $query = DB::getQueryLog();
        $query = end($query);
        return $this->sendResponse($finaldata,  'Successfully Fetch Supplier...');
    }

    public function SupplierApproval(Request $request, $user_id):JsonResponse
    {
        $user = UserProfile::where('user_id',$user_id)->first();
        if($user){
            if($user->approval_status=== 'Approved'){
                $user->approval_status = 'Disapproved';
            }
            else{
                $user->approval_status = 'Approved';
            }
            $user->save();
        }
        $userdata = User::where('id', $user_id)->first();
        Mail::to($userdata->email)->send(new UserApprovalMail($user->user_id, $userdata->name,$user->approval_status));
        return $this->sendResponse([], 'Supplier approved...');
    }

    public function  deleteCustomer(Request $request, $id)
    {
        $user = User::where("id", $id)->first();

        if ($user) {
            $user->delete();
      
        }     
        return $this->sendResponse([], 'deleted.');
    }
  
    public function ticketSupport(Request $request){
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|numeric',
            'suplier_id' => 'required|numeric',
            'order_id' => 'required|numeric',
            'complainBy' => 'required',
            'comment'  => 'required',
            'commentType' => 'required',
            'commentCount' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        } 
        $ticket = new TicketSupport;
        $tickets='WSET-';
        $ticket->ticketNumber = $tickets.random_int(100000, 999999);
        $ticket->customer_id = $request->customer_id;
        $ticket->suplier_id = $request->suplier_id;
        $ticket->order_id = $request->order_id;
        $ticket->complainBy = $request->complainBy;
        $ticket->comment = $request->comment;
        $ticket->commentType = $request->commentType;
        $ticket->commentCount = $request->commentCount;
        $ticket->save();
        return $this->sendResponse($ticket, 'Registration is Successfully Done.');
    }
}
