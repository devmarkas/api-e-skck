<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware(
            'auth:api',
            // ['except' => ['save']]
        );
    }

    public function user()
    {
        $user = User::find(Auth::user()->id);

        $data = json_decode(json_encode([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'phone' => $user->phone,
            'email' => $user->email,
            'role' => $user->role == '1' ? 'Admin' : 'User',
        ]));

        return response()->json(['data' => $data, 'message' => 'User profile Has Successfully Retrive'], 201);
    }
}
