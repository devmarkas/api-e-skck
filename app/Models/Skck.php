<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skck extends Model{
    protected $table = "eskck";

    protected $fillable = [
        'user_id',
        'send',
        'va',
        'expired_date',
        'external_id',
        'bank_code',
        'status',
        'eskck_expire',
    ];

    // public $timestamps = false;
}