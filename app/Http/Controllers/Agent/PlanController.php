<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Helpers;
use App\Models\Api;
use App\Models\Provider;
use App\Models\State;
use App\Models\Apiprovider;
use Validator;


class PlanController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $api = Api::where('vender_id', 2)->first();
        if ($api) {
            $this->key = 'Bearer ' . $api->api_key;
            $this->url = "";
            $this->api_id = $api->id;
        }
    }

    function plan_type(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $provider_id = $request->provider_id;
        $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
        $operator_code = (empty($apiproviders)) ? '' : $apiproviders->operator_code;
        if (empty($operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'Api provider code not added!']);
        }
        $url = "";
        $api_request_parameters = array();
        $method = 'GET';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        if (!empty($res->data)) {
            $data = $res->data;
            return Response()->json(['status' => 'success', 'message' => 'Successful..!', 'data' => $data]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Not found']);
        }
    }

    function prepaid_plan(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'state_id' => 'required|exists:states,id',
            'plantype_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $provider_id = $request->provider_id;
        $state_id = $request->state_id;
        $plantype_id = $request->plantype_id;
        $states = State::find($state_id);
        $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
        $operator_code = (empty($apiproviders)) ? '' : $apiproviders->operator_code;
        if (empty($operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'Api provider code not added!']);
        }
        $url = "";
        $api_request_parameters = array(
            'provider_id' => $operator_code,
            'circle_id' => $states->mobileapi_code,
            'plantype_id' => $plantype_id,
        );
        $method = 'POST';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response, true);
        $status = $res['status'];
        if ($status == 0) {
            $data = $res['data'];
            return Response()->json([
                'status' => 'success',
                'message' => $res['message'],
                'provider_id' => $provider_id,
                'state_id' => $state_id,
                'plans' => $data,
            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res['message']]);
        }
    }

    function dth_plan(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'plantype_id' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $provider_id = $request->provider_id;
        $plantype_id = $request->plantype_id;
        $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
        $operator_code = (empty($apiproviders)) ? '' : $apiproviders->operator_code;
        if (empty($operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'Api provider code not added!']);
        }
        $url = "";
        $api_request_parameters = array(
            'provider_id' => $operator_code,
            'plantype_id' => $plantype_id,
        );
        $method = 'POST';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response, true);
        $status = $res['status'];
        if ($status == 0) {
            $data = $res['data'];
            return Response()->json([
                'status' => 'success',
                'message' => $res['message'],
                'provider_id' => $provider_id,
                'plans' => $data,
            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => $res['message']]);
        }

    }



    function roffer_plan(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'mobile_number' => 'required',
            'state_id' => 'required|exists:states,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $provider_id = $request->provider_id;
        $state_id = $request->state_id;
        $states = State::find($state_id);
        $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
        $operator_code = (empty($apiproviders)) ? '' : $apiproviders->operator_code;
        if (empty($operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'Api provider code not added!']);
        }
        $url = "";
        $api_request_parameters = array(
            'provider_id' => $operator_code,
            'number' => $mobile_number,
            'circle_id' => $states->mobileapi_code,
        );
        $method = 'POST';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response, true);
        $status = $res['status'];
        if ($status == 0) {
            $data = $res['data'];
            return Response()->json([
                'status' => 'success',
                'message' => $res['message'],
                'provider_id' => $provider_id,
                'state_id' => $state_id,
                'plans' => $data,
            ]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'R Offer not found']);
        }

    }


    function dth_customer_info(Request $request)
    {
        $rules = array(
            'provider_id' => 'required|exists:providers,id',
            'number' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $provider_id = $request->provider_id;
        $number = $request->number;
        $providers = Provider::find($provider_id);
        $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
        $operator_code = (empty($apiproviders)) ? '' : $apiproviders->operator_code;
        if (empty($operator_code)) {
            return Response()->json(['status' => 'failure', 'message' => 'Api provider code not added!']);
        }
        $url = $this->url . 'customer_info';
        $api_request_parameters = array(
            'provider_id' => $operator_code,
            'number' => $number,
        );
        $method = 'POST';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response, true);
        $status = $res['status'];
        if ($status == 0) {
            $records = $res['data'];
            $tel = $number;
            $operator = $providers->provider_name;
            $countRecord = count($records);
            if ($countRecord == 1) {
                foreach ($records as $key => $value) {
                    $MonthlyRecharge = $value['MonthlyRecharge'];
                    $Balance = $value['Balance'];
                    $customerName = $value['customerName'];
                    $NextRechargeDate = $value['NextRechargeDate'];
                    $planname = $value['planname'];
                }
                return Response()->json(['status' => 'success',
                    'tel' => $tel,
                    'operator' => $operator,
                    'MonthlyRecharge' => $MonthlyRecharge,
                    'Balance' => $Balance,
                    'customerName' => $customerName,
                    'NextRechargeDate' => $NextRechargeDate,
                    'planname' => $planname]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => $records['desc']]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Customer Info not found']);
        }
    }




    function dth_refresh(Request $request)
    {
        $rules = array(
            'number' => 'required',
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $provider_id = $request->provider_id;
        $number = $request->number;
        $providers = Provider::find($provider_id);
        if ($providers) {
            $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
            $operator_code = (empty($apiproviders)) ? '' : $apiproviders->operator_code;
            if (empty($operator_code)) {
                return Response()->json(['status' => 'failure', 'message' => 'Api provider code not added!']);
            }
            $url = $this->url . 'refresh';
            $api_request_parameters = array(
                'provider_id' => $operator_code,
                'number' => $number,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->key];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response);
            $records = $res->data;
            return Response()->json([
                'status' => 'success',
                'message' => $records->desc,
            ]);

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Provider not found']);
        }
    }

    function dth_roffer(Request $request)
    {
        $rules = array(
            'number' => 'required',
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $provider_id = $request->provider_id;
        $number = $request->number;
        $providers = Provider::find($provider_id);
        if ($providers) {
            $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
            $operator_code = (empty($apiproviders)) ? '' : $apiproviders->operator_code;
            if (empty($operator_code)) {
                return Response()->json(['status' => 'failure', 'message' => 'Api provider code not added!']);
            }
            $url = $this->url . 'roffer';
            $api_request_parameters = array(
                'provider_id' => $operator_code,
                'number' => $number,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->key];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response, true);
            $status = $res['status'];
            if ($status == 0) {
                $records = $res['data'];
                return Response()->json(['status' => 'success', 'plans' => $records]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'R Offer not found']);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Provider not found']);
        }
    }

    function find_operator(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required|digits:10',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $mobile_number = $request->mobile_number;
        $url = $this->url . 'operator_info';
        $api_request_parameters = array(
            'service_id' => 1,
            'number' => $mobile_number,
        );
        $method = 'POST';
        $header = ["Accept:application/json", "Authorization:" . $this->key];
        $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
        $res = json_decode($response);
        if ($res->status == 0) {
            $data = $res->data;
            $providers = Apiprovider::where('api_id', $this->api_id)->where('operator_code', $data->provider_id)->first();
            $states = State::where('mobileapi_code',$data->circle_id)->first();
            $details = array(
                'provider_id' => (empty($providers)) ? 0 : $providers->provider_id,
                'provider_name' => (empty($providers)) ? '' : $providers->provider->provider_name,
                'state_id' => (empty($states)) ? 0 : $states->id,
                'state_name' => (empty($states)) ? '' : $states->name,
            );
            return Response()->json(['status' => 'success', 'message' => 'Successful..', 'details' => $details]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Failed']);
        }

    }


    function dth_info_by_mobile(Request $request)
    {
        $rules = array(
            'mobile_number' => 'required',
            'provider_id' => 'required|exists:providers,id',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $provider_id = $request->provider_id;
        $number = $request->mobile_number;
        $providers = Provider::find($provider_id);
        if ($providers) {
            $apiproviders = Apiprovider::where('api_id', $this->api_id)->where('provider_id', $provider_id)->first();
            $operator_code = (empty($apiproviders)) ? '' : $apiproviders->operator_code;
            if (empty($operator_code)) {
                return Response()->json(['status' => 'failure', 'message' => 'Api provider code not added!']);
            }
            $url = $this->url . 'dthinfo_by_mobile';
            $api_request_parameters = array(
                'provider_id' => $operator_code,
                'number' => $number,
            );
            $method = 'POST';
            $header = ["Accept:application/json", "Authorization:" . $this->key];
            $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
            $res = json_decode($response, true);
            $status = $res['status'];
            if ($status == 0) {
                $records = $res['data'];
                foreach ($records as $key => $value) {
                    if (empty($value['status'])) {
                        return Response()->json(['status' => 'failure', 'message' => $value['desc']]);
                    }
                    $MonthlyRecharge = $value['MonthlyRecharge'];
                    $Balance = $value['Balance'];
                    $customerName = $value['customerName'];
                    $NextRechargeDate = $value['NextRechargeDate'];
                    $planname = $value['planname'];
                    $Custmerid = $value['Custmerid'];
                }
                return Response()->json(['status' => 'success',
                    'tel' => $number,
                    'operator' => $providers->provider_name,
                    'MonthlyRecharge' => $MonthlyRecharge,
                    'Balance' => $Balance,
                    'customerName' => $customerName,
                    'NextRechargeDate' => $NextRechargeDate,
                    'planname' => $planname,
                    'Custmerid' => $Custmerid,
                ]);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'R Offer not found']);
            }

        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Provider not found']);
        }
    }
}
