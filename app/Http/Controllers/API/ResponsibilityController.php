<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponRequest;
use App\Http\Requests\UpdateReponsRequest;
use App\Models\Responsibility;
use Exception;
use Illuminate\Http\Request;

class ResponsibilityController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $ResponQuery = Responsibility::query();

        //menampilkan 1
        if ($id) {
            //cuman bisa mengambil data yg dimiliki mengunakan relasi
            $responsibility = $ResponQuery->find($id);

            if ($responsibility) {
                return ResponseFormatter::success($responsibility, 'Role Found');
            }
            return ResponseFormatter::error($responsibility, 'Role not found');
        }

        //mencari sesuai nama
        //menampilkan data dengan reationship
        $responsibilitys = $ResponQuery->where('role_id', $request->role_id);

        if ($name) {
            $responsibilitys->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $responsibilitys->paginate($limit),
            'Role Found'
        );

    }

    public function create(CreateResponRequest $request)
    {
        try {

            //create company
            $respon = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            if (!$respon) {
                throw new Exception('Responsibility not created');
            }

            return ResponseFormatter::success($respon, 'Responsibility Created');

        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function update(UpdateReponsRequest $request, $id)
    {
        try {
            //cek
            $respon = Responsibility::find($id);

            if (!$respon) {
                throw new Exception('Responsibility not found');
            }

            //update role
            $respon->update([
                'name' => $request->name,
                'role_id' => $request->role_id,
            ]);

            return ResponseFormatter::success($respon, 'Responsibility update');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'Responsibility field');
        }
    }

    public function destroy($id)
    {
        try {
            //cek
            $respon = Responsibility::find($id);

            if (!$respon) {
                throw new Exception('Responsibility not found');
            }

            //delete
            $respon->delete();
            return ResponseFormatter::success($respon, 'Responsibility Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

}
