<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\Categorycontroller;
use App\Http\Controllers\API\SubcategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\RequestQuoteController;
use App\Http\Controllers\API\CatSubcategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\API\CertificateController;
use App\Http\Controllers\API\RequestResponseController;
use App\Http\Controllers\SendMailController;
use App\Http\Controllers\API\QuestionnaireController;
use App\Http\Controllers\API\CreditPointsController;

Route::post('/register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('userrole', [UserController::class, 'userRole']);
Route::post('send-otp', [UserController::class, 'sendotp']);

Route::post('reset-password', [UserController::class, 'resetPassword']);
Route::get('/allCatlistProd', [CatSubcategoryController::class, 'allCatlistProduct'])->name('allCatlistProduct');
// Route::get('sendmail', [SendMailController::class, 'send_mail'])->name('send_mail');
Route::get('/productlist/{subcategory_id}', [CatSubcategoryController::class,
 'productdetails'])->name('productdetails');
Route::resource('categorylist', CategoryController::class);
Route::resource('subcategorylist', SubcategoryController::class);
Route::post('listSupplier', [UserController::class, 'SearchSupplier'])->name("SearchSupplier");   
// Route::middleware('auth:sanctum')->group( function () {
Route::get('/categorysubdetails/{category_id}', 
    [CatSubcategoryController::class, 'catsubdetails'])->name('catsubdetails');
Route::get('/allproductlist', [CatSubcategoryController::class, 'allProduct'])
    ->name('allproduct');
Route::get('/getproduct/{prod_id}', [CatSubcategoryController::class, 'getproduct'])->name('getproduct');    
Route::post('SearchSupplierlist', 
    [UserController::class, 'SearchSupplier'])->name("SearchSupplier");  
    Route::get('getmeasurements',[RequestQuoteController::class, 'getMeasurement']); 
    
    Route::group(['middleware' => ['jwt.verify']], function(){

    Route::resource('products', ProductController::class);
    Route::resource('category', Categorycontroller::class);
    Route::resource('subcategory', SubcategoryController::class);
    Route::post('register-user-with-token', [UserController::class, 'register']);
    Route::get('logout', [UserController::class, 'logout']);
    Route::post('send-otp-with-token', [UserController::class, 'sendotp']);
    Route::post('reset-password-with-token', [UserController::class, 'resetPassword']);
    

    Route::get('get-profile/{user_id}',[UserController::class,'getUserProfile']);
    Route::put('update-profile/{user_id}',[UserController::class,'updateUserProfile']);
    Route::post('addproduct',[UserController::class,'addProduct']);
    Route::put('update-bankdetails/{user_id}',[UserController::class,'updateUserBankDetails']);
    Route::put('update-shippingaddress/{user_id}',[UserController::class, 'updateUserShippingAddress']); 
    // Route::get('get-companyinfo/{user_id}',[UserController::class,'getCompanyInfo']);

    // companyinfo
    Route::get('get-companyinfo/{user_id}',[UserController::class,'getCompanyInfo'])->name('getcompanyinfo');
    Route::put('update-companyinfo/{user_id}',[UserController::class,
    'updateCompanyInfo'])->name('updatecompanyinfo');

    
    Route::put('update-qltystandrd/{user_id}',[UserController::class,
    'updateQltyStandard'])->name('updateqltystandard');

    Route::put('update-suppexperience/{user_id}',[UserController::class,
    'updateSuppExperience'])->name('updatesuppexperience');

    Route::put('customer-support/{user_id}',[UserController::class,
    'customerSupport'])->name('customersupport');

    // Route::get('get-customersupport/{user_id}',[UserController::class,
    // 'getcustomerSupport'])->name('getcustomersupport');

    Route::post('CustomerList', [UserController::class, 'CustomerList'])->name("customerlist");  
    Route::post('SupplierList', [UserController::class, 'SupplierList'])->name("supplierlist");
    Route::post('suppapproval/{user_id}', [UserController::class, 'SupplierApproval'])->name("supplierapproval");    
    Route::post('SearchSupplier', [UserController::class, 'SearchSupplier'])->name("SearchSupplier");   
    Route::delete('deleteCustomer/{user_id}', [UserController::class, 'deleteCustomer'])->name("deletecustomer");    
    Route::get("notificationData/{user_id}", [NotificationController::class, 'getNotificationData']);
    Route::post("notificationData", [NotificationController::class, 'insertNotificationData']);
   
//  category catlist routes
    Route::get('/allCatlist', [CatSubcategoryController::class, 'allCatlist'])->name('allcatlist');
    Route::post('/suppcatlist', [CatSubcategoryController::class, 'suppcatlist'])->name('suppcatlist');
    Route::post('/allcatsubprodlist/{user_id}', [CatSubcategoryController::class, 'allCatSubProdlist'])->name('allCatSubProdlist');
    Route::get('/allProduct', [CatSubcategoryController::class, 'allProduct'])
    ->name('allproduct');
    Route::get('/singleCatlist/{name}', [CatSubcategoryController::class, 'singleCatlist'])->name('singlecatlist');
    Route::get('/catsubdetails/{category_id}', [CatSubcategoryController::class, 'catsubdetails'])->name('catsubdetails');
    Route::get('/productdetails/{subcategory_id}', [CatSubcategoryController::class, 'productdetails'])->name('productdetails');
    Route::post('/searchSubcat', [CatSubcategoryController::class, 'searchSubcat'])->name('searchSubcat');
    Route::get('/searchcatsubprod/{name}', [CatSubcategoryController::class,'searchCatSubProd'])->name('searchcatsubprod');
    // requestquote start
    Route::post('reqquote',[RequestQuoteController::class,'regRequestQuote']);
    Route::post('cancelquote',[RequestQuoteController::class,'cancelQuote']);
    Route::get('get-cancelquote/{user_id}',[RequestQuoteController::class,'getCancelQuote']);
    Route::post('get-reqquote',[RequestQuoteController::class,'getRequestedQuotes']);
    Route::put('update-reqquote/{id}',[RequestQuoteController::class,'updateRequestQuote']);
    Route::delete('delete-reqquote/{id}',[RequestQuoteController::class,'deleteRequestQuote']);
    Route::post('catreqquote/{product}',[RequestQuoteController::class,'catSupplierList']);
    Route::get('getmeasurement',[RequestQuoteController::class, 'getMeasurement']);
    // requested Quotes start
        Route::post('get-requestedquotes',[RequestQuoteController::class,'getRequestedQuotes']);
    // RequestResponse
    Route::post('storereqresp/',[RequestResponseController::class,'storeRequestResponse']);
    Route::put('update-response/{id}',[RequestResponseController::class,'updateResponse']);
    Route::post('searchreqresp/',[RequestResponseController::class,'searchRequestResponse']);
    Route::post('delete-reqresp/{id}',[RequestResponseController::class,'deleteRequestResponse']);
    Route::get('getprodreq/{id}',[RequestQuoteController::class,'getProdReq']);
    Route::post('getreqmail/',[RequestQuoteController::class,'getReqMail']);
    // negotiation api's
    Route::post('storenegotiation/',[RequestResponseController::class,'storeNegotiation']);
    Route::post('updatenegotiation/{id}',[RequestResponseController::class,'updateNegotiation']);
    Route::get('getnegotiation',[RequestResponseController::class,'getNegotiation']);
    Route::post('searchnegotiation/',[RequestResponseController::class,'searchNegotiation']);
    Route::get('getquotenego/{id}',[RequestResponseController::class,'getQuoteNego']);
    
    // order api
    Route::post('storeorder',[RequestResponseController::class,'storeOrder']);
    Route::post('getorder',[RequestResponseController::class,'getOrder']);
    Route::post('searchorder',[RequestResponseController::class,'searchOrder']);
    Route::post('changeorderstatus',[RequestResponseController::class,'changeOrderStatus']);
    // Route::post('addinvoicedetails',[RequestResponseController::class,'addInvoiceDetails']);
      Route::get('getinvoicedetails/{order_id}',[RequestResponseController::class,'getInvoiceDetails']);
    Route::post('feedback',[RequestResponseController::class,'feedback']);
    Route::post('getfeedback',[RequestResponseController::class,'getFeedback']);
    Route::get('countresp',[RequestResponseController::class, 'countResp']);
    Route::post('getsupplier_rating',[RequestResponseController::class,'getSupplierRating']);

        // credit point admin end

    Route::post('admincredit',[CreditPointsController::class,'adminCredit']);
    Route::post('user_creditpoint',[CreditPointsController::class,'UserCreditPoint']);

    // ticket support system

    
    Route::post('ticket',[UserController::class, 'ticketSupport'])->name("ticketSupport");
//Certifications
    Route::resource('certificate', CertificateController::class);
    // Questionnaire Api
    Route::resource('questionnaire', QuestionnaireController::class);
    // Route::post('getquestion', UserController::class, 'getquestions');
    Route::post('getquestion',[UserController::class,'getquestions']);
});

