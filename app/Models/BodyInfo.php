<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodyInfo extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'body_info';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'blood_pressure', 'blood_glucose', 'gout', 'cholesterol', 'created_at', 'created_by'];
}
