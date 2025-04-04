<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Commission;
use App\Models\Scheme;
use App\Models\Provider;
use App\Models\MerchantCommissions;
use Validator;
use App\Models\Service;

class MerchantCommissionController extends Controller
{
    public function __construct()
    {
       
        $this->provider_id = 584;
        $this->service_id = 19;
        
    }

    function merchant_commission_setup($id,Request $request)
    {
        if (Auth::User()->role_id == 1) {
           
            $service_id = Service::where('status_id', 1)->get(['id']);
            $data = array(
                'page_title' => 'Merchant Commission Setup',
                'merchant_id' => $id
               
            );
            
            $providers = Provider::where('id',$this->provider_id)->where('service_id', $this->service_id)->get();
            $services = Service::where('status_id', 1)->where('id', $this->service_id)->get();
            return view('admin.merchant_user.merchant_commission_setup', compact('providers', 'services'))->with($data);
            
        } else {
            return redirect()->back();
        }

    }

    function merchant_set_operator_commission(Request $request)
    {
       
        if (Auth::User()->role_id == 1) {
           
            $providers = Provider::find($request->provider_id);
            
           
            $data = array(
                'page_title' => 'Merchant Commission Setup',
                'provider_name' => $providers->provider_name,
                'provider_id' => $providers->id,
                'service_id' => $providers->service_id,
                'merchant_id' => $request->merchant_id,
            );
            $merchantCommission = MerchantCommissions::where('merchant_id', $request->merchant_id)->where('provider_id', $request->provider_id)->get();
            return view('admin.merchant_user.set_operator_commission', compact('merchantCommission'))->with($data);
        } 
        else {
            return redirect()->back();
        }
    }

    function merchant_store_commission(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'min_amount' => 'required',
                'max_amount' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
           
            $merchant_id = $request->merchant_id;
            $provider_id = $request->provider_id;
            $service_id = $request->service_id;
            $min_amount = $request->min_amount;
            $max_amount = $request->max_amount;
            $provider_commission_type = $request->provider_commission_type;
            $type = $request->type;
            $st = $request->st;
            $sd = $request->sd;
            $d = $request->d;
            $referral = $request->referral;
            $r = $request->r;
            $trans_type = $request->trans_type;
            $providers = Provider::find($provider_id);
            $whereRaw = 'provider_id ="' . $provider_id . '"';
            if (in_array($providers->service_id, [16,17, 19, 25]) && $provider_commission_type > 0) {
                $whereRaw .= ' AND provider_commission_type ="' . $provider_commission_type . '"';
            }
            $commission = MerchantCommissions::whereRaw($whereRaw)->where('merchant_id', $merchant_id)->where('min_amount', '<=', $min_amount)->where('max_amount', '>=', $max_amount)->first();
            if ($commission) {
                return Response()->json(['status' => 'failure', 'message' => 'Kinldy check min amount or max amount already added in slab']);
            } else {
                $now = new \DateTime();
                $ctime = $now->format('Y-m-d H:i:s');

                MerchantCommissions::insertGetId([
                    'merchant_id' => $merchant_id,
                    'provider_id' => $provider_id,
                    'service_id' => $providers->service_id,
                    'min_amount' => $min_amount,
                    'max_amount' => $max_amount,
                    'provider_commission_type' => $provider_commission_type,
                    'st' => $st,
                    'sd' => $sd,
                    'd' => $d,
                    'r' => $r,
                    'referral' => $referral,
                    'type' => $type,
                    'created_at' => $ctime,
                    'status' => 1,
                    'trans_type' => $trans_type,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'Merchant Commission Successfully Updated']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry Not Permission']);
        }
    }

    function merchant_view_operator_commission(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $id = $request->id;
            $commission = MerchantCommissions::find($id);
            if ($commission) {
                $commissions = array(
                    'id' => $id,
                    'provider_name' => $commission->provider->provider_name,
                    'min_amount' => $commission->min_amount,
                    'max_amount' => $commission->max_amount,
                    'provider_commission_type' => $commission->provider_commission_type,
                    'st' => $commission->st,
                    'sd' => $commission->sd,
                    'd' => $commission->d,
                    'r' => $commission->r,
                    'referral' => $commission->referral,
                    'type' => $commission->type,
                    'trans_type' => $commission->trans_type,
                );
                return Response()->json([
                    'status' => 'success',
                    'commission' => $commissions,
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }


    function merchant_update_operator_commission(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $id = $request->id;
            $min_amount = $request->min_amount;
            $max_amount = $request->max_amount;
            $st = $request->st;
            $sd = $request->sd;
            $d = $request->d;
            $r = $request->r;
            $referral = $request->referral;
            $type = $request->type;
            $trans_type = $request->trans_type;
            MerchantCommissions::where('id', $id)->update([
                'min_amount' => $min_amount,
                'max_amount' => $max_amount,
                'st' => $st,
                'sd' => $sd,
                'd' => $d,
                'r' => $r,
                'referral' => $referral,
                'type' => $type,
                'trans_type' => $trans_type,
            ]);
            return Response()->json(['status' => 'success', 'message' => 'Merchant commission successfully updated']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

   

    function merchant_delete_commission_slab(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $id = $request->id;
            MerchantCommissions::where('id', $id)->delete();
            return Response()->json(['status' => 'success', 'message' => 'Slab Successfully deleted']);
        } else {
            return Response()->json();
        }
    }
}
