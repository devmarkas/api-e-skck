<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pidana extends Model{
    protected $table = "perkara_pidana";

    protected $fillable = [
        'eskck_id',
        'pernah_pidana',
        'pidana_apa',
        'putusan_pidana',
        'saat_ini_pidana',
        'pernah_pelanggaran',
        'pelanggaran_apa',
        'sampai_mana_pelanggaran',
        'sampai_mana_pidana',
    ];

    // public $timestamps = false;
}