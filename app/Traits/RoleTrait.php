<?php

namespace App\Traits;


use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait RoleTrait
{

    public function getById($id)
    {
        return Role::where('id', $id)->first();
    }

    public function getData($input = [], $per_page = 0)
    {
        $user = getAdminLoginUser();
        $query = Role::query();
        if (!isSuperAdmin($user)) {
            $query->where('name', '!=', 'Super Admin');
        }
        $query->orderBy('name');
        if ($per_page > 0) {
            return $query->paginate($per_page);
        }
        return $query->get();
    }

    public function getRoleArray()
    {
        $user = getAdminLoginUser();
        $query = Role::query();
        if (!isSuperAdmin($user)) {
            $query->where('name', '!=', 'Super Admin');
        }
        return $query->pluck('name', 'id')->toArray();
    }

    public function createRoleWithPermission($input, $id = null)
    {
        // pre($input);
        $role = new Role();
        if ($id) {
            $role = $this->getById($id);
        }
        $role->name = isset($input['name']) ? $input['name'] : '';
        $role->guard_name = isset($input['guard_name']) ? $input['guard_name'] : '';
        $role->save();
        $role->syncPermissions(isset($input['permission']) ? $input['permission'] : []);
        return $role;
    }

    public function getPermissionByRoleId($id)
    {
        return Permission::join("role_has_permissions", "role_has_permissions.permission_id", "=", "permissions.id")
            ->where("role_has_permissions.role_id", $id)
            ->get()->groupBy('module')->map(function ($per) {
                return $per->groupBy('sub_module');
            });
    }

    public function getPermissionIdsByRoleId($id)
    {
        return DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')->toArray();
    }

    public function getPermissions()
    {
        return Permission::query()->where("guard_name", "web")->get()->groupBy('module')->map(function ($per) {
            return $per->groupBy('sub_module');
        });
    }

    public function getPermissionModules()
    {
        return Permission::where("guard_name", "web")->distinct()->pluck('module')->toArray();
    }

    public function getPermissionModuleWithSubModule()
    {
        return Permission::where("guard_name", "web")->get()->groupBy('module')->map(function ($per) {
            return $per->groupBy('sub_module');
        });
    }

    public function getWebPermissionModules()
    {
        return Permission::where("guard_name", "web")->distinct()->pluck('module')->toArray();
    }

    public function getWebPermissionModuleWithSubModule()
    {
        return Permission::where("guard_name", "web")->get()->groupBy('module')->map(function ($per) {
            return $per->groupBy('sub_module');
        });
    }


    public function destroyData($id)
    {
        $role = $this->getById($id);
        if ($role) {
            $role->users()->detach();
            $role->permissions()->detach();
            return $role->delete();
        }
        return false;
    }
}
