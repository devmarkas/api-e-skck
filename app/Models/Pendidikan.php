<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendidikan extends Model{
    protected $table = "pendidikan";

    protected $fillable = [
        'eskck_id',
        'sd_nama',
        'sd_provinsi',
        'sd_kota',
        'sd_tahun',
        'smp_nama',
        'smp_provinsi',
        'smp_kota',
        'smp_tahun',
        'sma_nama',
        'sma_provinsi',
        'sma_kota',
        'sma_tahun',
        'perguruan_nama',
        'perguruan_provinsi',
        'perguruan_kota',
        'perguruan_tahun',
    ];

    // public $timestamps = false;
}