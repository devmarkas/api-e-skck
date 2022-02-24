<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lampiran extends Model{
    protected $table = "lampiran";

    protected $fillable = [
        'eskck_id',
        'foto',
        'ktp',
        'paspor',
        'kk',
        'akte_ijazah',
        'sidik_jari',
    ];

    // public $timestamps = false;
}