<?php

namespace App\Http\Controllers\Admin;

use App\Models\RolesNew;
use DB;
use Str;
use File;
use Hash;
use \Crypt;
use Carbon;
use Helpers;
use Validator;
use App\Models\Api;
use App\Models\Role;
use App\Models\User;
use App\Models\State;
use App\Models\Member;
use App\Models\Report;
use App\Models\Scheme;
use App\Models\Balance;
use App\Models\Company;
use App\Models\Profile;
use App\Models\Service;
use App\Models\District;
use App\Models\Zipcodes;
use App\Models\Sitesetting;
use App\Models\Servicegroup;
use Illuminate\Http\Request;
use App\Models\MerchantUsers;
use Dotenv\Store\File\Reader;
use App\Library\MemberLibrary;
use App\Library\PermissionLibrary;
use App\Http\Controllers\Controller;
use App\Models\Credentials;
use App\Models\MerchantTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;

class MemberController extends Controller
{


    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $companies = Helpers::company_id();
        $this->company_id = $companies->id;
        $sitesettings = Sitesetting::where('company_id', $this->company_id)->first();
        $this->brand_name = (empty($sitesettings)) ? '' : $sitesettings->brand_name;
        $this->backend_template_id = (empty($sitesettings)) ? 1 : $sitesettings->backend_template_id;

        $apis = Api::where('vender_id', 10)->first();
        $this->key = (empty($apis)) ? '' : 'Bearer ' . $apis->api_key;

        $this->min_amount = 10;
        $this->max_amount = 1000000;
    }

    function member_list($role_slug)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['member_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $roles = Role::where('role_slug', $role_slug)->first();
        if ($roles) {
            if ($roles->id > Auth::User()->role_id) {
                $role_title = $roles->role_title;
                $data = array(
                    'page_title' => $role_title,
                    'role_slug' => $role_slug,
                    'url' => url('admin/member-list-api') . '?' . 'role_slug=' . $role_slug . '&parent_id=0',
                );
                $states = State::where('status_id', 1)->get();
                if ($this->backend_template_id == 1) {
                    return view('admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.member_list', compact('states'))->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function bankit_user()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['member_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $data = array(
            'page_title' => 'Bankit Users',
            'url' => url('admin/bankit-user-api'),
        );
        return view('admin.bankit_user')->with($data);
    }

    public function bankit_user_api(Request $request)
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

        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        // Total records
        $totalRecords = User::select('count(*) as allcount')
            ->where('role_id', 8)
            ->where(function ($q) {
                $q->where('aeps_onboard_status', '=', 1);
                $q->orWhere('cms_onboard_status', '=', 1);
            })
            ->count();

        $totalRecordswithFilter = User::select('count(*) as allcount')
            ->where('role_id', 8)
            ->where(function ($q) {
                $q->where('aeps_onboard_status', '=', 1);
                $q->orWhere('cms_onboard_status', '=', 1);
            })
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records

        $records = User::query();
        if (in_array($columnName, ['joining_date', 'member_type'])) {
            $records = $records->orderBy('created_at', $columnSortOrder);
        } elseif ($columnName == "mobile_number") {
            $records = $records->orderBy('mobile', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
        $records = $records->where('role_id', '=', 8)
            ->where(function ($q) {
                $q->where('aeps_onboard_status', '=', 1);
                $q->orWhere('cms_onboard_status', '=', 1);
            })
            ->where(function ($query) use ($searchValue) {
                $query->where(DB::raw("CONCAT(name, ' ', middle_name,' ', last_name)"), 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->skip($start)
            ->take($rowperpage)
            ->get();


        $data_arr = array();

        foreach ($records as $value) {
            if ($value->aeps_onboard_status == 1) {
                $aeps_onboard_status = '<span class="badge badge-success">Completed</span>';
            } else {
                $aeps_onboard_status = '<span class="badge badge-danger">Pending</span>';
            }

            if ($value->cms_onboard_status == 1) {
                $cms_onboard_status = '<span class="badge badge-success">Completed</span>';
            } else {
                $cms_onboard_status = '<span class="badge badge-danger">Pending</span>';
            }
            $full_name = $value->name . " " . $value->middle_name . " " . $value->last_name;

            $data_arr[] = array(
                "id" => $value->id,
                "joining_date" => "$value->created_at",
                "name" => $full_name,
                "mobile_number" => $value->mobile,
                "member_type" => $value->role->role_title,
                "cms_agent_id" => $value->cms_agent_id,
                "aeps_onboard_status" => $aeps_onboard_status,
                "cms_onboard_status" => $cms_onboard_status,
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

    function iserveu_user()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['member_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $data = array(
            'page_title' => 'IServeU Onboard Users',
            'url' => url('admin/iserveu-user-api'),
        );
        return view('admin.iserveu_user')->with($data);
    }

    public function iserveu_user_api(Request $request)
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

        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
        // Total records
        $totalRecords = User::select('count(*) as allcount')
            ->where('role_id', 8)
            ->where(function ($q) {
                $q->where('iserveu_onboard_status', '=', 1);
            })
            ->count();

        $totalRecordswithFilter = User::select('count(*) as allcount')
            ->where('role_id', 8)
            ->where(function ($q) {
                $q->where('iserveu_onboard_status', '=', 1);
            })
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('cms_agent_id', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records

        $records = User::query();
        if (in_array($columnName, ['joining_date', 'member_type'])) {
            $records = $records->orderBy('created_at', $columnSortOrder);
        } elseif ($columnName == "mobile_number") {
            $records = $records->orderBy('mobile', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
        $records = $records->where('role_id', '=', 8)
            ->where(function ($q) {
                $q->where('iserveu_onboard_status', '=', 1);
            })
            ->where(function ($query) use ($searchValue) {
                $query->where(DB::raw("CONCAT(name, ' ', middle_name,' ', last_name)"), 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%')
                    ->orWhere('cms_agent_id', 'like', '%' . $searchValue . '%');
            })->skip($start)
            ->take($rowperpage)
            ->get();


        $data_arr = array();

        foreach ($records as $value) {
            if ($value->iserveu_onboard_status == 1) {
                $iserveu_onboard_status = '<span class="badge badge-success">Completed</span>';
            } else {
                $iserveu_onboard_status = '<span class="badge badge-danger">Pending</span>';
            }

            $data_arr[] = array(
                "id" => $value->id,
                "joining_date" => "$value->created_at",
                "fullname" => $value->fullname,
                "mobile_number" => $value->mobile,
                "member_type" => $value->role->role_title,
                "cms_agent_id" => $value->cms_agent_id,
                "iserveu_onboard_status" => $iserveu_onboard_status,
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

    public function update_iserveu_user_onboard(Request $request)
    {

        $rules = array('user_file' => 'required|mimes:csv');
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $file = fopen($request->user_file, "r");
        $i = 0;
        while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {
            if ($i != 0) {
                $agentid = $data[0];
                if ($agentid != "") {
                    User::where('cms_agent_id', $agentid)->update([
                        'iserveu_onboard_status' => 1
                    ]);
                }
            }
            $i++;
        }
        return response()->json(['status' => 'success', 'message' => "Agent has been onboarded successfully."]);
    }

    public function merchantUsers()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['member_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $data = array(
            'page_title' => 'Merchant Users',
            'url' => url('admin/merchant-user-api'),
        );
        return view('admin.merchant_user.index')->with($data);
    }

    public function merchantUsersApi(Request $request)
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

        // Total records
        $totalRecords = MerchantUsers::select('count(*) as allcount')
            ->count();

        $totalRecordswithFilter = MerchantUsers::select('count(*) as allcount')
            ->where(function ($query) use ($searchValue) {
                $query->where('first_name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile_number', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records

        $records = MerchantUsers::query();
        $records = $records->orderBy($columnName, $columnSortOrder);
        $records = $records->where(function ($query) use ($searchValue) {
            $query->where(DB::raw("CONCAT(first_name,' ', last_name)"), 'like', '%' . $searchValue . '%')
                ->orWhere('first_name', 'like', '%' . $searchValue . '%')
                ->orWhere('mobile_number', 'like', '%' . $searchValue . '%')
                ->orWhere('email', 'like', '%' . $searchValue . '%');
        })->skip($start)
            ->take($rowperpage)
            ->get();


        $data_arr = array();

        foreach ($records as $value) {
            $status = '<span class="badge badge-danger">In-active</span>';
            if ($value->status == 1) {
                $status = '<span class="badge badge-success">Active</span>';
            }

            $data_arr[] = array(
                "id" => '<button class="btn btn-danger btn-sm" onclick="view_members(' . $value->id . ')"> ' . $value->id . ' View</button>',
                "joining_date" => "$value->created_at",
                "fullname" => $value->first_name . ' ' . $value->last_name,
                "mobile_number" => $value->mobile_number,
                "email" => $value->email,
                "wallet" => $value->wallet,
                "status" => $status,
                "api_commission" => '<a href="' . Route('merchantSetupCommission', $value->id) . '" class="btn btn-success btn-sm"> Commission Set Up </button>'
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

    public function parent_down_users($role_slug, $parent_id)
    {
        $parent_id = Crypt::decrypt($parent_id);
        $roles = Role::where('role_slug', $role_slug)->first();
        if ($roles) {
            if ($roles->id > Auth::User()->role_id) {
                $role_title = $roles->role_title;
                $data = array(
                    'page_title' => $role_title,
                    'role_slug' => $role_slug,
                    'url' => url('admin/member-list-api') . '?' . 'role_slug=' . $role_slug . '&parent_id=' . $parent_id . '',
                );
                $states = State::where('status_id', 1)->get();
                if ($this->backend_template_id == 1) {
                    return view('admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.member_list', compact('states'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.member_list', compact('states'))->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function suspended_users()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['suspended_user_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 7) {
            $role_slug = '';
            $data = array(
                'page_title' => 'Suspended User',
                'url' => url('admin/suspended-user-api'),
                'role_slug' => $role_slug,
            );
            $states = State::where('status_id', 1)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.suspended_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.suspended_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.suspended_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.suspended_users', compact('states'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }


    function create_user($role_slug)
    {
        $roles = Role::where('role_slug', $role_slug)->first();
        if ($roles) {
            if ($roles->id > Auth::User()->role_id) {
                $roledetails = Role::where('id', $roles->id)->get();
                // $schemes = Scheme::where('user_id', Auth::id())->get();
                $schemes = Scheme::get();
                $state = State::where('status_id', 1)->orderByRaw(
                    "CASE WHEN name like 'Uttar Pradesh' THEN 1 ELSE 0 END DESC"
                )->get();
                $district = District::where('status_id', 1)->get();
                $companies = Company::where('status_id', 1)->where('parent_id', Auth::id())->get();
                $companies_id = array();
                foreach ($companies as $value) {
                    $companies_id[] = $value->id;
                }
                $my_company_id = array(Auth::User()->company_id);
                $company_id = array_merge($companies_id, $my_company_id);
                $company = Company::whereIn('id', $company_id)->where('status_id', 1)->get();
                $servicegroup_id = Servicegroup::where('status_id', 1)->get(['id']);
                $services = Service::whereIn('status_id', [1])->whereIn('servicegroup_id', $servicegroup_id)->get();
                $data = array('page_title' => $roles->role_title);
                if ($this->backend_template_id == 1) {
                    return view('admin.create_user', compact('roledetails', 'schemes', 'state', 'district', 'company', 'services', 'role_slug'))->with($data);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.create_user', compact('roledetails', 'schemes', 'state', 'district', 'company'))->with($data);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.create_user', compact('roledetails', 'schemes', 'state', 'district', 'company'))->with($data);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.create_user', compact('roledetails', 'schemes', 'state', 'district', 'company'))->with($data);
                } else {
                    return redirect()->back();
                }
            } else {
                return Redirect::back();
            }
        } else {
            return Redirect::back();
        }
    }

    function create_super_admin()
    {
        $role_slug = 'super-admin';
        $rolesRes = Role::where('role_slug', $role_slug)->first();
        if ($rolesRes) {
            $roleNew = new RolesNew();
            $roles = $roleNew->getRoleArray();
            $roledetails = Role::where('id', $rolesRes->id)->get();
            $data = array('page_title' => $rolesRes->role_title);
            if ($this->backend_template_id == 1) {
                return view('admin.create_admin_user', compact('roledetails', 'roles'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function store_members(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['create_member_permission'];
            if (!$myPermission == 1) {
                return response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        $rules = array(
            'name' => 'required',
            // 'middle_name' => 'required',
            'last_name' => 'required',
            'fullname' => 'required',
            'gender' => 'required',
            'dob' => 'required',
            'email' => 'required|email|unique:users',
            'mobile' => 'required|unique:users|digits:10',
            'role_id' => 'required',
            'shop_name' => 'required',
            'office_address' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state_id' => 'required|exists:states,id',
            // 'district_id' => 'required|exists:districts,id',
            'pin_code' => 'required|digits:6|integer',
            'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/|unique:members',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $name = $request->name;
        $last_name = $request->last_name;
        $fullname = $request->fullname ?? "";
        $email = $request->email;
        $mobile = $request->mobile;
        $shop_name = $request->shop_name;
        $office_address = $request->office_address;
        $address = $request->address;
        $city = $request->city;
        $pin_code = $request->pin_code;
        $state_id = $request->state_id;
        $district_id = $request->district_id;
        $lock_amount = (empty($request->lock_amount) ? 0 : $request->lock_amount);
        $gst_type = (empty($request->gst_type) ? 0 : $request->gst_type);
        $user_gst_type = (empty($request->user_gst_type) ? 0 : $request->user_gst_type);
        $pan_number = $request->pan_number;
        $gst_number = $request->gst_number;
        $active_services = $request->active_services;
        $middle_name = ""; // $request->middle_name;
        $gender = $request->gender;
        $dob = $request->dob;

        if ($request->role_id > Auth::user()->role_id) {
            $role_id = $request->role_id;
        } else {
            $role_id = Auth::user()->role_id + 1;
        }
        $password = mt_rand();

        $company_id = Auth::User()->company_id;
        if (Auth::User()->role_id == 1) {
            $scheme_id = $request->scheme_id;
        } else {
            $scheme_id = Auth::User()->scheme_id;
        }
        $parent_id = Auth::id();
        $library = new MemberLibrary();
        return $library->storeMember($name, $last_name, $email, $password, $mobile, $role_id, $parent_id, $scheme_id, $company_id, $gst_type, $user_gst_type, $lock_amount, $address, $city, $state_id, $district_id, $pin_code, $shop_name, $office_address, $pan_number, $gst_number, $active_services, $middle_name, $gender, $dob, $fullname, $request);
    }

    function store_admin_users(Request $request)
    {

        $rules = array(
            'name' => 'required',
            'last_name' => 'required',
            'password' => 'required|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'cpassword' => 'required|same:password',
            'email' => 'required|email|unique:users',
            'mobile' => 'required|unique:users|digits:10',
            'role' => 'required'
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $name = $request->name;
        $last_name = $request->last_name;
        $email = $request->email;
        $mobile = $request->mobile;
        $password = $request->password;
        $role = $request->role ?? NULL;
        DB::beginTransaction();
        try {
            $api_token = Str::random(60);
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');

            $user_id = User::insertGetId([
                'name' => $name,
                'middle_name' => "",
                'last_name' => $last_name,
                'email' => $email,
                'password' => bcrypt($password),
                'mobile' => $mobile,
                'role_id' => 1,
                'api_token' => $api_token,
                'created_at' => $ctime,
                'status_id' => 1,
                'company_id' => 1,
                'active' => 1,
                'mobile_verified' => 1,
                'gst_type' => 0,
                'user_gst_type' => 0,
            ]);
            $balance_id = Balance::insertGetId([
                'user_id' => $user_id,
                'user_balance' => 0,
                'sms_balance' => 0,
                'aeps_balance' => 0,
                'lien_amount' => 0,
                'balance_alert' => 0,
            ]);

            $profile_id = Profile::insertGetId([
                'user_id' => $user_id,
                'active_services' => ''
            ]);

            $member_id = Member::insertGetId([
                'user_id' => $user_id,
                'address' => 'Noida',
                'city' => 'noida',
                'state_id' => '5',
                'district_id' => '5',
                'pin_code' => '201301',
                'shop_name' => 'shop',
                'office_address' => 'noida',
                'pan_number' => '',
                'gst_number' => '',
            ]);

            $usern = User::find($user_id);
            $usern->balance_id = $balance_id;
            $usern->profile_id = $profile_id;
            $usern->member_id = $member_id;
            $usern->assign_role = $role;
            $usern->save();

            $usern->assignRole($role);

            DB::commit();
            return response()->json(['status' => 'success', 'message' => "Your profile has been created in our system."]);
        } catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
        }
    }

    function update_members(Request $request)
    {
        //        dd($request->all());
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['update_member_permission'];
            if (!$myPermission == 1) {
                return response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        $user_id = Crypt::decrypt($request->user_id);
        //$user_id = $request->user_id;
        $rules = array(
            'name' => 'required',
            // 'middle_name' => 'required',
            'last_name' => 'required',
            'fullname' => 'required',
            'gender' => 'required',
            'dob' => 'required',
            'email' => 'required|email|unique:users,email,' . $user_id,
            'email',
            'mobile' => 'required|digits:10|unique:users,mobile,' . $user_id,
            'mobile',
            'role_id' => 'required',
            'shop_name' => 'required',
            'office_address' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state_id' => 'required|exists:states,id',
            // 'district_id' => 'required|exists:districts,id',
            'pin_code' => 'required|digits:6|integer',
            'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }

        DB::beginTransaction();
        try {
            $shop_name = $request->shop_name;
            $office_address = $request->office_address;
            $lock_amount = $request->lock_amount;
            $status_id = $request->status_id;

            $address = $request->address;
            $city = $request->city;
            $state_id = $request->state_id;
            $district_id = $request->district_id;
            $pin_code = $request->pin_code;
            $pan_number = $request->pan_number;
            $gst_number = $request->gst_number;
            $cdm_card_number = $request->cdm_card_number;
            $embossed_card_number = $request->embossed_card_number;
            $middle_name = $request->middle_name ?? "";
            $fullname = $request->fullname ?? "";
            $gender = $request->gender;
            $dob = $request->dob;
            $userdetails = User::where('id', $user_id)->first();
            if ($request->role_id > Auth::user()->role_id) {
                $role_id = $request->role_id;
            } else {
                $role_id = $userdetails->role->id;
            }
            if (isset($cdm_card_number) && !empty($cdm_card_number)) {
                $userCheck = User::whereNot('id', $user_id)->where('cdm_card_number', $cdm_card_number)->first();
                if (!empty($userCheck)) {
                    return response()->json(['status' => 'failure', 'message' => 'CDM already exist in System']);
                }
            }
            if (isset($embossed_card_number) && !empty($embossed_card_number)) {
                $userCheckEm = User::whereNot('id', $user_id)->where('embossed_card_number', $embossed_card_number)->first();
                if (!empty($userCheckEm)) {
                    return response()->json(['status' => 'failure', 'message' => 'Embossed already exist in System']);
                }
            }
            $scheme_id = (Auth::User()->role_id == 1) ? $request->scheme_id : $userdetails->scheme_id;
            $mobile = (Auth::User()->role_id == 1) ? $request->mobile : $userdetails->mobile;
            $parent_id = (Auth::User()->role_id == 1) ? $request->parent_id : $userdetails->parent_id;
            $name = (Auth::User()->role_id == 1) ? $request->name : $userdetails->name;
            $last_name = (Auth::User()->role_id == 1) ? $request->last_name : $userdetails->last_name;
            $email = (Auth::User()->role_id == 1) ? $request->email : $userdetails->email;
            $gst_type = (Auth::User()->role_id == 1) ? $request->gst_type : $userdetails->gst_type;
            $user_gst_type = (Auth::User()->role_id == 1) ? $request->user_gst_type : $userdetails->user_gst_type;
            $day_book = (Auth::User()->role_id == 1) ? $request->day_book : $userdetails->profile->day_book;
            $monthly_statement = (Auth::User()->role_id == 1) ? $request->monthly_statement : $userdetails->profile->monthly_statement;
            $active_services = (Auth::User()->role_id == 1) ? $request->active_services : $userdetails->profile->active_services;
            $server_ip = ($request->server_ip) ? $request->server_ip : '';
            if ($server_ip) {
                $server_ip = explode(",", $server_ip);
                $server_ip = json_encode($server_ip);
            }

            $is_ip_whiltelist = $request->is_ip_whiltelist;
            $callback_url = $request->callback_url ?? NULL;
            $longitude = $request->longitude ?? NULL;
            $latitude = $request->latitude ?? NULL;
            $credentials_id = $request->credentials_id ?? NULL;
            $type_rs_per = $request->type_rs_per ?? NULL;
            $charges = $request->charges ?? NULL;

            User::where('id', $user_id)->update([
                'name' => $name,
                'middle_name' => $middle_name,
                'last_name' => $last_name,
                'fullname' => $fullname,
                'email' => $email,
                'gender' => $gender,
                'dob' => $dob,
                'mobile' => $mobile,
                'role_id' => $role_id,
                'scheme_id' => $scheme_id,
                'lock_amount' => $lock_amount,
                'parent_id' => $parent_id,
                'gst_type' => 0,
                'user_gst_type' => 0,
                'cdm_card_number' => $cdm_card_number,
                'embossed_card_number' => $embossed_card_number,
                'status_id' => (empty($status_id) ? 0 : $status_id),
                'login_restrictions' => (Auth::User()->role_id == 1 && Auth::User()->company->login_restrictions == 1) ? $request->login_restrictions : $userdetails->login_restrictions,
                //'latitude' => (Auth::User()->role_id == 1 && Auth::User()->company->login_restrictions == 1) ? $request->latitude : $userdetails->latitude,
                //'longitude' => (Auth::User()->role_id == 1 && Auth::User()->company->login_restrictions == 1) ? $request->longitude : $userdetails->longitude,
                'cms_agent_id' => $request->cms_agent_id,
                'cms_onboard_status' => $request->cms_onboard_status,
                'aeps_onboard_status' => $request->aeps_onboard_status,
                'iserveu_onboard_status' => $request->iserveu_onboard_status,
                'is_ip_whiltelist' => $is_ip_whiltelist,
                'callback_url' => $callback_url,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'server_ip' => $server_ip,
                'credentials_id' => $credentials_id,
                'type_rs_per' => $type_rs_per,
                'charges' => $charges
            ]);

            Member::where('user_id', $user_id)->update([
                'address' => $address,
                'city' => $city,
                'state_id' => $state_id,
                'district_id' => $district_id,
                'pin_code' => $pin_code,
                'shop_name' => $shop_name,
                'office_address' => $office_address,
                'pan_number' => $pan_number,
                'gst_number' => $gst_number,
            ]);

            Profile::where('user_id', $user_id)->update([
                'day_book' => $day_book,
                'monthly_statement' => $monthly_statement,
                'active_services' => $active_services,
            ]);
            DB::commit();
            return Response()->json(['status' => 'success', 'message' => 'user successfully updated']);
        } catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
        }
    }


    function view_members_details(Request $request)
    {
        $id = $request->id;
        $users = User::where('id', $id)->first();
        if ($users) {
            if (Cache::has('is_online' . $users->id)) {
                $is_online = 'online';
            } else {
                $is_online = Carbon\Carbon::parse($users->last_seen)->diffForHumans();
            }

            if (Auth::User()->role_id == 1) {
                $login_otp = $users->login_otp;
                $pan_username = $users->pan_username;
                $pan_password = $users->pan_password;
            } else {
                $login_otp = 000000;
                $pan_username = "";
                $pan_password = "";
            }
            $states = '';
            $districts = '';
            $editUserUrl = url('admin/view-update-users') . '/' . Crypt::encrypt($users->id);
            if ($users->role_id == 1) {
                $editUserUrl = url('admin/view-update-admins') . '/' . Crypt::encrypt($users->id);
            } else {
                $states = State::find($users->member->state_id);
                $districts = District::find($users->member->district_id);
            }
            $details = array(
                'user_id' => $users->id,
                'role_id' => $users->role_id,
                'update_anchor_url' => $editUserUrl,
                'kyc_anchor_url' => url('admin/view-user-kyc') . '/' . Crypt::encrypt($users->id),
                'reset_password_anchor' => 'reset_password(' . $users->id . ')',
                't_reset_password_anchor' => 't_reset_password(' . $users->id . ')',
                'login_anchor' => 'login_user(' . $users->id . ')',
                'name' => $users->name,
                'last_name' => $users->last_name,
                'fullname' => $users->fullname,
                'mobile' => $users->mobile,
                'email' => $users->email,
                'lock_amount' => $users->lock_amount,
                'shop_name' => (isset($users->member->shop_name)) ? $users->member->shop_name : '',
                'address' => (isset($users->member->address)) ? $users->member->address : '',
                'city' => (isset($users->member->city)) ? $users->member->city : '',
                'state_name' => (empty($states) ? '' : $states->name),
                'district_name' => (empty($districts) ? '' : $districts->district_name),
                'pin_code' => (isset($users->member->pin_code)) ? $users->member->pin_code : '',
                'office_address' => (isset($users->member->office_address)) ? $users->member->office_address : '',
                'pan_number' => (isset($users->member->pan_number)) ? $users->member->pan_number : '',
                'gst_number' => (isset($users->member->gst_number)) ? $users->member->gst_number : '',
                'recharge' => (isset($users->profile->recharge)) ? $users->profile->recharge : '',
                'money' => (isset($users->profile->money)) ? $users->profile->money : '',
                'aeps' => (isset($users->profile->aeps)) ? $users->profile->aeps : '',
                'payout' => (isset($users->profile->payout)) ? $users->profile->payout : '',
                'pancard' => (isset($users->profile->pancard)) ? $users->profile->pancard : '',
                'ecommerce' => (isset($users->profile->ecommerce)) ? $users->profile->ecommerce : '',
                'is_online' => $is_online,
                'reason' => $users->reason,
                'user_balance' => isset($users->balance->user_balance) ? number_format($users->balance->user_balance, 2) : '',
                'aeps_balance' => isset($users->balance->aeps_balance) ? number_format($users->balance->aeps_balance, 2) : '',
                'login_otp' => $login_otp,
                'pan_username' => $pan_username,
                'pan_password' => $pan_password,

                'is_ip_whiltelist' => $users->is_ip_whiltelist,
                'server_ip' => $users->server_ip,
                'callback_url' => $users->callback_url,
                'api_key' => $users->api_key,
                'secret_key' => $users->secrete_key,

            );
            return Response()->json([
                'status' => 'success',
                'details' => $details,
            ]);
        } else {
            return Response()->json([
                'status' => 'failure',
                'message' => 'User not found'
            ]);
        }
    }

    function view_update_users($encrypt_id)
    {

        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['update_member_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $user_id = Crypt::decrypt($encrypt_id);
        $userdetails = User::where('id', $user_id)->first();
        if ($userdetails) {
            if (Auth::User()->role_id <= 2 || $userdetails->parent_id == Auth::id()) {

                $details = array(
                    'user_id' => $encrypt_id,
                    'name' => $userdetails->name,
                    'middle_name' => $userdetails->middle_name,
                    'last_name' => $userdetails->last_name,
                    'fullname' => $userdetails->fullname,
                    'email' => $userdetails->email,
                    'mobile' => $userdetails->mobile,
                    'gender' => $userdetails->gender,
                    'dob' => $userdetails->dob,
                    'role_id' => $userdetails->role_id,
                    'scheme_id' => $userdetails->scheme_id,
                    'lock_amount' => $userdetails->lock_amount,
                    'company_id' => $userdetails->company_id,
                    'parent_id' => $userdetails->parent_id,
                    'gst_type' => $userdetails->gst_type,
                    'user_gst_type' => $userdetails->user_gst_type,
                    'pan_username' => $userdetails->pan_username,
                    'pan_password' => $userdetails->pan_password,
                    'status_id' => $userdetails->status_id,
                    'login_restrictions' => $userdetails->login_restrictions,
                    'latitude' => $userdetails->latitude,
                    'longitude' => $userdetails->longitude,
                    'cms_agent_id' => $userdetails->cms_agent_id,

                    'member_id' => $userdetails->member->id,
                    'address' => $userdetails->member->address,
                    'city' => $userdetails->member->city,
                    'state_id' => $userdetails->member->state_id,
                    'district_id' => $userdetails->member->district_id,
                    'pin_code' => $userdetails->member->pin_code,
                    'shop_name' => $userdetails->member->shop_name,
                    'office_address' => $userdetails->member->office_address,
                    'pan_number' => $userdetails->member->pan_number,
                    'gst_number' => $userdetails->member->gst_number,


                    'profile_id' => $userdetails->profile->id,
                    'seller' => $userdetails->profile->seller,
                    'day_book' => $userdetails->profile->day_book,
                    'monthly_statement' => $userdetails->profile->monthly_statement,
                    'active_services' => $userdetails->profile->active_services,
                    'cdm_card_number' => $userdetails->cdm_card_number,
                    'embossed_card_number' => $userdetails->embossed_card_number,
                    'cms_onboard_status' => $userdetails->cms_onboard_status,
                    'aeps_onboard_status' => $userdetails->aeps_onboard_status,
                    'iserveu_onboard_status' => $userdetails->iserveu_onboard_status,

                    'is_ip_whiltelist' => $userdetails->is_ip_whiltelist,
                    'server_ip' => $userdetails->server_ip,
                    'callback_url' => $userdetails->callback_url,
                    'credentials_id' => $userdetails->credentials_id,
                    'type_rs_per' => $userdetails->type_rs_per,
                    'charges' => $userdetails->charges
                );

                if (Auth::User()->role_id == 1) {
                    $roledetails = Role::where('id', '>', Auth::user()->role_id)->where('status_id', 1)->get();
                } else {
                    $roledetails = Role::where('id', '>', Auth::user()->role_id)->whereNotIn('id', [9, 10])->where('status_id', 1)->get();
                }
                // $schemes = Scheme::where('user_id', Auth::id())->get();
                $schemes = Scheme::get();
                $state = State::where('status_id', 1)->orderByRaw(
                    "CASE WHEN name like 'Uttar Pradesh' THEN 1 ELSE 0 END DESC"
                )->get();
                $state_id = State::where('id', $userdetails->member->state_id)->value('name');
                $cities = Zipcodes::select('city')->where('state', $state_id)->groupBy('city')->orderBy('city')->get();
                $permanentdistrict = District::where('status_id', 1)->where('state_id', $userdetails->member->state_id)->get();
                $companies = Company::where('status_id', 1)->where('parent_id', Auth::id())->get();
                $companies_id = array();
                foreach ($companies as $value) {
                    $companies_id[] = $value->id;
                }
                $my_company_id = array(Auth::User()->company_id);
                $company_id = array_merge($companies_id, $my_company_id);
                $company = Company::whereIn('id', $company_id)->where('status_id', 1)->get();
                $parents = User::whereIn('role_id', [1, 2, 3, 4, 5, 6, 7, 8])->where('company_id', Auth::user()->company_id)->get();
                $servicegroup_id = Servicegroup::where('status_id', 1)->get(['id']);
                $services = Service::whereIn('status_id', [1])->whereIn('servicegroup_id', $servicegroup_id)->get();
                $data = array('page_title' => 'Update User : ' . $userdetails->name . ' ' . $userdetails->last_name . '');
                $credentials = Credentials::get();
                if ($this->backend_template_id == 1) {
                    return view('admin.view_update_users', compact('roledetails', 'schemes', 'state', 'permanentdistrict', 'company', 'parents', 'services', 'cities', 'credentials'))->with($data)->with($details);
                } elseif ($this->backend_template_id == 2) {
                    return view('themes2.admin.view_update_users', compact('roledetails', 'schemes', 'state', 'permanentdistrict', 'company', 'parents'))->with($data)->with($details);
                } elseif ($this->backend_template_id == 3) {
                    return view('themes3.admin.view_update_users', compact('roledetails', 'schemes', 'state', 'permanentdistrict', 'company', 'parents'))->with($data)->with($details);
                } elseif ($this->backend_template_id == 4) {
                    return view('themes4.admin.view_update_users', compact('roledetails', 'schemes', 'state', 'permanentdistrict', 'company', 'parents'))->with($data)->with($details);
                } else {
                    return redirect()->back();
                }
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function view_user_kyc($encrypt_id)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['viewUser_kyc_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $user_id = Crypt::decrypt($encrypt_id);
        $userdetails = User::where('id', $user_id)->first();

        if ($userdetails->member->shop_photo) {
            $shop_photo = $userdetails->member->shop_photo;
        } else {
            $shop_photo = null;
        }
        if ($userdetails->member->gst_regisration_photo) {
            $gst_regisration_photo = $userdetails->member->gst_regisration_photo;
        } else {
            $gst_regisration_photo = null;
        }

        if ($userdetails->member->pancard_photo) {
            $pancard_photo = $userdetails->member->pancard_photo;
        } else {
            $pancard_photo = null;
        }

        if ($userdetails->member->cancel_cheque) {
            $cancel_cheque = $userdetails->member->cancel_cheque;
        } else {
            $cancel_cheque = null;
        }

        if ($userdetails->member->address_proof) {
            $address_proof = $userdetails->member->address_proof;
        } else {
            $address_proof = null;
        }

        if ($userdetails->member->profile_photo) {
            $profile_photo = $userdetails->member->profile_photo;
        } else {
            $profile_photo = null;
        }

        if ($userdetails->member->aadhar_front) {
            $aadhar_front = $userdetails->member->aadhar_front;
        } else {
            $aadhar_front = null;
        }

        if ($userdetails->member->aadhar_back) {
            $aadhar_back = $userdetails->member->aadhar_back;
        } else {
            $aadhar_back = null;
        }
        if ($userdetails->member->agreement_form) {
            $agreement_form = $userdetails->member->agreement_form;
        } else {
            $agreement_form = null;
        }
        $details = array(
            'shop_photo' => $shop_photo,
            'gst_regisration_photo' => $gst_regisration_photo,
            'pancard_photo' => $pancard_photo,
            'cancel_cheque' => $cancel_cheque,
            'aadhar_front' => $aadhar_front,
            'aadhar_back' => $aadhar_back,
            'agreement_form' => $agreement_form,
            'profile_photo' => $profile_photo,
            'name' => $userdetails->name . ' ' . $userdetails->last_name,
            'role_type' => $userdetails->role->role_title,
            'website_name' => $userdetails->company->company_name,
            'email' => $userdetails->email,
            'mobile' => $userdetails->mobile,
            'joining_date' => "$userdetails->created_at",
            'edit_url' => url('admin/view-update-users') . '/' . Crypt::encrypt($encrypt_id),
            'kyc_status' => $userdetails->member->kyc_status,
            'user_id' => $userdetails->id,
            'kyc_remark' => $userdetails->member->kyc_remark,


        );
        $page_title = $userdetails->name . ' Kyc';
        $data = array('page_title' => $page_title);
        if ($this->backend_template_id == 1) {
            return view('admin.view_user_kyc')->with($data)->with($details);
        } elseif ($this->backend_template_id == 2) {
            return view('themes2.admin.view_user_kyc')->with($data)->with($details);
        } elseif ($this->backend_template_id == 3) {
            return view('themes3.admin.view_user_kyc')->with($data)->with($details);
        } elseif ($this->backend_template_id == 4) {
            return view('themes4.admin.view_user_kyc')->with($data)->with($details);
        } else {
            return redirect()->back();
        }
    }

    function update_kyc(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['update_kyc_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'user_id' => 'required',
                'kyc_remark' => 'required',
                'kyc_status' => 'required',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $user_id = $request->user_id;
            $kyc_remark = $request->kyc_remark;
            $kyc_status = $request->kyc_status;
            Member::where('user_id', $user_id)->update([
                'kyc_status' => $kyc_status,
                'kyc_remark' => $kyc_remark,
            ]);
            User::where('id', $user_id)->update(['active' => 1]);
            return Response()->json(['status' => 'success', 'message' => 'kyc update successfull']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'sorry not permission']);
        }
    }


    function get_distric_by_state(Request $request)
    {
        if ($request->state_id) {
            $state_id = $request->state_id;
            $districts = District::where('state_id', $state_id)->get();
            $response = array();
            foreach ($districts as $value) {
                $product = array();
                $product["district_id"] = $value->id;
                $product["district_name"] = $value->district_name;
                array_push($response, $product);
            }
            return Response()->json(['status' => 'success', 'districts' => $response]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'select state']);
        }
    }

    function get_city_by_state(Request $request)
    {
        if ($request->state_id) {
            $state_id = $request->state_id;
            $response = Zipcodes::select('city')->where('state', $state_id)->groupBy('city')->orderBy('city')->get();
            return Response()->json(['status' => 'success', 'cities' => $response]);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'select state']);
        }
    }


    function reset_password(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['company_settings_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'admin_password' => 'required',
                'password' => 'required|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $user_id = $request->user_id;
            $admin_password = $request->admin_password;
            $password = $request->password;
            $userdetail = User::find(Auth::id());
            $current_password = $userdetail->password;
            if (Hash::check($admin_password, $current_password)) {
                User::where('id', $user_id)->update(['password' => Hash::make($password)]);
                $new_session_id = \Session::getId(); //get new session_id after user sign in
                $userDetails = User::find($user_id);
                if ($userDetails->session_id != '') {
                    $last_session = \Session::getHandler()->read($userDetails->session_id);
                    if ($last_session) {
                        if (\Session::getHandler()->destroy($userDetails->session_id)) {
                        }
                    }
                }
                User::where('id', $user_id)->update(['session_id' => $new_session_id]);
                $customerdetails = User::where('id', $user_id)->first();
                // $message = "Dear $customerdetails->name Your password reset by $userdetail->name now your new password is : $password $this->brand_name";
                $message = "Please click on the link provided to reset your password and regain access to your trustxpay account. Do not share this link with anyone. For more info: trustxpay.org PAYOBL";
                $template_id = 3;

                // $library = new SmsLibrary();
                // $library->send_sms($customerdetails->mobile, $message, $template_id);
                return Response()->json(['status' => 'success', 'message' => 'Password successfully reset']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Your login password is wrong']);
            }
        }
    }

    function add_merchant_balance(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'refno' => 'required',
                'amount' => 'required|numeric|between:' . $this->min_amount . ',' . $this->max_amount . '',
                'password' => 'required',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }

            $refno = $request->refno;
            $amount = $request->amount;
            $password = $request->password;
            $user_id = Auth::id();
            $userdetail = User::find($user_id);
            $current_password = $userdetail->password;
            if (Hash::check($password, $current_password)) {
                $merchantdetails = MerchantUsers::find($request->id);
                $cdate = date('Y-m-d', time());
                $finalamount = $merchantdetails->wallet + $amount;
                MerchantUsers::where('id', $request->id)->update(['wallet' => $finalamount]);
                MerchantTransactions::insertGetId([
                    'merchant_id' => $request->id,
                    'provider_id' => "326",
                    'account_number' => $merchantdetails->mobile_number,
                    'created_at' => $cdate,
                    'updated_at' => $cdate,
                    'transaction_id' => $refno,
                    'opening_balance' => $merchantdetails->wallet,
                    'amount' => $amount,
                    'total_balance' => $finalamount,
                    'description' => "Purchase Balance  $amount",
                    'status_id' => 6,
                ]);
                return Response()->json(['status' => 'success', 'message' => 'Balance added successfully']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Password is wrong']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permiision']);
        }
    }

    function transaction_reset_password(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['company_settings_permission'];
            if (!$myPermission == 1) {
                return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        if (Auth::User()->role_id <= 2) {
            $rules = array(
                'admin_password' => 'required',
                'password' => 'required|string|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',

            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
            }
            $user_id = $request->user_id;
            $admin_password = $request->admin_password;
            $password = $request->password;
            $userdetail = User::find(Auth::id());
            $current_password = $userdetail->password;
            if (Hash::check($admin_password, $current_password)) {
                User::where('id', $user_id)->update(['transaction_password' => Hash::make($password)]);
                return Response()->json(['status' => 'success', 'message' => 'Transaction password successfully reset']);
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'Your login password is wrong']);
            }
        }
    }

    function refresh_scheme(Request $request)
    {
        $schemes = Scheme::where('user_id', Auth::id())->orderBy('id', 'DESC')->get();
        $response = array();
        foreach ($schemes as $value) {
            $product = array();
            $product["scheme_id"] = $value->id;
            $product["scheme_name"] = $value->scheme_name;
            array_push($response, $product);
        }
        return Response()->json(['status' => 'success', 'scheme' => $response]);
    }


    function export_member(Request $request)
    {
        $users = User::all();
        $arr = array();
        foreach ($users as $value) {
            $data = array(
                $value->id,
            );
            array_push($arr, $data);
        }

        $delimiter = ",";
        $filename = 'download/member_export_' . mt_rand(10, 99) . '.csv';
        $fp = fopen($filename, 'w+');
        $col = ['User Id'];
        fputcsv($fp, $col, $delimiter);
        foreach ($arr as $line) {
            fputcsv($fp, $line, $delimiter);
        }
        fclose($fp);
        $url = url('') . '/' . $filename;
        echo "<a href='$url' download>download</a>";
        exit();
    }


    function not_working_users()
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['not_working_users_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->role_id <= 7) {
            $data = array('page_title' => 'Not Working Users',);
            $states = State::where('status_id', 1)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.not_working_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.not_working_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.not_working_users', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.not_working_users', compact('states'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function not_working_users_api(Request $request)
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
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        $datefrom = Carbon\Carbon::now()->startOfMonth()->subMonth()->toDateString();
        $dateto = date('Y-m-d', time());
        $report = Report::whereDate('created_at', '>=', $datefrom)->whereDate('created_at', '<=', $dateto)->distinct()->select(['user_id'])->get();
        $user_id = array();
        foreach ($report as $member) {
            $user_id[] = $member->user_id;
        }

        // Total records
        $totalRecords = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->whereNotIn('id', $user_id)
            ->count();

        $totalRecordswithFilter = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->whereNotIn('id', $user_id)
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records

        $records = User::query();
        if (in_array($columnName, ['username', 'balance', 'member_type', 'last_date'])) {
            $records = $records->orderBy('created_at', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
        $records = $records->whereIn('id', $my_down_member)
            ->whereNotIn('id', $user_id)
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = array();
        foreach ($records as $value) {
            $report = Report::where('user_id', $value->id)->orderBy('id', 'DESC')->first();
            if ($report) {
                $last_date = $report->created_at->format('Y-m-d h:m:s');
            } else {
                $last_date = "Not working til now";
            }
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->id);
            $data_arr[] = array(
                "id" => '<button class="btn btn-danger btn-sm" onclick="view_members(' . $value->id . ')"> ' . $value->id . ' View</button>',
                "username" => $value->name . ' ' . $value->last_name,
                "mobile" => $value->mobile,
                "balance" => number_format($value->balance->user_balance, 2),
                "member_type" => $value->role->role_title,
                "last_date" => "$last_date",
                "statement" => '<a href="' . $statement_url . '" class="btn btn-primary btn-sm">Statement</a>',

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


    function create_pancard_id(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $user_id = Crypt::decrypt($request->user_id);
            $userdetails = User::where('id', $user_id)->first();
            $sender_id = Auth::User()->company->sender_id;
            if ($userdetails) {
                $url = "";
                $api_request_parameters = array();
                $method = 'GET';
                $header = ["Accept:application/json", "Authorization:" . $this->key];
                $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                $res = json_decode($response);
                if ($res->message == 'Success') {
                    User::where('id', $userdetails->id)->update(['pan_username' => $res->username, 'pan_password' => $res->password]);
                    return Response(['status' => 'success', 'message' => 'Successful..!']);
                } else {
                    $url = "";
                    $api_request_parameters = array('mobile_number' => $userdetails->mobile, 'username' => $sender_id . '' . $userdetails->id);
                    $method = 'POST';
                    $header = ["Accept:application/json", "Authorization:" . $this->key];
                    $response = Helpers::pay_curl_post($url, $header, $api_request_parameters, $method);
                    $res = json_decode($response);
                    if ($res->status == 0) {
                        User::where('id', $userdetails->id)->update(['pan_username' => $res->username, 'pan_password' => $res->password]);
                        return Response(['status' => 'success', 'message' => 'Successful..!']);
                    } else {
                        return Response()->json(['status' => 'failure', 'message' => $res->message]);
                    }
                }
            } else {
                return Response()->json(['status' => 'failure', 'message' => 'record not found']);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_dropdown_package(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'scheme_id' => 'required|exists:schemes,id',
                'user_id' => 'required|exists:users,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $scheme_id = $request->scheme_id;
            $user_id = $request->user_id;
            User::where('id', $user_id)->update(['scheme_id' => $scheme_id]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function update_dropdown_parent(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            $rules = array(
                'parent_id' => 'required|exists:users,id',
                'user_id' => 'required|exists:users,id',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return Response()->json(['status' => 'failure', 'message' => $validator->messages()->first()]);
            }
            $parent_id = $request->parent_id;
            $user_id = $request->user_id;
            User::where('id', $user_id)->update(['parent_id' => $parent_id]);
            return Response()->json(['status' => 'success', 'message' => 'Successful..!']);
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function all_user_list($slug = null)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['member_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        $optional1 = (empty($slug)) ? '' : Crypt::decrypt($slug);
        $type = (empty($slug)) ? 0 : 1;
        $role_slug = '';
        $data = array(
            'page_title' => 'User By Package',
            'role_slug' => $role_slug,
            'url' => url('admin/all-user-list-api') . '?' . 'optional1=' . $optional1 . '&type=' . $type,
        );
        $states = State::where('status_id', 1)->get();
        if ($this->backend_template_id == 1) {
            return view('admin.all_user_list', compact('states'))->with($data);
        } elseif ($this->backend_template_id == 2) {
            return view('themes2.admin.all_user_list', compact('states'))->with($data);
        } elseif ($this->backend_template_id == 3) {
            return view('themes3.admin.all_user_list', compact('states'))->with($data);
        } elseif ($this->backend_template_id == 4) {
            return view('themes4.admin.all_user_list', compact('states'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function all_user_list_api(Request $request)
    {
        $optional1 = $request->optional1;
        $type = $request->get('amp;type');
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
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        // Total records
        if ($type == 1) {
            $totalRecords = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('scheme_id', $optional1)
                ->count();

            $totalRecordswithFilter = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('scheme_id', $optional1)
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->count();
            // Fetch records

            $records = User::query();
            if ($columnName == 'joining_date') {
                $records = $records->orderBy('created_at', $columnSortOrder);
            } elseif ($columnName == 'mobile_number') {
                $records = $records->orderBy('mobile', $columnSortOrder);
            } elseif ($columnName == 'member_type') {
                $records = $records->orderBy('role_id', $columnSortOrder);
            } elseif ($columnName == 'status') {
                $records = $records->orderBy('status_id', $columnSortOrder);
            } elseif ($columnName == 'user_balance' || $columnName == 'parent_name' || $columnName == 'package_name' || $columnName == 'is_online') {
                $records = $records->orderBy('id', $columnSortOrder);
            } else {
                $records = $records->orderBy($columnName, $columnSortOrder);
            }
            $records = $records->whereIn('id', $my_down_member)
                ->whereIn('id', $my_down_member)
                ->where('scheme_id', $optional1)
                ->orderBy('id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->skip($start)
                ->take($rowperpage)
                ->get();
        } else {
            $totalRecords = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->whereNotIn('id', [Auth::id()])
                ->count();
            $totalRecordswithFilter = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->whereNotIn('id', [Auth::id()])
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->count();
            // Fetch records
            $records = User::query();
            if ($columnName == 'joining_date') {
                $records = $records->orderBy('created_at', $columnSortOrder);
            } elseif ($columnName == 'mobile_number') {
                $records = $records->orderBy('mobile', $columnSortOrder);
            } elseif ($columnName == 'member_type') {
                $records = $records->orderBy('role_id', $columnSortOrder);
            } elseif ($columnName == 'status') {
                $records = $records->orderBy('status_id', $columnSortOrder);
            } elseif ($columnName == 'user_balance' || $columnName == 'parent_name' || $columnName == 'package_name' || $columnName == 'is_online') {
                $records = $records->orderBy('id', $columnSortOrder);
            } else {
                $records = $records->orderBy($columnName, $columnSortOrder);
            }
            $records = $records->whereIn('id', $my_down_member)
                ->whereNotIn('id', [Auth::id()])
                ->orderBy('id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->skip($start)
                ->take($rowperpage)
                ->get();
        }
        $role_slug = "";
        Self::userListCommon($records, $draw, $totalRecords, $totalRecordswithFilter, $role_slug);
    }

    function member_list_api(Request $request)
    {
        $role_slug = $request->role_slug;
        $parent_id = $request->get('amp;parent_id');
        $roles = Role::where('role_slug', $role_slug)->first();
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
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        // Total records
        if ($parent_id == 0) {
            $totalRecords = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('role_id', $roles->id)
                ->count();
            $totalRecordswithFilter = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('role_id', $roles->id)
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->count();
            // Fetch records
            $records = User::query();
            if (in_array($columnName, ['joining_date', 'mobile_number', 'parent_name', 'package_name', 'status', 'is_online', 'member_type'])) {
                $records = $records->orderBy('created_at', $columnSortOrder);
            } elseif (in_array($columnName, ['user_balance', 'aeps_balance'])) {
                $records = $records->join('balances', 'users.id', 'balances.user_id')
                    ->orderBy('balances.' . $columnName, $columnSortOrder);
            } else {
                $records = $records->orderBy($columnName, $columnSortOrder);
            }
            $records = $records->whereIn('users.id', $my_down_member)
                ->where('role_id', $roles->id)
                ->orderBy('users.id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->skip($start)
                ->take($rowperpage)
                ->get();
        } else {
            $totalRecords = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('parent_id', $parent_id)
                ->count();
            $totalRecordswithFilter = User::select('count(*) as allcount')
                ->whereIn('id', $my_down_member)
                ->where('parent_id', $parent_id)
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->count();
            // Fetch records
            $records = User::query();
            if (in_array($columnName, ['joining_date', 'mobile_number', 'parent_name', 'package_name', 'status', 'is_online', 'member_type'])) {
                $records = $records->orderBy('created_at', $columnSortOrder);
            } elseif (in_array($columnName, ['user_balance', 'aeps_balance'])) {
                $records = $records->join('balances', 'users.id', 'balances.user_id')
                    ->orderBy('balances.' . $columnName, $columnSortOrder);
            } else {
                $records = $records->orderBy($columnName, $columnSortOrder);
            }
            $records = $records->whereIn('users.id', $my_down_member)
                ->where('parent_id', $parent_id)
                ->orderBy('users.id', 'DESC')
                ->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                        ->orWhere('email', 'like', '%' . $searchValue . '%');
                })->skip($start)
                ->take($rowperpage)
                ->get();
        }
        Self::userListCommon($records, $draw, $totalRecords, $totalRecordswithFilter, $role_slug);
    }


    function suspended_user_api(Request $request)
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
        $role_id = Auth::User()->role_id;
        $company_id = Auth::User()->company_id;
        $user_id = Auth::id();
        $library = new MemberLibrary();
        $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);

        // Total records
        $totalRecords = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->whereNotIn('active', [1])
            ->count();

        $totalRecordswithFilter = User::select('count(*) as allcount')
            ->whereIn('id', $my_down_member)
            ->whereNotIn('active', [1])
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->count();

        // Fetch records

        $records = User::query();
        if (in_array($columnName, ['joining_date', 'mobile_number', 'user_balance', 'parent_name', 'package_name', 'status', 'is_online', 'member_type'])) {
            $records = $records->orderBy('created_at', $columnSortOrder);
        } else {
            $records = $records->orderBy($columnName, $columnSortOrder);
        }
        $records = $records->whereIn('id', $my_down_member)
            ->whereNotIn('active', [1])
            ->where(function ($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('mobile', 'like', '%' . $searchValue . '%')
                    ->orWhere('email', 'like', '%' . $searchValue . '%');
            })->skip($start)
            ->take($rowperpage)
            ->get();
        $role_slug = "";
        Self::userListCommon($records, $draw, $totalRecords, $totalRecordswithFilter, $role_slug);
    }

    function userListCommon($records, $draw, $totalRecords, $totalRecordswithFilter, $role_slug)
    {
        $data_arr = array();
        foreach ($records as $value) {
            if ($value->status_id == 1) {
                $status = '<span class="badge badge-success">Enabled</span>';
            } else {
                $status = '<span class="badge badge-danger">Disabled</span>';
            }
            if ($value->mobile_verified == 1) {
                $mobile = '<span>' . $value->mobile . '</span>';
            } else {
                $mobile = '<span style="color:red;" alt="mobile number not verified">' . $value->mobile . '</span>';
            }
            if (Cache::has('is_online' . $value->id)) {
                $is_online = '<span class="badge badge-success">Online</span>';
            } else {
                $is_online = Carbon\Carbon::parse($value->last_seen)->diffForHumans();
            }
            // this is for scheme
            $schemes = Scheme::find($value->scheme_id);
            if ($schemes) {
                $package_id = $schemes->id;
                $package_name = $schemes->scheme_name;
            } else {
                $package_id = 0;
                $package_name = "No Package";
            }
            $schemeLoop = Scheme::where('status_id', 1)->whereNotIn('id', [$package_id])->get();
            $newPackageName = '<select class="form-control" id="packageId_' . $value->id . '" onchange="adminUpdatePackage(' . $value->id . ')" style="width:100%;">
            <option value="' . $package_id . '">' . $package_name . '</option>';
            foreach ($schemeLoop as $scheme):
                $newPackageName .= '<option value="' . $scheme->id . '"> ' . $scheme->scheme_name . '</option>';
            endforeach;
            $newPackageName .= '</select>';
            // end scheme
            $role_slug = (empty($role_slug)) ? $value->role->role_slug : $role_slug;
            $parent_down_users = url('admin/parent-down-users') . '/' . $role_slug . '/' . Crypt::encrypt($value->id);
            $parent = User::find($value->parent_id);
            $parent_name = $parent->name ?? '';
            $parent_last_name = $parent->last_name ?? '';
            $statement_url = url('admin/report/v1/user-ledger-report') . '/' . Crypt::encrypt($value->id);
            $countmyusers = User::where('parent_id', $value->id)->count();
            // parent dropdown for admin
            $parentLoop = User::whereIn('role_id', [1, 2, 3, 4, 5, 6, 7, 8])->whereNotIn('id', [$value->parent_id])->where('company_id', Auth::user()->company_id)->get();
            $parentDropDown = '<select class="form-control" id="parentId_' . $value->id . '" onchange="adminUpdateParent(' . $value->id . ')">
            <option value="' . $value->parent_id . '">' . $parent_name . ' ' . $parent_last_name . '</option>';
            foreach ($parentLoop as $par):
                $parentDropDown .= '<option value="' . $par->id . '"> ' . $par->name . ' ' . $par->last_name . '</option>';
            endforeach;
            $parentDropDown .= '</select>';
            $name = $value->name ?? '';

            if ($role_slug == 'super-admin') {
                $member_type = $value->role->role_title;
                if ($value->id != 1) {
                    $member_type = "Admin";
                }
                // pre($value->getRoleNames()[0] ?? "--");
                $asignRole = "N/A";
                if (isset($value->getRoleNames()[0])) {
                    $asignRole = $value->getRoleNames()[0];
                }
                $data_arr[] = array(
                    "id" => '<button class="btn btn-danger btn-sm" onclick="view_members(' . $value->id . ')"> ' . $value->id . ' View</button>',
                    "joining_date" => "$value->created_at",
                    "name" => $name,
                    "mobile_number" => $mobile,
                    "member_type" => $asignRole,
                    "status" => $status,
                );
            } else {
                $view_settings = '-';
                if (hasAdminPermission('admin.member.view.settings') && $value->role_id == 8) {
                    $view_settings = '<a href="javascript:;" class="btn btn-info btn-sm clsViewApiSettings" data-id=' . $value->id . '>Api Settings</a>';
                }
                //close parent dropdown for admin
                $data_arr[] = array(
                    "id" => '<button class="btn btn-danger btn-sm" onclick="view_members(' . $value->id . ')"> ' . $value->id . ' View</button>',
                    "joining_date" => "$value->created_at",
                    "name" => '<a href="' . $parent_down_users . '">' . $name . ' ' . $value->last_name . ' (' . $countmyusers . ')</a>',
                    "mobile_number" => $mobile,
                    "member_type" => $value->role->role_title,
                    "cms_agent_id" => $value->cms_agent_id,
                    "user_balance" => number_format($value->balance->user_balance, 2),
                    "aeps_balance" => number_format($value->balance->aeps_balance, 2),
                    "parent_name" => (Auth::User()->role_id != 1) ? $parent_name . ' ' . $parent_last_name : $parentDropDown,
                    "package_name" => (Auth::User()->role_id != 1) ? $package_name : $newPackageName,
                    "status" => $status,
                    'is_online' => ($is_online == '1 second ago') ? 'not logged in yet' : $is_online,
                    "statement" => '<a href="' . $statement_url . '" class="btn btn-primary btn-sm">Statement</a>',
                    'api_settings' => $view_settings
                );
            }
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

    function force_logout_all_users(Request $request)
    {
        if (Auth::User()->role_id == 1) {
            try {
                $role_id = Auth::User()->role_id;
                $company_id = Auth::User()->company_id;
                $user_id = Auth::id();
                $library = new MemberLibrary();
                $my_down_member = $library->my_down_member($role_id, $company_id, $user_id);
                $users = User::whereIn('id', $my_down_member)->get();
                foreach ($users as $value) {
                    $child_id = $value->id;
                    $new_session_id = \Session::getId(); //get new session_id after user sign in
                    $userDetails = User::find($child_id);
                    if ($userDetails->session_id != '') {
                        $last_session = \Session::getHandler()->read($userDetails->session_id);
                        if ($last_session) {
                            if (\Session::getHandler()->destroy($userDetails->session_id)) {
                            }
                        }
                    }
                    User::where('id', $child_id)->update(['session_id' => $new_session_id]);
                }
                return Response()->json(['status' => 'success', 'message' => 'All users successfully logouts!']);
            } catch (ModelNotFoundException $exception) {
                return Response()->json(['status' => 'failure', 'message' => $exception->getMessage()]);
            }
        } else {
            return Response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
        }
    }

    function view_user_active_services(Request $request)
    {
        $user_id = Crypt::decrypt($request->user_id);
        $profiles = Profile::where('user_id', $user_id)->first();
        return Response()->json([
            'status' => 'success',
            'message' => 'Successful..!',
            'active_services' => $profiles->active_services,
        ]);
    }

    function admin_list()
    {
        // get staff permission
        $role_slug = 'super-admin';
        $roles = Role::where('role_slug', $role_slug)->first();

        if ($roles) {
            $role_title = "Admin Users";
            $data = array(
                'page_title' => $role_title,
                'role_slug' => $role_slug,
                'url' => url('admin/member-list-api') . '?' . 'role_slug=' . $role_slug . '&parent_id=0',
            );
            $states = State::where('status_id', 1)->get();
            if ($this->backend_template_id == 1) {
                return view('admin.member_list', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 2) {
                return view('themes2.admin.member_list', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 3) {
                return view('themes3.admin.member_list', compact('states'))->with($data);
            } elseif ($this->backend_template_id == 4) {
                return view('themes4.admin.member_list', compact('states'))->with($data);
            } else {
                return redirect()->back();
            }
        } else {
            return redirect()->back();
        }
    }

    function view_update_admins($encrypt_id)
    {
        $user_id = Crypt::decrypt($encrypt_id);
        $userdetails = User::where('id', $user_id)->first();
        if ($userdetails) {
            if (Auth::User()->role_id == 1) {
                $role = new RolesNew();
                $roles = $role->getRoleArray();
                $details = array(
                    'user_id' => $encrypt_id,
                    'name' => $userdetails->name,
                    'middle_name' => $userdetails->middle_name,
                    'last_name' => $userdetails->last_name,
                    'fullname' => $userdetails->fullname,
                    'email' => $userdetails->email,
                    'mobile' => $userdetails->mobile,
                    'role_id' => $userdetails->role_id,
                    'status_id' => $userdetails->status_id,
                    'assign_role' => $userdetails->assign_role,
                    'roles' => $roles
                );
                $data = array('page_title' => 'Update User : ' . $userdetails->name . ' ' . $userdetails->last_name . '');
                if ($this->backend_template_id == 1) {
                    return view('admin.view_update_admins')->with($data)->with($details);
                }
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function update_admins(Request $request)
    {

        $user_id = Crypt::decrypt($request->user_id);
        $rules = array(
            'name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users,email,' . $user_id,
            'email',
            'mobile' => 'required|digits:10|unique:users,mobile,' . $user_id,
            'mobile',

        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }

        DB::beginTransaction();
        try {
            $assign_role = $request->role;
            User::where('id', $user_id)->update([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'assign_role' => $assign_role
            ]);
            $role = \Spatie\Permission\Models\Role::where('id', $assign_role)->first();
            $admin  = User::where('id', $user_id)->first();
            $admin->syncRoles($role);
            DB::commit();
            return Response()->json(['status' => 'success', 'message' => 'user successfully updated']);
        } catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
        }
    }

    function merchantUsersCreate()
    {
        $schemes = Scheme::get();
        $state = State::where('status_id', 1)->orderByRaw(
            "CASE WHEN name like 'Uttar Pradesh' THEN 1 ELSE 0 END DESC"
        )->get();
        $district = District::where('status_id', 1)->get();


        $servicegroup_id = Servicegroup::where('status_id', 1)->get(['id']);
        $services = Service::whereIn('status_id', [1])->whereIn('servicegroup_id', $servicegroup_id)->get();
        $data = array('page_title' => 'Create merchat');
        if ($this->backend_template_id == 1) {
            return view('admin.merchant_user.create_merchant', compact('schemes', 'state', 'district', 'services'))->with($data);
        } else {
            return redirect()->back();
        }
    }

    function storeMerchantUsers(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['create_merchant_permission'];
            if (!$myPermission == 1) {
                return response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        $rules = array(
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:merchant',
            'mobile_number' => 'required|unique:merchant|digits:10',
            'address' => 'required',
            'city' => 'required',
            'state_id' => 'required|exists:states,id',
            'pin_code' => 'required|digits:6|integer',
            'callback_url' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/|unique:merchant',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }
        $first_name = $request->first_name;
        $last_name = $request->last_name;

        $email = $request->email;
        $mobile = $request->mobile_number;

        $address = $request->address;
        $city = $request->city;
        $pin_code = $request->pin_code;
        $state_id = $request->state_id;
        $pan_number = $request->pan_number;
        $gst_number = $request->gst_number;
        $active_services = $request->active_services;
        $status = $request->status;
        $merchant_ip = ($request->merchant_ip) ? $request->merchant_ip : '';
        $server_ip = ($request->server_ip) ? $request->server_ip : '';
        if ($server_ip) {
            $server_ip = explode(",", $server_ip);
            $server_ip = json_encode($server_ip);
        }
        $is_ip_whiltelist = $request->is_ip_whiltelist;
        $callback_url = $request->callback_url ?? NULL;
        $longitude = $request->longitude ?? NULL;
        $latitude = $request->latitude ?? NULL;
        $password = mt_rand();
        DB::beginTransaction();

        try {
            $api_token = Str::random(36);
            $secrete_key = Str::random(16);
            $now = new \DateTime();
            $ctime = $now->format('Y-m-d H:i:s');
            $user_id = MerchantUsers::insertGetId([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'password' => bcrypt($password),
                'mobile_number' => $mobile,
                'created_at' => $ctime,
                'status' => $status,
                'address' => $address,
                'city' => $city,
                'state' => $state_id,
                'pincode' => $pin_code,
                'pan_number' => $pan_number,
                'gst_number' => $gst_number,
                'api_key' => $api_token,
                'secrete_key' => $secrete_key,
                'merchant_ip' => $merchant_ip,
                'is_ip_whiltelist' => $is_ip_whiltelist,
                'active_services' => $active_services,
                'callback_url' => $callback_url,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'server_ip' => $server_ip
            ]);
            if ($user_id) {
                $userDetails = MerchantUsers::find($user_id);
                DB::commit();
                try {
                    $data = array(
                        'customer_name' => $userDetails->first_name . ' ' . $userDetails->last_name,
                        'subject' => 'Your profile has been created successfully',
                        'company_name' => 'Trustxpay',
                        'app_url' => Route('merchant.login'),
                        'mobile' => $userDetails->mobile_number,
                        'password' => $password
                    );
                    \Mail::send('mail.register', $data, function ($m) use ($userDetails, $data) {
                        $m->to($userDetails['email'], $data['customer_name'])->subject($data['subject']);
                        $m->bcc(["anil.mathukiya@payomatix.com", "jatin.patel@payomatix.com"]);
                        $m->from(env('MAIL_FROM_ADDRESS'), $data['company_name']);
                    });
                } catch (\Exception $e) {
                    \Log::error("Signup email send failed===" . $e->getMessage());
                }

                return response()->json(['status' => 'success', 'message' => "Your profile has been created in our system. Please check your email inbox for the login details."]);
            }
        } catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
        }
    }

    function viewMerchantDetails(Request $request)
    {
        $id = $request->id;
        $users = MerchantUsers::where('id', $id)->first();
        if ($users) {
            $editUserUrl = url('admin/view-update-merchants') . '/' . Crypt::encrypt($users->id);
            $states = State::find($users->state);
            $details = array(
                'user_id' => $users->id,
                'update_anchor_url' => $editUserUrl,
                'first_name' => $users->first_name,
                'last_name' => $users->last_name,
                'mobile' => $users->mobile_number,
                'email' => $users->email,
                'wallet' => $users->wallet,
                'api_key' => $users->api_key,
                'secrete_key' => $users->secrete_key,
                'callback_url' => $users->callback_url,
                'address' => (isset($users->address)) ? $users->address : '',
                'city' => (isset($users->city)) ? $users->city : '',
                'state_name' => (empty($states) ? '' : $states->name),
                'pin_code' => (isset($users->pincode)) ? $users->pincode : '',
                'pan_number' => (isset($users->pan_number)) ? $users->pan_number : '',
                'gst_number' => (isset($users->gst_number)) ? $users->gst_number : '',
                'status' => $users->status,
                'is_ip_whiltelist' => $users->is_ip_whiltelist,
                'merchant_ip' => $users->merchant_ip,
                'server_ip' => $users->server_ip
            );
            return Response()->json([
                'status' => 'success',
                'details' => $details,
            ]);
        } else {
            return Response()->json([
                'status' => 'failure',
                'message' => 'User not found'
            ]);
        }
    }

    function viewMerchantUsers($encrypt_id)
    {
        $user_id = Crypt::decrypt($encrypt_id);
        $userdetails = MerchantUsers::where('id', $user_id)->first();
        if ($userdetails) {
            if (Auth::User()->role_id == 1) {
                $state = State::where('id', $userdetails->state)->first();
                $details = array(
                    'user_id' => $encrypt_id,
                    'first_name' => $userdetails->first_name,
                    'last_name' => $userdetails->last_name,
                    'email' => $userdetails->email,
                    'mobile' => $userdetails->mobile_number,
                    'status' => $userdetails->status,
                    'is_ip_whiltelist' => $userdetails->is_ip_whiltelist,
                    'active_services' => $userdetails->active_services,
                    'merchant_ip' => $userdetails->merchant_ip,
                    'server_ip' => $userdetails->server_ip,
                    'address' => $userdetails->address,
                    'city' => $userdetails->city,
                    'state' => $userdetails->state,
                    'pincode' => $userdetails->pincode,
                    'pan_number' => $userdetails->pan_number,
                    'gst_number' => $userdetails->gst_number,
                    'callback_url' => $userdetails->callback_url,
                    'latitude' => $userdetails->latitude,
                    'longitude' => $userdetails->longitude
                );
                $states = State::where('status_id', 1)->orderByRaw(
                    "CASE WHEN name like 'Uttar Pradesh' THEN 1 ELSE 0 END DESC"
                )->get();
                $state_id = State::where('id', $userdetails->state)->value('name');
                $cities = Zipcodes::select('city')->where('state', $state_id)->groupBy('city')->orderBy('city')->get();
                $servicegroup_id = Servicegroup::where('status_id', 1)->get(['id']);
                $services = Service::whereIn('status_id', [1])->whereIn('servicegroup_id', $servicegroup_id)->get();

                $data = array('page_title' => 'Update Merchant : ' . $userdetails->first_name . ' ' . $userdetails->last_name . '');
                if ($this->backend_template_id == 1) {
                    return view('admin.merchant_user.view_update_merchant', compact('states', 'services', 'cities'))->with($data)->with($details);
                }
            } else {
                return redirect()->back();
            }
        } else {
            return Redirect::back();
        }
    }

    function updateMerchantUsers(Request $request)
    {
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['update_merchant_permission'];
            if (!$myPermission == 1) {
                return response()->json(['status' => 'failure', 'message' => 'Sorry not permission']);
            }
        }
        $user_id = Crypt::decrypt($request->user_id);

        $rules = array(
            'first_name' => 'required',
            'last_name' => 'required',

            'email' => 'required|email|unique:merchant,email,' . $user_id,
            'email',
            'mobile' => 'required|digits:10|unique:merchant,mobile_number,' . $user_id,
            'mobile',

            'address' => 'required',
            'city' => 'required',
            'state_id' => 'required|exists:states,id',
            'pin_code' => 'required|digits:6|integer',
            'callback_url' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'pan_number' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return Response()->json(['status' => 'validation_error', 'errors' => $validator->getMessageBag()->toArray()]);
        }

        DB::beginTransaction();
        try {

            $status_id = $request->status_id;
            $is_ip_whiltelist = $request->is_ip_whiltelist;
            $merchant_ip = $request->merchant_ip;
            $server_ip = ($request->server_ip) ? $request->server_ip : '';
            if ($server_ip) {
                $server_ip = explode(",", $server_ip);
                $server_ip = json_encode($server_ip);
            }
            $address = $request->address;
            $city = $request->city;
            $state_id = $request->state_id;

            $pin_code = $request->pin_code;
            $pan_number = $request->pan_number;
            $gst_number = $request->gst_number;

            $callback_url = $request->callback_url ?? NULL;
            $longitude = $request->longitude ?? NULL;
            $latitude = $request->latitude ?? NULL;

            $userdetails = MerchantUsers::where('id', $user_id)->first();

            $mobile = (Auth::User()->role_id == 1) ? $request->mobile : $userdetails->mobile_number;
            $first_name = (Auth::User()->role_id == 1) ? $request->first_name : $userdetails->first_name;
            $last_name = (Auth::User()->role_id == 1) ? $request->last_name : $userdetails->last_name;
            $email = (Auth::User()->role_id == 1) ? $request->email : $userdetails->email;
            $active_services = (Auth::User()->role_id == 1) ? $request->active_services : $userdetails->active_services;
            MerchantUsers::where('id', $user_id)->update([
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'mobile_number' => $mobile,
                'status' => (empty($status_id) ? 0 : $status_id),
                'is_ip_whiltelist' => (empty($is_ip_whiltelist) ? 0 : $is_ip_whiltelist),
                'address' => $address,
                'city' => $city,
                'state' => $state_id,
                'pincode' => $pin_code,
                'pan_number' => $pan_number,
                'gst_number' => $gst_number,
                'active_services' => $active_services,
                'merchant_ip' => $merchant_ip,
                'callback_url' => $callback_url,
                'longitude' => $longitude,
                'latitude' => $latitude,
                'server_ip' => $server_ip,
            ]);
            DB::commit();
            return Response()->json(['status' => 'success', 'message' => 'user successfully updated']);
        } catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
        }
    }

    function view_merchant_active_services(Request $request)
    {
        $user_id = Crypt::decrypt($request->user_id);
        $profiles = MerchantUsers::where('id', $user_id)->first();
        return Response()->json([
            'status' => 'success',
            'message' => 'Successful..!',
            'active_services' => $profiles->active_services ?? null,
        ]);
    }

    function memberLoginProcess(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);
            // dd($user);
            if ($user) {
                Auth::logout();
                Auth::loginUsingId($user->id);
                return redirect()->route('login');
            }
            return redirect()->back()->with('error', 'Member not found.');
        } catch (Exception $e) {

            return ['status' => false, 'message' => 'Unable to Member login. ' . $e->getMessage()];
        }
    }

    function getViewApiSettings(Request $request)
    {
        $id = $request->id;
        $users = User::where('id', $id)->first();
        if ($users) {
            $editUserUrl = url('admin/member/edit/settings') . '/' . Crypt::encrypt($users->id);
            $details = array(
                'user_id' => $users->id,
                'role_id' => $users->role_id,
                'update_anchor_url' => $editUserUrl,
                'is_ip_whiltelist' => $users->is_ip_whiltelist,
                'server_ip' => $users->server_ip,
                'callback_url' => $users->callback_url,
                'api_key' => $users->api_key,
                'secret_key' => $users->secrete_key,

            );
            return Response()->json([
                'status' => 'success',
                'details' => $details,
            ]);
        } else {
            return Response()->json([
                'status' => 'failure',
                'message' => 'User not found'
            ]);
        }
    }

    public function getEditApiSettings(Request $request)
    {

        $encrypt_id = $request->id;
        $user_id = Crypt::decrypt($encrypt_id);
        $userdetails = User::where('id', $user_id)->first();
        if ($userdetails) {
            $details = array(
                'user_id' => $encrypt_id,
                'is_ip_whiltelist' => $userdetails->is_ip_whiltelist,
                'server_ip' => $userdetails->server_ip,
                'callback_url' => $userdetails->callback_url,
                'credentials_id' => $userdetails->credentials_id,
                'type_rs_per' => $userdetails->type_rs_per,
                'charges' => $userdetails->charges,
                'secret_key' => $userdetails->secrete_key,
                'api_key' => $userdetails->api_key,
                'credentials_id'=>$userdetails->credentials_id
            );
            $data = array('page_title' => 'Update Settings : ' . $userdetails->name . ' ' . $userdetails->last_name . '');
            $credentials = Credentials::get();
            return view('admin.view_update_users_settings',compact('credentials'))->with($data)->with($details);
        } else {
            return Redirect::back();
        }
    }


    function updateApiSettings(Request $request)
    {
        $user_id = Crypt::decrypt($request->user_id);
        DB::beginTransaction();
        try {

            $server_ip = ($request->server_ip) ? $request->server_ip : '';
            if ($server_ip) {
                $server_ip = explode(",", $server_ip);
                $server_ip = json_encode($server_ip);
            }
            $is_ip_whiltelist = $request->is_ip_whiltelist;
            $callback_url = $request->callback_url ?? NULL;
            $credentials_id = $request->credentials_id ?? NULL;

            User::where('id', $user_id)->update([
                'is_ip_whiltelist' => $is_ip_whiltelist,
                'callback_url' => $callback_url,
                'server_ip' => $server_ip,
                'credentials_id' => $credentials_id
            ]);
            DB::commit();
            return Response()->json(['status' => 'success', 'message' => 'Api settings successfully updated']);
        } catch (\Exception $ex) {
            DB::rollback();
            // throw $ex;
            return response()->json(['status' => 'failure', 'message' => $ex->getMessage()]);
        }
    }
}
