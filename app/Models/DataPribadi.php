<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPribadi extends Model{
    protected $table = "data_pribadi";

    protected $fillable = [
        'dp_eskck_id',
        'dp_nama_lengkap',
        'dp_tempat_lahir',
        'dp_tanggal_lahir',
        'dp_jenis_kelamin',
        'dp_status_perkawinan',
        'dp_kewarganegaraan',
        'dp_alamat',
        'dp_provinsi',
        'dp_kabupaten',
        'dp_kecamatan',
        'dp_kelurahan',
        'dp_no_ktp',
        'dp_no_sim',
        'dp_no_paspor',
    ];

    // public $timestamps = false;
}