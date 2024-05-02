<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'tag';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'tag_slug', 'tag_name', 'created_at', 'created_by'];
}
