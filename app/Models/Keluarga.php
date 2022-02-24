<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model{
    protected $table = "hub_keluarga";

    protected $fillable = [
        'eskck_id',
        'hub_type',
        'hub_nama',
        'hub_umur',
        'hub_agama',
        'hub_kewarganegaraan',
        'hub_pekerjaan',
        'hub_alamat',
        'hub_lokasi',
        'ayah_nama',
        'ayah_umur',
        'ayah_agama',
        'ayah_kewarganegaraan',
        'ayah_pekerjaan',
        'ayah_alamat',
        'ayah_lokasi',
        'ibu_nama',
        'ibu_umur',
        'ibu_agama',
        'ibu_kewarganegaraan',
        'ibu_pekerjaan',
        'ibu_alamat',
        'ibu_lokasi',
    ];

    // public $timestamps = false;
}