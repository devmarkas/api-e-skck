<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Validator;

class FeedbackController extends Controller
{

    public function __construct()
    {
        $this->middleware(
            'auth:api',
            // ['except' => ['save']]
        );
    }

    public function save(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                // 'user_id' => 'required',
                'ulasan' => 'required',
                'rating' => 'required',
            ],
            // [
            //     'required' => ':attribute belum terisi',
            // ]
        );

        if ($validator->passes()) {

            $user = Auth::user();

            $feedback = new Feedback();
            $feedback->user_id = $user->id;
            $feedback->ulasan = $request->ulasan;
            $feedback->rating = $request->rating;

            $feedback->save();

            return response()->json(['message' => 'Feedback Successfully Saved'], 200);
        } else {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
    }

    public function ratingApp()
    {
        if (Auth::user()->role != '1') {
            return response()->json(["message" => "You can't access"], 401);
        }

        $data = Feedback::avg('rating');

        return response()->json(['data' => number_format((float)$data, 1, '.', ''), 'message' => 'App Rating Successfully Retrive'], 200);
    }

    public function list(Request $request)
    {
        // $date = new Carbon($request->tanggal_awal);
        // return $date;
        if (Auth::user()->role != '1') {
            return response()->json(["message" => "You can't access"], 401);
        }

        $data = Feedback::join('users', 'users.id', '=', 'feedback.user_id')
            ->when($request->search, function ($query, $search) {
                $query->where('users.first_name', 'like', '%' . $search . '%')
                    ->orWhere('users.last_name', 'like', '%' . $search . '%');
            })
            ->when($request->tanggal_awal, function ($query, $tanggal_awal) {
                $date = new Carbon($tanggal_awal);
                $query->whereDate('feedback.created_at', '>=', $date);
            })
            ->when($request->tanggal_akhir, function ($query, $tanggal_akhir) {
                $date = new Carbon($tanggal_akhir);
                $query->whereDate('feedback.created_at', '<=', $date);
            })
            ->select('users.first_name', 'users.last_name', 'feedback.ulasan', 'feedback.rating', 'feedback.created_at')
            ->paginate(10);

        return response()->json(['data' => $data, 'message' => 'Feedback Successfully Retrive'], 200);
    }
}
