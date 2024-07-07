<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\ErrorHandler\ErrorService;
use App\Services\ErrorService as ServicesErrorService;
use Exception;
use Illuminate\Validation\ValidationException;

class LoginService
{
    protected $errorService;

    /* The JSON response function can be seen in helper.php under the app folder.
        The error handling service can be found in app/Services. */

    public function __construct(ServicesErrorService $errorService)
    {
        $this->errorService = $errorService;
    }

    public function LoginAllUsers(Request $request)
    {
        try {
            // ............Validate user login............
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $credentials = $request->only('email', 'password');

            $token = Auth::attempt($credentials);

            if (!$token) {
                return jsonResponse('Unauthorized', null, 401, 'error');
            }

            $user = Auth::user();

            return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 200);
        } catch (Exception $e) {

            return $this->errorService->handleError($e);

        }
    }


    public function RegisterUsers(Request $request)
    {
        ;
        try {
            //............ Validate user request............
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
            ]);
            // dd($request->validate([
            //     'name' => 'required|string|max:255',
            //     'email' => 'required|string|email|max:255|unique:users',
            //     'password' => 'required|string|min:6',
            // ]));
            // ............Create a new user............
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);


            // ............Log the user in and generate a token............
            $token = Auth::login($user);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 200);

        } catch (\Exception $e) {
            // ............Handle my exceptions
            return $this->errorService->handleError($e);

            return jsonResponse('Email already exists', null, 409, 'error');
        }
    }



    public function logOut()
    {
        try {

            Auth::logout();

            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
        } catch (Exception $e) {

            return $this->errorService->handleError($e);
        }
    }
}