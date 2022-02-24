<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fisik extends Model{
    protected $table = "fisik";

    protected $fillable = [
        'eskck_id',
        'rambut',
        'wajah',
        'kulit',
        'tinggi_badan',
        'berat_badan',
        'tanda_istimewa',
        'jari_kiri',
        'jari_kanan',
    ];

    // public $timestamps = false;
}