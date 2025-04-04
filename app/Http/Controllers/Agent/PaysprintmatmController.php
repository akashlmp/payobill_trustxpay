<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use Crypt;
use App\Models\Matmtransaction;

use App\Library\BasicLibrary;

// paysptint api
use App\Paysprint\MicroAtm as MicroAtm;

class PaysprintmatmController extends Controller
{

    public function __construct()
    {
        $this->api_id = 1;
        $this->provider_id = 587;
    }


    function merchantDetails(Request $request)
    {
        $user_id = Auth::id();
        $library = new BasicLibrary();
        $activeService = $library->getActiveService($this->provider_id, $user_id);
        $serviceStatus = $activeService['status_id'];
        if ($serviceStatus == 1 && Auth::user()->role_id == 8) {
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $insert_id = Matmtransaction::insertGetId([
                'user_id' => Auth::id(),
                'created_at' => $ctime,
                'status_id' => 3,
            ]);
            $libraries = new MicroAtm();
            $data = $libraries->getDetails($insert_id);
            return Response()->json(['status' => 'success', 'message' => 'Successful..', 'data' => $data]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Service is currently inactive. Please contact customer care for further assistance.']);
        }
    }
}
