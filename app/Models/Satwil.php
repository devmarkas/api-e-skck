<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satwil extends Model{
    protected $table = "satwil";

    protected $fillable = [
        'eskck_id',
        'keperluan',
        'polda',
        'polres',
        'polsek',
        'alamat',
        'provinsi',
        'kota',
        'kecamatan',
        'kelurahan',
    ];

    // public $timestamps = false;
}