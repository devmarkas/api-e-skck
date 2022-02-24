<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register','verify']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        $user = DB::table('users')->where('email', '=', request(['email']))->first();

        if($user->email_verified_at == null || $user->email_verified_at == ''){
            return response()->json(['error' => 'Email is not verified'], 401);
        }

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function register(Request $request){

        $validator = Validator::make($request->all(), [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['min:8', 'required_with:confirm_password', 'same:confirm_password'],
                'confirm_password' => ['required','string', 'min:8'],
                // [
                // 'required' => ':attribute required',
                // 'min' => ':attribute must be at least :min',
                // ]
            ]
        );

        if($validator->passes()){
            // $this->notify(new App\Notifications\CustomVerifyEmail);
            $user = $this->saveUser($request);
            $this->sendMail($user);
            // $credentials = request(['email', 'password']);
            // $token = auth()->attempt($credentials);
        
            // return $this->respondWithToken($token);
            return response()->json(['message'=>'Check your email'], 201);
            
        }else{
            return response()->json(['error'=>$validator->errors()->all()], 400);
        }  
    }

    // protected function checkEmail($request){
    //     $data = DB::table('users')->where('email', '=', $request)->count();

    //     return $data;
    // }

    protected function sendMail($user){
        // return $user;
        $expires = strtotime("+30 minutes");
        $link = url('/email/verify?expires='.$expires.'&signature='.Hash::make($user->email));
        $data = array('email'=>$user->email,'url'=>$link);

        Mail::send('mail', $data, function($message) use ($user) {
            $message->to($user->email)->subject('Verify Email Address');
            // $message->from('selva@snamservices.com','Selvakumar');
        });
    }

    protected function saveUser($request){
        DB::table('users')->insert(
            [
                'first_name' => $request->first_name, 
                'last_name' => $request->last_name, 
                'phone' => $request->phone, 
                'email' => $request->email, 
                'role' => '2',
                'password' => Hash::make($request->password),
            ]
        );

        return $request;
    }

    public function verify(Request $request){
        if(strtotime("now") > $request->expires){
            return 'This link has been expired';
        }
        $user = DB::table('users')->get();

        foreach($user as $key => $item){
            if(Hash::check($item->email, $request->signature)){
                DB::table('users')
                ->where('id', '=', $item->id)
                ->update([
                    'email_verified_at' => date("Y-m-d H:i:s")
                ]);

                // return $item->email.'<br>'.$request->signature.'<br>'.Hash::make($item->email);
                return 'Your email has been verify';
                break;
            }
        }

        return 'tidak ada yang sama';
        
    }
}