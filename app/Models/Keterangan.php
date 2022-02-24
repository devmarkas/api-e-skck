<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keterangan extends Model{
    protected $table = "keterangan";

    protected $fillable = [
        'eskck_id',
        'riwayat',
        'hobi',
        'alamat_telp',
        'email',
        'sponsor',
        'alamat_sponsor',
        'telp_sponsor',
        'usaha_sponsor',
    ];

    // public $timestamps = false;
}