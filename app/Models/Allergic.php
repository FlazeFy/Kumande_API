<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Allergic extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'allergic';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'allergic_context', 'allergic_desc', 'created_at', 'created_by', 'updated_at'];
}
