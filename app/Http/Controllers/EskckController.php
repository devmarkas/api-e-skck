<?php

namespace App\Http\Controllers;

use App\Models\DataPribadi;
use App\Models\Fisik;
use App\Models\Keluarga;
use App\Models\Keterangan;
use App\Models\Lampiran;
use App\Models\Pendidikan;
use App\Models\Pidana;
use App\Models\Satwil;
use App\Models\Skck;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Validator;

class EskckController extends Controller
{

    public function __construct()
    {
        $this->middleware(
            'auth:api',
            // ['except' => ['save']]
        );
    }

    public function search(Request $request)
    {
        // return $request->status_pembayaran;
        if (Auth::user()->role != '1') {
            return response()->json(["message" => "You can't access"], 401);
        }
        $data = Skck::join('satwil', 'satwil.eskck_id', '=', 'eskck.id')
            ->join('users', 'users.id', '=', 'eskck.user_id')
            ->when($request->tanggal_pembuatan, function ($query, $pembuatan) {
                $query->where('eskck.created_at', '>=', $pembuatan . '23:59:59');
            })
            ->when($request->masa_berlaku, function ($query, $masa_berlaku) {
                $query->where('eskck.eskck_expire', '<=', $masa_berlaku . '23:59:59')
                    ->where('eskck.eskck_expire', '!=', '');
            })
            ->when($request->status_pembayaran, function ($query, $status) {
                $query->where('eskck.status', '=', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->orwhere('users.first_name', 'like', '%' . $search . '%')
                    ->orWhere('users.last_name', 'like', '%' . $search . '%')
                    ->orWhere('satwil.keperluan', 'like', '%' . $search . '%');
            })
            ->select('users.first_name as nama_pertama', 'users.last_name as nama_terakhir', 'eskck.created_at as tanggal_pembuatan', 'eskck.eskck_expire as masa_berlaku', 'eskck.status as status_pembayaran', 'satwil.keperluan as keperluan')
            ->paginate(10);

        return response()->json(["data" => $data, "message" => "E-SKCK search and filter successfully retrive"], 201);
    }

    public function keperluan()
    {
        $enamBulanLalu = Carbon::now()->subMonths(6);

        $dataKeperluan = Skck::join('satwil', 'satwil.eskck_id', '=', 'eskck.id')
            ->where('eskck.user_id', '=', Auth::user()->id)
            ->where('eskck.send', '=', '1')
            ->whereDate('eskck.updated_at', '>', $enamBulanLalu)
            ->select('satwil.keperluan')
            ->distinct()
            ->get();

        return response()->json(['data' => $dataKeperluan, 'message' => 'Keperluan E-SKCK Has Successfully retrive'], 201);
    }

    public function history()
    {
        if (Auth::user()->role == '1') {
            $data = Skck::where('eskck.send', '=', '1')
                ->join('satwil', 'satwil.eskck_id', '=', 'eskck.id')
                ->join('users', 'users.id', '=', 'eskck.user_id')
                ->select('eskck.id as id', 'users.first_name as nama_pertama', 'users.last_name as nama_terakhir', 'eskck.created_at as tanggal_pembuatan', 'eskck.eskck_expire as masa_berlaku', 'eskck.status as status_pembayaran', 'satwil.keperluan as keperluan')
                ->paginate(10);
        } else {
            $data = Skck::where('eskck.user_id', '=', Auth::user()->id)
                ->where('eskck.send', '=', '1')
                ->join('satwil', 'satwil.eskck_id', '=', 'eskck.id')
                ->select('eskck.id as id', 'eskck.created_at as tanggal_pembuatan', 'eskck.eskck_expire as masa_berlaku', 'eskck.status as status_pembayaran', 'satwil.keperluan as keperluan')
                ->paginate(10);
        }

        // $eskck = null;
        // foreach ($data as $key => $item) {
        //     $eskck[] = [
        //         'tanggal_pembuatan' => \Carbon\Carbon::parse($item->tanggal_pembuatan)->isoFormat('DD MMMM Y'),
        //         'masa_berlaku' => \Carbon\Carbon::parse($item->masa_berlaku)->isoFormat('DD MMMM Y'),
        //         'status_pembayaran' => $item->status_pembayaran,
        //         'keperluan' => $item->keperluan,
        //     ];
        // }

        return response()->json(['data' => $data, 'message' => 'E-SKCK Data Has Successfully retrive'], 201);
    }

    protected function lokasi($lokasi)
    {
        // return $lokasi != null;
        if ($lokasi != null) {
            $lokasiJson = json_decode($lokasi);
            $data = json_decode(json_encode([
                'provinsi' => $lokasiJson->provinsi->nama,
                'kabupaten' => $lokasiJson->kota->nama,
                'kecamatan' => $lokasiJson->kecamatan->nama,
                'Kelurahan' => $lokasiJson->kelurahan->nama,
            ]));
        } else {
            $data = json_decode(json_encode([
                'provinsi' => '-',
                'kabupaten' => '-',
                'kecamatan' => '-',
                'Kelurahan' => '-',
            ]));
        }
        return $data;
    }

    protected function getNameByJson($name)
    {
        if ($name != null) {
            $json = json_decode($name);

            return $json->nama;
        } else {
            return '-';
        }
    }

    protected function lampiranUrl($id)
    {
        $lampiran = Lampiran::where('eskck_id', '=', $id)->first();

        if (File::exists(public_path('images/skck/' . $lampiran->foto))) {
            $lampiran->foto = env('API_DOMAIN') . '/images/skck/' . $lampiran->foto;
        } else {
            $lampiran->foto = env('WEB_DOMAIN') . '/images/skck/' . $lampiran->foto;
        }

        if (File::exists(public_path('images/skck/' . $lampiran->ktp))) {
            $lampiran->ktp = env('API_DOMAIN') . '/images/skck/' . $lampiran->ktp;
        } else {
            $lampiran->ktp = env('WEB_DOMAIN') . '/images/skck/' . $lampiran->ktp;
        }

        if (File::exists(public_path('images/skck/' . $lampiran->kk))) {
            $lampiran->kk = env('API_DOMAIN') . '/images/skck/' . $lampiran->kk;
        } else {
            $lampiran->kk = env('WEB_DOMAIN') . '/images/skck/' . $lampiran->kk;
        }

        if (File::exists(public_path('images/skck/' . $lampiran->akte_ijazah))) {
            $lampiran->akte_ijazah = env('API_DOMAIN') . '/images/skck/' . $lampiran->akte_ijazah;
        } else {
            $lampiran->akte_ijazah = env('WEB_DOMAIN') . '/images/skck/' . $lampiran->akte_ijazah;
        }

        if ($lampiran->paspor != null) {
            if (File::exists(public_path('images/skck/' . $lampiran->paspor))) {
                $lampiran->paspor = env('API_DOMAIN') . '/images/skck/' . $lampiran->paspor;
            } else {
                $lampiran->paspor = env('WEB_DOMAIN') . '/images/skck/' . $lampiran->paspor;
            }
        } else {
            $lampiran->paspor = null;
        }

        if ($lampiran->sidik_jari != null) {
            if (File::exists(public_path('images/skck/' . $lampiran->sidik_jari))) {
                $lampiran->sidik_jari = env('API_DOMAIN') . '/images/skck/' . $lampiran->sidik_jari;
            } else {
                $lampiran->sidik_jari = env('WEB_DOMAIN') . '/images/skck/' . $lampiran->sidik_jari;
            }
        } else {
            $lampiran->sidik_jari = null;
        }

        return $lampiran;
    }

    public function detail($eskck_id)
    {
        $skck = Skck::find($eskck_id);

        if (Auth::user()->id == $skck->user_id || Auth::user()->role) {
            $satwil = Satwil::where('eskck_id', '=', $skck->id)->first();
            $dataPribadi = DataPribadi::where('dp_eskck_id', '=', $skck->id)->first();

            $keluarga = Keluarga::where('eskck_id', '=', $skck->id)->first();
            $keluarga->hub_lokasi = $this->lokasi($keluarga->hub_lokasi);
            $keluarga->ayah_lokasi = $this->lokasi($keluarga->ayah_lokasi);
            $keluarga->ibu_lokasi = $this->lokasi($keluarga->ibu_lokasi);

            $pendidikan = Pendidikan::where('eskck_id', '=', $skck->id)->first();
            $pendidikan->sd_provinsi = $this->getNameByJson($pendidikan->sd_provinsi);
            $pendidikan->sd_kota = $this->getNameByJson($pendidikan->sd_kota);
            $pendidikan->smp_provinsi = $this->getNameByJson($pendidikan->smp_provinsi);
            $pendidikan->smp_kota = $this->getNameByJson($pendidikan->smp_kota);
            $pendidikan->sma_provinsi = $this->getNameByJson($pendidikan->sma_provinsi);
            $pendidikan->sma_kota = $this->getNameByJson($pendidikan->sma_kota);
            $pendidikan->perguruan_provinsi = $this->getNameByJson($pendidikan->perguruan_provinsi);
            $pendidikan->perguruan_kota = $this->getNameByJson($pendidikan->perguruan_kota);
            $pidana = Pidana::where('eskck_id', '=', $skck->id)->first();
            $fisik = Fisik::where('eskck_id', '=', $skck->id)->first();
            // $lampiran = Lampiran::where('eskck_id', '=', $skck->id)->first();
            $lampiran = $this->lampiranUrl($skck->id);
            $keterangan = Keterangan::where('eskck_id', '=', $skck->id)->first();

            // return $keluarga;

            $data = [
                'satwil' => $satwil,
                'dataPribadi' => $dataPribadi,
                'keluarga' => $keluarga,
                'pendidikan' => $pendidikan,
                'pidana' => $pidana,
                'fisik' => $fisik,
                'lampiran' => $lampiran,
                'keterangan' => $keterangan,
            ];
            return response()->json(["data" => $data, "message" => "SKCK detail successfully retrive"], 201);
        } else {
            return response()->json(["message" => "You can't access this data"], 401);
        }
    }

    public function save(Request $request)
    {
        $validator = $this->validationInput($request);

        if ($validator->passes()) {
            $eskck = $this->eskckData();

            $this->saveSatwil($request, $eskck->id);
            $this->saveDataPribadi($request, $eskck->id);
            $this->saveKeluarga($request, $eskck->id);
            $this->savePendidikan($request, $eskck->id);
            $this->savePidana($request, $eskck->id);
            $this->saveFisik($request, $eskck->id);
            $this->saveLampiran($request, $eskck->id);
            $this->saveKeterangan($request, $eskck->id);

            return response()->json(['data' => $eskck, 'message' => 'E-SKCK Form Successfully Saved'], 200);
        } else {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }
    }

    protected function eskckData()
    {
        $eskck = new Skck();
        $eskck->user_id = Auth::user()->id;
        $eskck->send = '1';
        $eskck->status = 'Belum Dibayar';
        $eskck->save();

        return $eskck;
    }

    protected function saveKeterangan($request, $eskck_id)
    {
        $keterangan = new Keterangan();

        $keterangan->eskck_id = $eskck_id;
        $keterangan->riwayat = $request->riwayat;
        $keterangan->hobi = $request->hobi;
        $keterangan->alamat_telp = $request->alamat_telp;
        $keterangan->email = $request->email;
        $keterangan->sponsor = $request->sponsor;
        $keterangan->alamat_sponsor = $request->alamat_sponsor;
        $keterangan->telp_sponsor = $request->telp_sponsor;
        $keterangan->usaha_sponsor = $request->usaha_sponsor;

        $keterangan->save();
    }

    protected function saveLampiran($request, $eskck_id)
    {
        $i = 1;
        $path = public_path() . '/images/skck';
        $foto = 'foto' . time() . $i . '.' . $request->foto->getClientOriginalExtension();
        $request->foto->move($path, $foto);
        $ktp = 'ktp' . time() . $i . '.' . $request->ktp->getClientOriginalExtension();
        $request->ktp->move($path, $ktp);

        if ($request->hasFile('paspor')) {
            $paspor = 'paspor' . time() . $i . '.' . $request->paspor->getClientOriginalExtension();
            $request->paspor->move($path, $paspor);
        } else {
            $paspor = null;
        }

        $kk = 'kk' . time() . $i . '.' . $request->kk->getClientOriginalExtension();
        $request->kk->move($path, $kk);
        $akte_ijazah = 'akte_ijazah' . time() . $i . '.' . $request->akte_ijazah->getClientOriginalExtension();
        $request->akte_ijazah->move($path, $akte_ijazah);
        if ($request->hasFile('sidik_jari')) {
            $sidik_jari = 'sidik_jari' . time() . $i . '.' . $request->sidik_jari->getClientOriginalExtension();
            $request->sidik_jari->move($path, $sidik_jari);
        } else {
            $sidik_jari = null;
        }

        $lampiran = new Lampiran();
        $lampiran->eskck_id = $eskck_id;
        $lampiran->foto = $foto;
        $lampiran->ktp = $ktp;
        $lampiran->paspor = $paspor;
        $lampiran->kk = $kk;
        $lampiran->akte_ijazah = $akte_ijazah;
        $lampiran->sidik_jari = $sidik_jari;
        $lampiran->save();
    }

    protected function saveFisik($request, $eskck_id)
    {
        $fisik = new Fisik();

        $fisik->eskck_id = $eskck_id;
        $fisik->rambut = $request->rambut;
        $fisik->wajah = $request->wajah;
        $fisik->kulit = $request->kulit;
        $fisik->tinggi_badan = $request->tinggi_badan;
        $fisik->berat_badan = $request->berat_badan;
        $fisik->tanda_istimewa = $request->tanda_istimewa;
        $fisik->jari_kiri = $request->sidik_jari_kiri;
        $fisik->jari_kanan = $request->sidik_jari_kanan;

        $fisik->save();
    }

    protected function savePidana($request, $eskck_id)
    {
        $pidana = new Pidana();

        $pidana->eskck_id = $eskck_id;
        $pidana->pernah_pidana = $request->perkara_pidana;
        $pidana->pidana_apa = $request->pidana_apa;
        $pidana->putusan_pidana = $request->putusan_pidana;
        $pidana->saat_ini_pidana = $request->saat_ini_pidana;
        $pidana->sampai_mana_pidana = $request->sampai_mana_pidana;
        $pidana->pernah_pelanggaran = $request->pelanggaran_hukum;
        $pidana->pelanggaran_apa = $request->pelanggaran_apa;
        $pidana->sampai_mana_pelanggaran = $request->sampai_mana_pelanggaran;

        $pidana->save();
    }

    protected function savePendidikan($request, $eskck_id)
    {
        $pendidikan = new Pendidikan();

        $pendidikan->eskck_id = $eskck_id;
        $pendidikan->sd_nama = $request->sd_nama;
        $pendidikan->sd_provinsi = $this->getProvinsiById($request->sd_provinsi) == '' ? null : json_encode($this->getProvinsiById($request->sd_provinsi));
        $pendidikan->sd_kota = $this->getKotaById($request->sd_kota) == '' ? null : json_encode($this->getKotaById($request->sd_kota));
        $pendidikan->sd_tahun = $request->sd_tahun;
        $pendidikan->smp_nama = $request->smp_nama;
        $pendidikan->smp_provinsi = $this->getProvinsiById($request->smp_provinsi) == '' ? null : json_encode($this->getProvinsiById($request->smp_provinsi));
        $pendidikan->smp_kota = $this->getKotaById($request->smp_kota) == '' ? null : json_encode($this->getKotaById($request->smp_kota));
        $pendidikan->smp_tahun = $request->smp_tahun;
        $pendidikan->sma_nama = $request->sma_nama;
        $pendidikan->sma_provinsi = $this->getProvinsiById($request->sma_provinsi) == '' ? null : json_encode($this->getProvinsiById($request->sma_provinsi));
        $pendidikan->sma_kota = $this->getKotaById($request->sma_kota) == '' ? null : json_encode($this->getKotaById($request->sma_kota));
        $pendidikan->sma_tahun = $request->sma_tahun;
        $pendidikan->perguruan_nama = $request->perguruan_nama;
        $pendidikan->perguruan_provinsi = $this->getProvinsiById($request->perguruan_provinsi) == '' ? null : json_encode($this->getProvinsiById($request->perguruan_provinsi));
        $pendidikan->perguruan_kota = $this->getKotaById($request->perguruan_kota) == '' ? null : json_encode($this->getKotaById($request->perguruan_kota));
        $pendidikan->perguruan_tahun = $request->perguruan_tahun;

        $pendidikan->save();
    }

    protected function saveKeluarga($request, $eskck_id)
    {
        $keluarga = new Keluarga();
        $keluarga->eskck_id = $eskck_id;
        if ($request->status_perkawinan_data_pribadi == 'Kawin') {
            $hubLokasi = $this->lokasiJson($request->hubungan_provinsi, $request->hubungan_kota, $request->hubungan_kecamatan, $request->hubungan_kelurahan);
            $keluarga->hub_type = $request->hubungan_type;
            $keluarga->hub_nama = $request->hubungan_nama;
            $keluarga->hub_umur = $request->hubungan_umur;
            $keluarga->hub_agama = $request->hubungan_agama;
            $keluarga->hub_kewarganegaraan = $request->hubungan_kewarganegaraan;
            $keluarga->hub_pekerjaan = $request->hubungan_pekerjaan;
            $keluarga->hub_alamat = $request->hubungan_alamat;
            $keluarga->hub_lokasi = $hubLokasi;
        }

        $ayahLokasi = $this->lokasiJson($request->ayah_provinsi, $request->ayah_kota, $request->ayah_kecamatan, $request->ayah_kelurahan);
        $ibuLokasi = $this->lokasiJson($request->ibu_provinsi, $request->ibu_kota, $request->ibu_kecamatan, $request->ibu_kelurahan);

        $keluarga->ayah_nama = $request->ayah_nama;
        $keluarga->ayah_umur = $request->ayah_umur;
        $keluarga->ayah_agama = $request->ayah_agama;
        $keluarga->ayah_kewarganegaraan = $request->ayah_kewarganegaraan;
        $keluarga->ayah_pekerjaan = $request->ayah_pekerjaan;
        $keluarga->ayah_alamat = $request->ayah_alamat;
        $keluarga->ayah_lokasi = $ayahLokasi;
        $keluarga->ibu_nama = $request->ibu_nama;
        $keluarga->ibu_umur = $request->ibu_umur;
        $keluarga->ibu_agama = $request->ibu_agama;
        $keluarga->ibu_kewarganegaraan = $request->ibu_kewarganegaraan;
        $keluarga->ibu_pekerjaan = $request->ibu_pekerjaan;
        $keluarga->ibu_alamat = $request->ibu_alamat;
        $keluarga->ibu_lokasi = $ibuLokasi;

        $keluarga->save();
    }

    protected function saveDataPribadi($request, $eskck_id)
    {

        $provinsi = $this->getProvinsiById($request->provinsi_data_pribadi);
        $kota = $this->getKotaById($request->kota_data_pribadi);
        $kecamatan = $this->getKecamatanById($request->kecamatan_data_pribadi);
        $kelurahan = $this->getKelurahanById($request->kelurahan_data_pribadi);

        $dataPribadi = new DataPribadi();
        $dataPribadi->dp_eskck_id = $eskck_id;
        $dataPribadi->dp_nama_lengkap = $request->nama_lengkap_data_pribadi;
        $dataPribadi->dp_tempat_lahir = $request->tempat_lahir_data_pribadi;
        $dataPribadi->dp_tanggal_lahir = $request->tanggal_lahir_data_pribadi;
        $dataPribadi->dp_agama = $request->agama_data_pribadi;
        $dataPribadi->dp_jenis_kelamin = $request->jenis_kelamin_data_pribadi;
        $dataPribadi->dp_status_perkawinan = $request->status_perkawinan_data_pribadi;
        $dataPribadi->dp_kewarganegaraan = $request->kewarganegaraan_data_pribadi;
        $dataPribadi->dp_alamat = $request->alamat_data_pribadi;
        $dataPribadi->dp_provinsi =  $provinsi->nama;
        $dataPribadi->dp_kabupaten = $kota->nama;
        $dataPribadi->dp_kecamatan = $kecamatan->nama;
        $dataPribadi->dp_kelurahan = $kelurahan->nama;
        $dataPribadi->dp_no_ktp = $request->no_ktp_data_pribadi;
        $dataPribadi->dp_no_sim = $request->no_sim_data_pribadi;
        $dataPribadi->dp_no_paspor = $request->no_paspor_data_pribadi;
        $dataPribadi->save();
    }


    protected function saveSatwil($request, $eskck_id)
    {
        $kecamatan = $this->getKecamatanById($request->kecamatan_satwil);
        $kelurahan = $this->getKelurahanById($request->kelurahan_satwil);

        $satwil = new Satwil();
        $satwil->eskck_id = $eskck_id;
        $satwil->keperluan = $request->keperluan_satwil;
        $satwil->polda = 'POLDA BANTEN';
        $satwil->polres = 'Polres Kota Tangerang';
        $satwil->polsek = $request->polsek_satwil;
        $satwil->alamat = $request->alamat_satwil;
        $satwil->provinsi = 'BANTEN';
        $satwil->kota = 'Kabupaten Tangerang';
        $satwil->kecamatan = $kecamatan->nama;
        $satwil->kelurahan = $kelurahan->nama;
        $satwil->save();
    }

    protected function getProvinsiById($provinsi_id)
    {

        if ($provinsi_id != null) {
            $provinsiRes = Http::get('https://dev.farizdotid.com/api/daerahindonesia/provinsi/' . $provinsi_id);
            $provinsi = json_decode($provinsiRes);
            return $provinsi;
        } else {
            return '';
        }
    }

    protected function getKotaById($kota_id)
    {
        if ($kota_id != null) {
            $kotaRes = Http::get('https://dev.farizdotid.com/api/daerahindonesia/kota/' . $kota_id);
            $kota = json_decode($kotaRes);

            return $kota;
        } else {
            return '';
        }
    }

    protected function getKecamatanById($kecamatan_id)
    {
        $kecamatanRes = Http::get('https://dev.farizdotid.com/api/daerahindonesia/kecamatan/' . $kecamatan_id);
        $kecamatan = json_decode($kecamatanRes);

        return $kecamatan;
    }

    protected function getKelurahanById($kelurahan_id)
    {
        $kelurahanRes = Http::get('https://dev.farizdotid.com/api/daerahindonesia/kelurahan/' . $kelurahan_id);
        $kelurahan = json_decode($kelurahanRes);

        return $kelurahan;
    }

    protected function lokasiJson($provinsi_id, $kota_id, $kecamatan_id, $kelurahan_id)
    {
        $provinsi = $this->getProvinsiById($provinsi_id);
        $kota = $this->getKotaById($kota_id);
        $kecamatan = $this->getKecamatanById($kecamatan_id);
        $kelurahan = $this->getKelurahanById($kelurahan_id);

        $lokasi = [
            'provinsi' => $provinsi,
            'kota' => $kota,
            'kecamatan' => $kecamatan,
            'kelurahan' => $kelurahan,
        ];

        return json_encode($lokasi);
    }

    protected function validationInput($request)
    {

        $validation = [
            'keperluan_satwil' => 'required',
            'polsek_satwil' => 'required',
            'alamat_satwil' => 'required',
            'kecamatan_satwil' => 'required',
            'kelurahan_satwil' => 'required',

            'nama_lengkap_data_pribadi' => 'required',
            'tempat_lahir_data_pribadi' => 'required',
            'tanggal_lahir_data_pribadi' => 'required',
            'agama_data_pribadi' => 'required',
            'jenis_kelamin_data_pribadi' => 'required',
            'status_perkawinan_data_pribadi' => 'required',
            'kewarganegaraan_data_pribadi' => 'required',
            'alamat_data_pribadi' => 'required',
            'provinsi_data_pribadi' => 'required',
            'kota_data_pribadi' => 'required',
            'kecamatan_data_pribadi' => 'required',
            'kelurahan_data_pribadi' => 'required',
            'no_ktp_data_pribadi' => 'required',

            'ayah_nama' => 'required',
            'ayah_umur' => 'required',
            'ayah_agama' => 'required',
            'ayah_kewarganegaraan' => 'required',
            'ayah_pekerjaan' => 'required',
            'ayah_alamat' => 'required',
            'ayah_provinsi' => 'required',
            'ayah_kota' => 'required',
            'ayah_kecamatan' => 'required',
            'ayah_kelurahan' => 'required',
            'ibu_nama' => 'required',
            'ibu_umur' => 'required',
            'ibu_agama' => 'required',
            'ibu_kewarganegaraan' => 'required',
            'ibu_pekerjaan' => 'required',
            'ibu_alamat' => 'required',
            'ibu_provinsi' => 'required',
            'ibu_kota' => 'required',
            'ibu_kecamatan' => 'required',
            'ibu_kelurahan' => 'required',

            'perkara_pidana' => 'required',
            'pelanggaran_hukum' => 'required',

            'rambut' => 'required',
            'wajah' => 'required',
            'kulit' => 'required',
            'tinggi_badan' => 'required',
            'berat_badan' => 'required',

            'foto' => 'required|mimes:jpeg,jpg,png',
            'ktp' => 'required|mimes:jpeg,jpg,png',
            'kk' => 'required|mimes:jpeg,jpg,png',
            'akte_ijazah' => 'required|mimes:jpeg,jpg,png',

            'riwayat' => 'required',
            'hobi' => 'required',
            'email' => 'required',
        ];

        if ($request->status_perkawinan_data_pribadi == 'Kawin') {
            $hub = [
                'hubungan_type' => 'required',
                'hubungan_nama' => 'required',
                'hubungan_umur' => 'required',
                'hubungan_agama' => 'required',
                'hubungan_kewarganegaraan' => 'required',
                'hubungan_pekerjaan' => 'required',
                'hubungan_alamat' => 'required',
                'hubungan_provinsi' => 'required',
                'hubungan_kota' => 'required',
                'hubungan_kecamatan' => 'required',
                'hubungan_kelurahan' => 'required',
            ];
            $validation = array_merge($validation, $hub);
        }

        // dd($validation);

        $validator = Validator::make($request->all(), $validation);

        return $validator;
    }
}
