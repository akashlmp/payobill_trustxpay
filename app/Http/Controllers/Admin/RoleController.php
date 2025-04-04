<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Validator;
use App\Models\Masterbank;
use App\Models\RolesNew;
use \Crypt;
use App\Models\User;
use Helpers;
use App\Library\PermissionLibrary;
use App\Models\Company;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{

    public function __construct()
    {
        $this->company_id = Helpers::company_id()->id;
        $dt = Helpers::company_id();
        $this->company_id = $dt->id;
        $companies = Company::find($this->company_id);
    }


    function index(Request $request)
    {
        $input = $request->all();
        $role = new RolesNew();
        // get staff permission
        if (Auth::User()->role_id == 2) {
            $library = new PermissionLibrary();
            $permission = $library->getPermission();
            $myPermission = $permission['role_master_permission'];
            if (!$myPermission == 1) {
                return redirect()->back();
            }
        }
        if (Auth::User()->id == 1) {
            $roles = $role->getData($input);
            $data = array('page_title' => 'Role Master');
            return view('admin.master.permission.index', compact('roles'))->with($data);
        } else {
            return Redirect::back();
        }
    }
    public function create()
    {
        if (Auth::User()->id != 1) {
            return Redirect::back();
        }
        $role = new RolesNew();
        $params['permission'] = $role->getPermissionModuleWithSubModule();
        $params['modules'] = $role->getPermissionModules();
        $params['page_title'] = "Create Role";
        return view('admin.master.permission.create', $params);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles_new,name',
            'permission' => 'required',
        ]);
        $role = new RolesNew();
        DB::beginTransaction();
        try {
            $input = $request->except('_token');
            $input['guard_name'] = 'web';
            $role->createRoleWithPermission($input);
            DB::commit();
            return redirect()->route('admin.roles')->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
            DB::rollback();
            Log::error($e);
            return redirect()->route('admin.roles')->with('error', 'Unable to create role.');
        }
    }

    public function edit($id)
    {
        if (Auth::User()->id != 1) {
            return Redirect::back();
        }

        $role = new RolesNew();
        $id = base64Decode($id);
        $params['role'] = $role->getById($id);
        $params['permission'] = $role->getPermissions();
        $params['modules'] = $role->getPermissionModules();
        $params['role_permissions'] = $role->getPermissionIdsByRoleId($id);
        $params['page_title'] = "Edit Role";
        return view('admin.master.permission.edit', $params);
    }

    public function update(Request $request, $id)
    {
        $id = base64Decode($id);
        $this->validate($request, [
            'name' => 'required|unique:roles_new,name,' . $id,
            'permission' => 'required',
        ]);
        $role = new RolesNew();
        DB::beginTransaction();
        try {
            $input = $request->except('_token');
            $input['guard_name'] = 'web';
            $role->createRoleWithPermission($input, $id);
            DB::commit();
            return redirect()->route('admin.roles')->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e);
            return redirect()->route('admin.roles')->with('error', 'Unable to update role.');
        }
    }

    public function show($id)
    {
        $role = new RolesNew();
        $id = base64Decode($id);
        $params['role'] = $role->getById($id);
        if ($params['role']) {
            $params['role_permissions'] = $role->getPermissionByRoleId($id);
            $params['page_title'] = "View Role";
            return view('admin.master.permission.show', $params);
        }
        return abort(404);
    }

    public function destroy(Request $request)
    {
        $id = $request->id;
        DB::beginTransaction();
        $role = new RolesNew();
        try {
            $id = base64Decode($id);
            $role->destroyData($id);
            DB::commit();
            return Response()->json(['status' => '1', 'message' => 'Role deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return Response()->json(['status' => 'failure', 'message' => 'Unable to delete role.']);
        }
    }
}
