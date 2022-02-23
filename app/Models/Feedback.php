<?php   
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model{
    protected $table = "feedback";

    protected $fillable = [
        'user_id',
        'ulasan',
        'rating'
    ];

    // public $timestamps = false;
}