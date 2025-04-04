<?php

namespace App\Http\Controllers\Api;

use App\Models\UserStaticQrAccount;
use Helpers;
use App\Traits\Authenticate;
use App\Models\MerchantPayoutapiLog;
use App\Http\Controllers\Controller;
use App\Easebuzz\StaticQr;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PayinStaticQrController extends Controller
{
    use Authenticate;

    protected $payout, $pincode, $api_id, $provider_id,$api_log,$easebuzz_static_qr;

    public function __construct()
    {
        $this->api_log = new MerchantPayoutapiLog;
        $this->easebuzz_static_qr = new StaticQr;
        $this->provider_id = 591;
        $this->api_id = 4;
    }

    public function store(Request $request)
    {
        $input = $request->only(['merchant_reference_id']);
        $input['mode'] = 1; // 1=live
        $input['type'] = 1;
        $return = $this->keyAuthentication($request);
        if ($return->getData()->success == false) {
            $this->api_log->storeData($input, json_encode($return->getData(), true));
            return $return;
        }

        $api_key = $request->bearerToken();
        $user = User::where('api_key', $api_key)->first();
        $validator = Validator::make($input, [
            'merchant_reference_id' => 'required|min:3|max:100|alpha_dash:ascii'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'success' => false,
                'message' => 'Validation error, please check errors parameter.',
                'errors' => $errors
            ]);
        }

        $virtual_acc_exists = UserStaticQrAccount::where('user_id', $user->id)->where('is_active', 1)->first();
        if ($virtual_acc_exists) {
            $virtual_acc_exists = $virtual_acc_exists->toArray();
            $return_array = [
                'success' => true,
                'status' => "SUCCESS",
                'message' => "",
                'merchant_reference_id' => $virtual_acc_exists['merchant_reference_id'],
                'reference_id' => $virtual_acc_exists['unique_request_number'],
                'upi_qrcode_image' => $virtual_acc_exists['upi_qrcode_remote_file_location'],
                'upi_qrcode_pdf' => $virtual_acc_exists['upi_qrcode_scanner_remote_file_location'],
                'upi_intent' => $virtual_acc_exists['upi_intent'],
                'timestamp' =>  $virtual_acc_exists['created_at']
            ];
            return response()->json($return_array);
        }

        if (isset($input['merchant_reference_id']) && !empty($input['merchant_reference_id'])) {
            $order_id_exists = UserStaticQrAccount::where('merchant_reference_id', $input['merchant_reference_id'])
                ->where('user_id', $user->id)
                ->exists();

            if ($order_id_exists) {
                $errors = [];
                $errors['merchant_reference_id'][0] = 'Duplicate merchant_reference_id, field must be unique.';

                return response()->json([
                    'success' => false,
                    'message' => 'Validation error, please check errors parameter.',
                    'errors' => $errors
                ]);
            }
        }
        list($master_id, $wire_key, $salt_key) =  getCredentials($user->credentials_id);
        DB::beginTransaction();
        try {
            $input['reference_id'] = Helpers::generateReferenceID();
            $input['created_at'] = date('Y-m-d H:i:s');
            $input['description'] = $user->fullname . " - Virtual Account";
            $input['label'] = $user->fullname;
            $parameters = [
                'description' => $input['description'],
                'label' => $user->fullname,
                'unique_request_number' => $input['reference_id'],
                'key' => $wire_key
            ];

            $response = $this->easebuzz_static_qr->doTransaction($parameters, $user->credentials_id);
            if ($response['status'] == "failure") {
                return response()->json([
                    'success' => true,
                    'status'=>"FAILED",
                    'message' => $response['message']
                ]);
            }
            $data = $response['data']['virtual_account'];
            $input['upi_qrcode_image'] = $data['upi_qrcode_remote_file_location'];
            $input['upi_qrcode_pdf'] = $data['upi_qrcode_scanner_remote_file_location'];
            $input['message'] = $response['message'] ?? 'QR Generated Successfully.';
            $input['merchant_reference_id'] = $request->merchant_reference_id;
            $input['status'] =  "SUCCESS";
            $payfor = urlencode("Pay for ". $user->fullname);
            $upi_intent ="upi://pay?tr=".$input['reference_id']."&tid=&pa=".$data['virtual_upi_handle']."&mc=&pn=".urlencode($user->fullname)."&am=&cu=INR&tn=".$payfor;
            $input['upi_intent'] = $upi_intent;

            UserStaticQrAccount::insertGetId([
                'user_id' => $user->id,
                'merchant_reference_id' => $request->merchant_reference_id,
                'unique_request_number' => $input['reference_id'],
                'virtual_account_id' => $data['id'],
                'account_number' => $data['account_number'],
                'label' => $input['label'],
                'virtual_account_number' => $data['virtual_account_number'],
                'virtual_ifsc_number' => $data['virtual_ifsc_number'],
                'virtual_upi_handle' => $data['virtual_upi_handle'],
                'description' => $input['description'],
                'is_active' => $data['is_active'],
                'auto_deactivate_at' => $data['auto_deactivate_at'],
                'upi_qrcode_remote_file_location' => $data['upi_qrcode_remote_file_location'],
                'upi_qrcode_scanner_remote_file_location' => $data['upi_qrcode_scanner_remote_file_location'],
                'created_at' => $input['created_at'],
                'updated_at' => $input['created_at'],
                'upi_intent'=>$upi_intent
            ]);
            DB::commit();
            return response()->json($this->getResponseArray($input));
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Server error, please contact trustxpay support' . $e->getMessage(),
            ]);
        }
    }

    public function get(Request $request)
    {
        $input = $request->only(['merchant_reference_id']);
        $input['mode'] = 1; // 1=live
        $input['type'] = 1;
        $return = $this->keyAuthentication($request);
        if ($return->getData()->success == false) {
            $this->api_log->storeData($input, json_encode($return->getData(), true));
            return $return;
        }

        $api_key = $request->bearerToken();
        $user = User::where('api_key', $api_key)->first();
        // $validator = Validator::make($input, [
        //     'merchant_reference_id' => 'required|min:3|max:100|alpha_dash:ascii'
        // ]);

        // if ($validator->fails()) {
        //     $errors = $validator->errors();
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Validation error, please check errors parameter.',
        //         'errors' => $errors
        //     ]);
        // }
        try {
            $virtual_acc_exists = UserStaticQrAccount::where('user_id', $user->id)->where('is_active', 1)->first();
            if ($virtual_acc_exists) {
                $virtual_acc_exists = $virtual_acc_exists->toArray();
                $return_array = [
                    'success' => true,
                    'status' => "SUCCESS",
                    'message' => "",
                    'merchant_reference_id' => $virtual_acc_exists['merchant_reference_id'],
                    'reference_id' => $virtual_acc_exists['unique_request_number'],
                    'upi_qrcode_image' => $virtual_acc_exists['upi_qrcode_remote_file_location'],
                    'upi_qrcode_pdf' => $virtual_acc_exists['upi_qrcode_scanner_remote_file_location'],
                    'upi_intent' => $virtual_acc_exists['upi_intent'],
                    'timestamp' =>  $virtual_acc_exists['created_at'],
                ];
                return response()->json($return_array);
            } else {
                return response()->json([
                    'success' => true,
                    'status' => "FAILED",
                    'message' => 'No data found'
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Server error, please contact trustxpay support' . $e->getMessage(),
            ]);
        }
    }

    public function status(Request $request)
    {

    }

    public function getResponseArray($input)
    {
        $return_array = [
            'success' => true,
            'status' => $input['status'],
            'message' => $input['message'],
            'merchant_reference_id' => $input['merchant_reference_id'],
            'reference_id' => $input['reference_id'],
            'upi_qrcode_image' => $input['upi_qrcode_image'],
            'upi_qrcode_pdf' => $input['upi_qrcode_pdf'],
            'upi_intent' => $input['upi_intent'],
            'timestamp' =>  $input['created_at'],
        ];
        $this->api_log->storeData($input, $return_array);

        return $return_array;
    }
}
