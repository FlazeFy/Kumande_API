<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'schedule';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'schedule_code', 'schedule_consume', 'schedule_desc', 'schedule_tag', 'schedule_time', 'created_at', 'created_by', 'updated_at', 'updated_by'];
    protected $casts = [
        'schedule_tag' => 'array',
        'schedule_time' => 'array'
    ];
}
