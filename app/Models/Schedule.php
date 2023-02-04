<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'schedule';
    protected $primaryKey = 'schedule_id';
    protected $fillable = ['schedule_id', 'schedule_consume', 'schedule_desc', 'schedule_tag', 'schedule_time', 'created_at', 'updated_at'];
}
