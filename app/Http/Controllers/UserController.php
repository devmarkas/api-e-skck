<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller{

    public function __construct()
    {
        $this->middleware('auth:api', 
            // ['except' => ['save']]
        );
    }

    public function user(){
        $data = User::find(Auth::user()->id)->select('first_name','last_name','phone','email')->first();

        return response()->json(['data'=>$data,'message'=>'User profile Has Successfully Retrive'], 201);        
    }
}