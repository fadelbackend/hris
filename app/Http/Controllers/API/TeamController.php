<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;

class TeamController extends Controller
{

    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $teamQuery = Team::query();

        //menampilkan 1
        if ($id) {
            //cuman bisa mengambil data yg dimiliki mengunakan relasi
            $team = $teamQuery->find($id);

            if ($team) {
                return ResponseFormatter::success($team, 'Team Found');
            }
            return ResponseFormatter::error($team, 'Team not found');
        }

        //mencari sesuai nama
        //menampilkan data dengan reationship
        $teams = $teamQuery->where('company_id', $request->company_id);

        if ($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Team Found'
        );

    }

    public function create(CreateTeamRequest $request)
    {
        try {
            // upload icon
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            //create company
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id,
            ]);

            if (!$team) {
                throw new Exception('Team not create');
            }

            return ResponseFormatter::success($team, 'Team Create');

        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        try {
            //cek
            $team = Team::find($id);

            if (!$team) {
                throw new Exception('Team not found');
            }

            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            //update company
            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id,
            ]);

            return ResponseFormatter::success($team, 'Team update');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'update field');
        }
    }

    public function destroy($id)
    {
        try {
            //cek
            $team = Team::find($id);

            if (!$team) {
                throw new Exception('Team not found');
            }

            //delete
            $team->delete();
            return ResponseFormatter::success($team, 'Team Deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

}
