<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Library\PermissionLibrary;
use App\Models\Api;
use Illuminate\Support\Facades\Auth;
use App\Models\Service;
use App\Models\Provider;
use App\Models\MerchantApiCommissions;
use Validator;

class ApiCommissionMasterController extends Controller
{
    public function __construct()
    {
       
        $this->provider_id = 584;
        $this->service_id = 19;
        
    }
    public function api_commission_master(Request $request){

        if (Auth::User()->role_id == 1) {
           
                $service_id = Service::where('status_id', 1)->get(['id']);
                $data = array(
                    'page_title' => 'Api Commission Master',
                    'provider_id' =>  $this->provider_id,
                    'service_id' =>  $this->service_id
                   
                );
               
                $providers = Provider::where('id', $this->provider_id)->where('service_id', $this->service_id)->where('status_id', 1)->get();
                $services = Service::where('status_id', 1)->where('id', $this->service_id)->get();
                return view('admin.api-commission-master.api_commission_master', compact('providers', 'services'))->with($data);
            
        } else {
            return redirect()->back();
        }
    }
   

    public function setup_api_commission_master(Request $request)
    {

        if (Auth::User()->role_id == 1) {
            
            $providers = Provider::find($request->provider_id);
            // dd($providers);
            
           
            $data = array(
                'page_title' => 'Api Commission Master Setup',
                'provider_name' => $providers->provider_name,
                'provider_id' => $providers->id,
                'service_id' => $providers->service_id,
                
            );
            $merchantApiCommission = MerchantApiCommissions::where('provider_id', $request->provider_id)->where('service_id', $request->service_id)->get();
            return view('admin.api-commission-master.setup_api_commission_master', compact('merchantApiCommission'))->with($data);
        } 
        else {
            return redirect()->back();
        }
    }

    public function store_api_commission_master(Request $request)
    {
        // dd($request->all());
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'provider_commission_type' => 'required|exists:apis,id',
                'provider_id' => 'required|exists:providers,id',
                'min_amount' => 'required',
                'max_amount' => 'required',
                'type' => 'required',
                'commission_type' => 'required',
                'commission' => 'required',
                'trans_type' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $provider_commission_type = $request->provider_commission_type;
            $provider_id = $request->provider_id;
            $service_id = $request->service_id;
            $min_amount = $request->min_amount;
            $max_amount = $request->max_amount;
            $type = $request->type;
            $commission_type = $request->commission_type;
            $commission = $request->commission;
            $trans_type = $request->trans_type;
            $apicommissions = MerchantApiCommissions::where('provider_id', $provider_id)->where('service_id', $service_id)->where('provider_commission_type', $provider_commission_type)->where('min_amount', '<=', $min_amount)->where('max_amount', '>=', $max_amount)->first();
            if ($apicommissions) {
                return Response()->json(['status' => 'failure', 'message' => 'Kinldy check min amount or max amount already added in slab']);
            }
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $providers = Provider::find($provider_id);
            MerchantApiCommissions::insertGetId([
                'merchant_id' => 0,
                'provider_id' => $provider_id,
                'service_id' => $providers->service_id,
                'provider_commission_type' => $provider_commission_type,
                'commission_type' => $commission_type,
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'commission' => $commission,
                'type' => $type,
                'status' => 1,
                'trans_type' => $trans_type,
                'created_at' => $ctime,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Commission Successfully Updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }
    }

    function view_api_commission_master(Request $request)
    {
        $rules = array(
            'id' => 'required|exists:merchant_api_commissions,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
        }
        $id = $request->id;
        $apicommissions = MerchantApiCommissions::find($id);
        
        if ($apicommissions) {
            $commissions = array(
                'id' => $id,
                'provider_commission_type' => $apicommissions->provider_commission_type,
                'provider_name' => $apicommissions->provider->provider_name,
                'min_amount' => $apicommissions->min_amount,
                'max_amount' => $apicommissions->max_amount,
                'type' => $apicommissions->type,
                'commission_type' => $apicommissions->commission_type,
                'commission' => $apicommissions->commission,
                'trans_type' => $apicommissions->trans_type,
            );
            return Response()->json([
                'status' => 'success',
                'message' => 'Successful..',
                'commission' => $commissions,
            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Record not found!!']);
        }
    }

    function update_api_commission_master(Request $request)
    {
        // dd($request->all());
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:merchant_api_commissions,id',
                'provider_commission_type' => 'required|exists:apis,id',
                'min_amount' => 'required',
                'max_amount' => 'required',
                'type' => 'required',
                'commission_type' => 'required',
                'commission' => 'required',
                'trans_type' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            $provider_commission_type = $request->provider_commission_type;
            $min_amount = $request->min_amount;
            $max_amount = $request->max_amount;
            $type = $request->type;
            $commission_type = $request->commission_type;
            $commission = $request->commission;
            $trans_type = $request->trans_type;
            MerchantApiCommissions::where('id', $id)->update([
                'provider_commission_type' => $provider_commission_type,
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'commission' => $commission,
                'type' => $type,
                'commission_type' => $commission_type,
                'trans_type' => $trans_type,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'commission successfully updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }
    }

    function delete_api_commission_master(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'id' => 'required|exists:merchant_api_commissions,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $id = $request->id;
            MerchantApiCommission::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Slab Successfully deleted']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission!']);
        }

    }
}
