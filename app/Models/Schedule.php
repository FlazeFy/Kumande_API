<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    use HasUuids;
    public $incrementing = false;

    protected $table = 'schedule';
    protected $primaryKey = 'id';
    protected $fillable = ['schedule_code', 'schedule_consume', 'schedule_desc', 'schedule_tag', 'schedule_time', 'created_at', 'updated_at'];
    protected $casts = [
        'schedule_tag' => 'array',
        'schedule_time' => 'array'
    ];
}
