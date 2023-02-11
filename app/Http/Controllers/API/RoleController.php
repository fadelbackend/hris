<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRolesRequest;
use App\Http\Requests\UpdateRolesRequest;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class RoleController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $roleQuery = Role::query();
        $withResponsibility = $request->input('withResponsibility', false);

        //menampilkan 1
        if ($id) {
            //cuman bisa mengambil data yg dimiliki mengunakan relasi
            $role = $roleQuery->with('responsibilities')->find($id);

            if ($role) {
                return ResponseFormatter::success($role, 'Role Found');
            }
            return ResponseFormatter::error($role, 'Role not found');
        }

        //mencari sesuai nama
        //menampilkan data dengan reationship
        $roles = $roleQuery->where('company_id', $request->company_id);

        if ($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }

        if ($withResponsibility) {
            $roles->with('responsibilities');
        }

        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Role Found'
        );

    }

    public function create(CreateRolesRequest $request)
    {
        try {

            //create company
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            if (!$role) {
                throw new Exception('Role not create');
            }

            return ResponseFormatter::success($role, 'Role Create');

        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function update(UpdateRolesRequest $request, $id)
    {
        try {
            //cek
            $role = Role::find($id);

            if (!$role) {
                throw new Exception('Role not found');
            }

            //update role
            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($role, 'Role update');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'Role field');
        }
    }

    public function destroy($id)
    {
        try {
            //cek
            $role = Role::find($id);

            if (!$role) {
                throw new Exception('Role not found');
            }

            //delete
            $role->delete();
            return ResponseFormatter::success($role, 'Role Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

}
