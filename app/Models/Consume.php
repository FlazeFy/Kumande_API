<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Consume extends Model
{
    use HasFactory;
    // use HasUuids;
    public $incrementing = false;

    protected $table = 'consume';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'slug_name', 'consume_type', 'consume_name', 'consume_from', 'consume_payment', 'is_favorite', 'consume_tag', 'consume_comment', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by'];
    protected $casts = [
        'consume_detail' => 'array',
        'consume_payment' => 'array',
        'consume_tag' => 'array'
    ];
}
