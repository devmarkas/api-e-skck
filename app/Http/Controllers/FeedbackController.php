<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use Validator;

class FeedbackController extends Controller{

    public function __construct()
    {
        $this->middleware('auth:api', 
            // ['except' => ['save']]
        );
    }

    public function save(Request $request){

        $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'ulasan' => 'required',
                'rating' => 'required',
            ], 
            // [
            //     'required' => ':attribute belum terisi',
            // ]
        );

        if ($validator->passes()) {

            $feedback = new Feedback();
            $feedback->user_id = $request->user_id;
            $feedback->ulasan = $request->ulasan;
            $feedback->rating = $request->rating;

            $feedback->save();

            return response()->json(['message'=>'Feedback Successfully Saved'], 200);
        } else {
            return response()->json(['error'=>$validator->errors()->all()], 401);
        }

    }

}