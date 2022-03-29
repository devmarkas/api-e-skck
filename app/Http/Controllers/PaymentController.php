<?php

namespace App\Http\Controllers;

use App\Models\DataPribadi;
use App\Models\Skck;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Xendit\Xendit;
use Validator;

class PaymentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['payCallback', 'fvaUpdate']]);
    }

    public function payment(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                // 'user_id' => 'required',
                'eskck_id' => 'required',
                'method' => 'required',
            ],
        );

        $eskck = Skck::find($request->eskck_id);
        $dataPribadi = DataPribadi::where('dp_eskck_id', '=', $request->eskck_id)->first();

        Carbon::setLocale('id');
        date_default_timezone_set('Asia/Jakarta');

        $date = Carbon::now();
        $date->addDays(1);
        $params = [
            "external_id" => 'ESKCK' . time() . '-' . $request->eskck_id,
            "bank_code" => $request->method,
            "name" => $dataPribadi->dp_nama_lengkap,
            "expected_amount" => 30000,
            "suggested_amount" => 30000,
            "expiration_date" => $date->toIso8601String()
        ];

        // dd($params);

        Xendit::setApiKey(env('PAYMENT_KEY'));
        $createVA = \Xendit\VirtualAccounts::create($params);
        if ($createVA['status'] == 'PENDING') {
            $status = 'Belum Dibayar';
        } else if ($createVA['status'] == 'SETTLEMENT') {
            $status = 'Sudah Dibayar';
        } else {
            $status = 'Gagal';
        }
        // var_dump($createVA);
        $eskck->update([
            'va' => $createVA['account_number'],
            'expired_date' => $createVA['expiration_date'],
            'external_id' => $createVA['external_id'],
            'bank_code' => $createVA['bank_code'],
            'status' => $status,
        ]);

        return response()->json(['data' => $eskck, 'message' => 'Payment has been saved'], 200);
    }

    public function payCallback(Request $req)
    {

        $eskck = Skck::where('external_id', '=', $req->external_id)->first();

        if (Skck::where('external_id', '=', $req->external_id)->count()) {

            $eskck_expire = Carbon::parse($req->transaction_timestamp)->setTimezone('Asia/Jakarta');
            $eskck_expire->addMonths(6);

            $eskck->update([
                'status' => "Sudah Dibayar",
                'eskck_expire' => $eskck_expire,
            ]);

            return response()->json($eskck, 200);
        } else {
            return response()->json('eskck tidak ditemukan tapi tenang yang penting nyambung', 200);
        }
    }

    public function fvaUpdate(Request $req)
    {
        $eskck = Skck::where('external_id', '=', $req->external_id)->first();

        if (Skck::where('external_id', '=', $req->external_id)->count()) {
            if ($req->status == "INACTIVE") {

                $eskck->update([
                    'status' => "Gagal",
                ]);

                return response()->json($eskck, 200);
            } else if ($req->status) {
                $eskck->update([
                    'status' => "Belum Dibayar",
                ]);

                return response()->json($eskck, 200);
            }
        } else {
            return response()->json('eskck tidak ditemukan tapi tenang yang penting nyambung', 200);
        }
    }
}
