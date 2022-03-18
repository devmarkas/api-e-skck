<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
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
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

      $user = User::where('email', '=', request(['email']))->first();
      
        if ($user->email_verified_at == null || $user->email_verified_at == '') {
            return response()->json(['error' => 'Email is not verified'], 401);
        }

        $user->update([
            'token_id' => $token
        ]);

        return $this->respondWithToken($token, $user->role);
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
    protected function respondWithToken($token, $role)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'role' => $role == 1 ? 'Admin' : 'User',
        ]);
    }

    public function register(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => ['min:8', 'required_with:confirm_password', 'same:confirm_password'],
                'confirm_password' => ['required', 'string', 'min:8'],
                // [
                // 'required' => ':attribute required',
                // 'min' => ':attribute must be at least :min',
                // ]
            ]
        );

        if ($validator->passes()) {
            // $this->notify(new App\Notifications\CustomVerifyEmail);
            $user = $this->saveUser($request);
            $this->sendMail($user);
            // $credentials = request(['email', 'password']);
            // $token = auth()->attempt($credentials);

            // return $this->respondWithToken($token);
            return response()->json(['message' => 'Check your email'], 201);
        } else {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
    }

    // protected function checkEmail($request){
    //     $data = DB::table('users')->where('email', '=', $request)->count();

    //     return $data;
    // }

    protected function sendMail($user)
    {
        // return $user;
        $expires = strtotime("+30 minutes");
        $link = url('/email/verify?expires=' . $expires . '&signature=' . Hash::make($user->email));
        $data = array('email' => $user->email, 'url' => $link, 'subject' => 'Verify Your Email');

        Mail::send('mail', $data, function ($message) use ($user) {
            $message->to($user->email)->subject('Verify Email Address');
            // $message->from('selva@snamservices.com','Selvakumar');
        });
    }

    protected function saveUser($request)
    {
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

    public function verify(Request $request)
    {
        if (strtotime("now") > $request->expires) {
            return 'This link has been expired';
        }
        $user = DB::table('users')->get();

        foreach ($user as $key => $item) {
            if (Hash::check($item->email, $request->signature)) {
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

    public function forgot(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => ['required', 'string', 'email', 'max:255'],
                // [
                // 'required' => ':attribute required',
                // 'min' => ':attribute must be at least :min',
                // ]
            ]
        );

        if ($validator->passes()) {
            $user = User::where('email', '=', $request->email)->first();

            // return $user;
            if ($user) {
                $this->sendLink($user);

                return response()->json(['message' => 'Check your email'], 201);
            } else {
                return response()->json(['error' => 'Email not found'], 400);
            }
        } else {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
    }

    protected function sendLink($user)
    {
        $expires = strtotime("+30 minutes");
        $link = url('/reset-password?expires=' . $expires . '&signature=' . Hash::make($user->email));
        $data = array('email' => $user->email, 'url' => $link, 'subject' => 'Reset Password');

        Mail::send('mail', $data, function ($message) use ($user) {
            $message->to($user->email)->subject('Reset Password');
            // $message->from('selva@snamservices.com','Selvakumar');
        });
    }

    public function resetpassword(Request $request)
    {
        if (strtotime("now") > $request->expires) {
            return 'This link has been expired';
        }

        $error = null;

        return view('resetPassword', compact('request', 'error'));
    }

    public function saveNewPassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'password' => 'required|required_with:password_confirmation|same:password_confirmation|min:8',
                'password_confirmation' => 'required|min:8',
            ],
            [
                'required' => ':attribute belum terisi',
                'same' => ':attribute harus sama'
            ]
        );

        if ($validator->passes()) {
            if (strtotime("now") > $request->expires) {
                return 'This link has been expired';
            }
            $user = DB::table('users')->get();

            foreach ($user as $key => $item) {
                if (Hash::check($item->email, $request->signature)) {
                    $userReset = DB::table('users')
                        ->where('id', '=', $item->id)
                        ->update([
                            'password' => Hash::make($request->password)
                        ]);

                    return 'Password has been reset';
                    break;
                }
            }
            return 'Has been error';
            // return redirect('https://e-skck.polresta-tangerang.com/login');
        } else {
            $request = json_decode(json_encode([
                'expires' => $request->expires,
                'signature' => $request->signature,
            ]));

            $error = $validator->errors()->all();
            return view('resetPassword', compact('request', 'error'));
        }
    }
}
