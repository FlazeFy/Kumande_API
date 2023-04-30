<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CountCalorie extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $table = 'count_calorie';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'weight', 'height', 'result', 'created_at', 'created_by', 'deleted_at', 'deleted_by'];

}
