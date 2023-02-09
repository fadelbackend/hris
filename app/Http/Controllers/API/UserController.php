<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Rules\Password;

class UserController extends Controller
{
    public function login(Request $request)
    {
        try {
            //validate request
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credential = request(['email', 'password']);
            if (!Auth::attempt($credential)) {
                return ResponseFormatter::error($credential, 'Unauthorizes');
            }

            $user = User::where('email', $request->email)->first();
            if (!Hash::check($request->password, $user->password)) {
                throw new Exception('Invalid Password');
            }

            //generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //return respone
            return ResponseFormatter::success([
                'acces_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Login succes');

        } catch (Exception $error) {
            return ResponseFormatter::error('Authication Failed');
        }

    }

    public function register(Request $request)
    {
        try {
            //validate request
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['required', 'string', new Password],
            ]);

            //create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            //generate token
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            //return respone
            return ResponseFormatter::success([
                'acces_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user,
            ], 'Register succes');

        } catch (Exception $error) {
            $user = request(['password']);
            return ResponseFormatter::error($user, $error->getMessage());
        }
    }

    public function logout(Request $request)
    {
        //Revoke Token
        $token = $request->user()->currentAccessToken()->delete();

        //respone
        return ResponseFormatter::success($token, 'Logout succes');
    }

    public function fetch(Request $request)
    {
        //Get user
        $user = $request->user();

        //respone
        return ResponseFormatter::success($user, 'Fetch succes');
    }
}
