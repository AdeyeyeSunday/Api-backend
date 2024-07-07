<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\LoginService;
use App\Http\Controllers\Auth\AuthenticatesUsers;

class AuthController extends Controller
{

            protected $loginService;

    public function __construct(LoginService $loginService)
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);

        return  $this->loginService = $loginService;
    }

    public function login(Request $request)
    {
      return $this->loginService->LoginAllUsers($request);
    }


    public function register(Request $request){
        return  $this->loginService->RegisterUsers($request);
    }

    public function logout()
    {
        return $this->loginService->logOut();
    }

}
