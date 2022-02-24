<?php
namespace App\Http\Controllers;

use App\Models\DataPribadi;
use App\Models\Skck;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Xendit\Xendit;
use Validator;

class PaymentController extends Controller{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['payCallback']]);
    }

    public function payment(Request $request){

        $validator = Validator::make($request->all(), [
                // 'user_id' => 'required',
                'eskck_id' => 'required',
                'method' => 'required',
            ],
        );

        $eskck = Skck::find($request->eskck_id);
        $dataPribadi = DataPribadi::where('dp_eskck_id','=',$request->eskck_id)->first();

        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');
        
        $date = Carbon::now();
        $date->addDays(1);
        $params = [
            "external_id" => 'ESKCK'.time().'-'.$request->eskck_id,
            "bank_code" => $request->method,
            "name" => $dataPribadi->dp_nama_lengkap,
            "expected_amount" => 30000,
            "suggested_amount" => 30000,
            "expiration_date" => $date->toIso8601String()
        ];

        // dd($params);

        Xendit::setApiKey('xnd_development_TTMpE5gZsDk3dTF3T2X30sTHFshDL7CR4oGALGVipJSt8tceSvPuSmTNJGehN1C');
        $createVA = \Xendit\VirtualAccounts::create($params);
        // var_dump($createVA);
        $eskck->update([
            'va' => $createVA['account_number'],
            'expired_date' => $createVA['expiration_date'],
            'external_id' => $createVA['external_id'],
            'bank_code' => $createVA['bank_code'],
            'status' => $createVA['status'],
        ]);

        return response()->json(['data'=>$eskck,'message'=>'Feedback Successfully Saved'], 200);

    }

    public function payCallback(Request $req){

        $eskck = Skck::where('external_id','=',$req->external_id)->first();

        if(Skck::where('external_id','=',$req->external_id)->count()){

            $eskck_expire = Carbon::parse($req->transaction_timestamp)->setTimezone('Asia/Jakarta');
            $eskck_expire->addMonths(6);
    
            $eskck->update([
                'status' => "SETTLEMENT",
                'eskck_expire' => $eskck_expire,
            ]);
    
            return response()->json($eskck, 200);
        } else {
            return response()->json('eskck tidak ditemukan tapi tenang yang penting nyambung', 200);
        }

    }
}