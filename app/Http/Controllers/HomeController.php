<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    //
    public function user()
    {
        return jsonResponse('User data fetched', User::whereId(Auth::user()->id)->first());
    }

    public function getAllUsers(Request $request)
    {

        $query = User::query();
        if ($request->has('search')) {

            $searchTerm = $request->input('search');

            $query->where('name', 'like', "%{$searchTerm}%")
            
                ->orWhere('email', 'like', "%{$searchTerm}%");
        }

        $users = $query->paginate(10);

        return jsonResponse('Users data fetched', $users);

    }

}
