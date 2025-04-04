<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Library\BasicLibrary;
use App\Models\WhiteListBank;
use Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hash;
use Validator;
use App\Models\Bankdetail;
use App\Models\Paymentmethod;
use App\Models\Loadcash;

use App\Models\User;
use App\Models\Returnrequest;
use DB;

class PaymentrequestController extends Controller
{
    public function __construct()
    {
        $this->bank_name = '';
        $this->bank_account_name = '';
        $this->bank_ifsc = '';
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->cdn_link = $companies->cdn_link;
    }

    function payment_request(Request $request)
    {
        $usersData = Auth::user();
        $bankdetails = Bankdetail::where('company_id', Auth::User()->company_id)->where('status_id', 1)->get();
        $methods = Paymentmethod::where('status_id', 1)->get();
        $data = array('page_title' => 'Payment Request');
        $loadcash = Loadcash::where('user_id', Auth::id())->get();
        $whiteListBanks = WhiteListBank::where('user_id', Auth::id())->where('type', 1)->get();
        if (empty($usersData->account_number)) {
            $number = Auth::id();
            $totalNumberLength = 12;
            $accountNumber = \Helpers::padWithZeros($number, $totalNumberLength);
            User::where('id', Auth::id())->update(['account_number' => 'PAYO' . $accountNumber]);
            $usersData->account_number = $accountNumber;
        }
        $bank_ifsc = $this->bank_ifsc;
        $bank_name = $this->bank_name;
        $bank_account_name = $this->bank_account_name;
        return view('agent.balance.payment_request', compact('usersData', 'bank_account_name', 'bank_name', 'bank_ifsc', 'bankdetails', 'methods', 'loadcash', 'whiteListBanks'))->with($data);
    }


    function balance_return_request()
    {
        $returnrequest = Returnrequest::where('user_id', Auth::id())->where('status_id', 3)->get();
        $data = array('page_title' => 'Balance Return Request');
        return view('agent.balance.balance_return_request', compact('returnrequest'))->with($data);

    }

    public function addWhiteLabel()
    {
        return view('agent.white-label.add');
    }

    public function editWhiteLabel($id)
    {
        $data = WhiteListBank::where('id', $id)->first();
        return view('agent.white-label.edit', compact('data'));
    }

    public function storeWhiteLabel(Request $request)
    {
        $rules = array(
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'payee_name' => 'required',
            'bank_name' => 'required',
            'bank_proof' => 'required|image|mimes:jpeg,png,jpg',
        );
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        try {
            $record = WhiteListBank::where('account_number', $request->account_number)->first();
            if (!empty($record)) {
                return Response()->json(['status' => 'failure', 'message' => 'Account number already exist.']);
            } else {
                $path = "whitelist-bank";
                $image_url = Helpers::upload_s3_image($request->bank_proof, $path);
                WhiteListBank::create([
                    'user_id' => Auth::id(),
                    'ifsc_code' => $request->ifsc_code,
                    'account_number' => $request->account_number,
                    'payee_name' => $request->payee_name,
                    'bank_name' => $request->bank_name,
                    'bank_proof' => $this->cdn_link . $image_url,
                    'status' => 0, //Pending for approval
                ]);
                $userdetails = User::find(Auth::id());
                $letter = collect([
                    'title' => "Bank white list Notification",
                    'body' => "$userdetails->name $userdetails->last_name  white list bank added. please review.",
                ]);
                $parent_id = array(1);
                $library = new BasicLibrary();
                $library->send_notification($parent_id, $letter);
                return Response()->json(['status' => 'success', 'message' => 'Save successfully']);
            }
        } catch (\Exception $exception) {
            return Response()->json(['status' => 'failure', 'message' => $exception->getMessage()]);
        }
    }

    public function updateWhiteLabel(Request $request)
    {
        $rules = array(
            'ifsc_code' => 'required',
            'account_number' => 'required',
            'payee_name' => 'required',
            'bank_name' => 'required',
        );
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        try {
            $record = WhiteListBank::where('account_number', $request->account_number)->whereNot('id', $request->id)->first();
            if (!empty($record)) {
                return Response()->json(['status' => 'failure', 'message' => 'Account number already exist.']);
            } else {
                $updateData = [
                    'ifsc_code' => $request->ifsc_code,
                    'account_number' => $request->account_number,
                    'payee_name' => $request->payee_name,
                    'bank_name' => $request->bank_name,
                    'status' => 0, //Pending for approval
                ];
                if (isset($request->bank_proof) && !empty($request->bank_proof)) {
                    $path = "whitelist-bank";
                    $imageUrl = Helpers::upload_s3_image($request->bank_proof, $path);
                    $updateData['bank_proof'] = $this->cdn_link . $imageUrl;
                }
                WhiteListBank::where('id', $request->id)->update($updateData);
                $userdetails = User::find(Auth::id());
                $letter = collect([
                    'title' => "Bank white list Notification",
                    'body' => "$userdetails->name $userdetails->last_name  updated information in white list bank. please review.",
                ]);
                $parent_id = array(1);
                $library = new BasicLibrary();
                $library->send_notification($parent_id, $letter);
                return Response()->json(['status' => 'success', 'message' => 'Updated successfully']);
            }
        } catch (\Exception $exception) {
            return Response()->json(['status' => 'failure', 'message' => $exception->getMessage()]);
        }
    }

    public function deleteWhiteLabel($id)
    {
        return view('agent.white-label.delete', compact('id'));
    }

    public function destroyBank(Request $request)
    {
        WhiteListBank::where('id', $request->id)->delete();
        return Response()->json(['status' => 'success', 'message' => 'Deleted successfully']);
    }
}
