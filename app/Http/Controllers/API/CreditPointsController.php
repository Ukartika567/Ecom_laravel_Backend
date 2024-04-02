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
use App\Models\CustOrder;
use Illuminate\Contracts\Database\Eloquent\Builder;
use App\Models\ShippingAddress;
use App\Models\Feedback;
use App\Models\RequestedQuotes;
use App\Models\CreditPoints;
use App\Models\AdminCreditPoints;
use App\Models\UserProfile;
class CreditPointsController extends BaseController
{
    public function adminCredit(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'credit_point' => 'required',
            'amount' => 'required',
            'supplier_id' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()); 
        }
        $admincredit = new AdminCreditPoints();
        $admincredit->credit_point = $request->credit_point;
        $admincredit->amount = $request->amount;
        $admincredit->supplier_id = $request->supplier_id;
        $admincredit->save();
        $credit = CreditPoints::where('user_id', $request->supplier_id)->first();
        $user = UserProfile::where('user_id', $request->supplier_id)->first();
        if($credit){
            $credit->increment('credit_point', $request->credit_point);
            $credit->save();
        } else{
            $credit = new CreditPoints();
            $credit->credit_point = $request->credit_point;
            $credit->user_id = $request->supplier_id;
            $credit->save();
        }
        if($user){
            $user->credit_points=$credit->credit_point;
            $user->save();
        }
        return $this->sendResponse($admincredit,"Successfully paid");
    }
}
