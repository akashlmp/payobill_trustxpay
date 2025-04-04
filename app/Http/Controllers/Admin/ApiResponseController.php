<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Apiresponse;


class ApiResponseController extends Controller
{
    //
    public function index(Request $request)
    {
        $data = [
            'page_title' => "API's Response",
            'urls' => url('admin/api-responses-data'),

        ];

        // Get the 'type' from the request
        $type = $request->input('type');


        // Query to get the filtered API responses
        if ($type) {
            $data = [
                'page_title' => "API's Response",
                'urls' => url('admin/api-responses-data'). '?' . 'type=' . $type,

            ];
        } else {
            $data = [
                'page_title' => "API's Response",
                'urls' => url('admin/api-responses-data'),

            ];
        }


        return view('admin.api-response.index')->with($data);

    }

    function api_response_ajax(Request $request)
    {

        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value


        $type = $request->get('type');

        if(!empty($type))
        {
            $totalRecords = ApiResponse::select('count(*) as allcount')->where('response_type',$type)->count();
            $totalRecordswithFilter = ApiResponse::select('count(*) as allcount')->where('response_type',$type)
                                    ->where(function ($query) use ($searchValue) {
                                        $query->where('api_type', 'like', '%' . $searchValue . '%')
                                        ->orWhere('message', 'like', '%' . $searchValue . '%')
                                        ->orWhere('request_message', 'like', '%' . $searchValue . '%');
                                    })->count();
            $records = ApiResponse::orderBy('id', 'DESC')
                        ->where('response_type',$type)
                        ->where(function ($query) use ($searchValue) {
                            $query->where('api_type', 'like', '%' . $searchValue . '%')
                            ->orWhere('message', 'like', '%' . $searchValue . '%')
                            ->orWhere('request_message', 'like', '%' . $searchValue . '%');
                        })
                        ->skip($start)
                        ->take($rowperpage)
                        ->get();
        }
        else{

            $totalRecords = ApiResponse::select('count(*) as allcount')->count();
            $totalRecordswithFilter = ApiResponse::select('count(*) as allcount')
                                    ->where(function ($query) use ($searchValue) {
                                        $query->where('api_type', 'like', '%' . $searchValue . '%')
                                        ->orWhere('message', 'like', '%' . $searchValue . '%')
                                        ->orWhere('request_message', 'like', '%' . $searchValue . '%');
                                    })->count();



            $records = ApiResponse::orderBy('id', 'DESC')
                        ->where(function ($query) use ($searchValue) {
                            $query->where('api_type', 'like', '%' . $searchValue . '%')
                            ->orWhere('message', 'like', '%' . $searchValue . '%')
                            ->orWhere('request_message', 'like', '%' . $searchValue . '%');
                        })
                        ->skip($start)
                        ->take($rowperpage)
                        ->get();
        }


        $data_arr = array();
        foreach ($records as $value) {

            $data_arr[] = array(
                "id" => $value->id,
                "api_type" =>$value->api_type,
                "response_type" => $value->response_type,
                "message" => "<code>". $value->message ."</code>",
                "request_message" => $value->request_message,
                "report_id" => $value->report_id,
                "created_at" => $value->created_at,
            );
        }
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );
        echo json_encode($response);
        exit;
    }

}
