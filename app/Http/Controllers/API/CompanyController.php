<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);
        $companyQuery = Company::with(['users'])->whereHas('users', function ($query) {
            $query->where('user_id', Auth::id());
        });
        $withTeam = $request->input('withTeam', false);

        //menampilkan 1
        if ($id) {
            //cuman bisa mengambil data yg dimiliki mengunakan relasi
            $company = $companyQuery->with('teams')->find($id);

            if ($company) {
                return ResponseFormatter::success($company, 'Company Found');
            }
            return ResponseFormatter::error($company, 'Company not found');
        }

        //mencari sesuai nama
        //menampilkan data dengan reationship
        $companies = $companyQuery;

        if ($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }

        // if ($withTeam) {
        //     $companies->with('teams');
        // }

        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Conpanies Found'
        );

    }

    public function create(CreateCompanyRequest $request)
    {

        try {
            // upload logo
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            //create company
            $company = Company::create([
                'name' => $request->name,
                'logo' => isset($path) ? $path : '',
            ]);

            if (!$company) {
                throw new Exception('Company not create');
            }

            //relasioship company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            //load user in company
            $company->load('users');

            return ResponseFormatter::success($company, 'Company Create');

        } catch (Exception $error) {
            return ResponseFormatter::error($error->getMessage(), 500);
        }

    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            //cek
            $company = Company::find($id);

            if (!$company) {
                throw new Exception('Company not found');
            }

            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            //update company
            $company->update([
                'name' => $request->name,
                'logo' => isset($path) ? $path : $company->logo,
            ]);

            return ResponseFormatter::success($company, 'Company update');

        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 'update field');
        }
    }
}
