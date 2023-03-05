<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Consume extends Model
{
    use HasFactory;
    use HasUuids;
    public $incrementing = false;

    protected $table = 'consume';
    protected $primaryKey = 'id';
    protected $fillable = ['consume_code', 'consume_type', 'consume_name', 'consume_from', 'consume_payment', 'consume_favorite', 'consume_tag', 'consume_comment', 'created_at', 'updated_at'];
    protected $casts = [
        'consume_name' => 'array',
        'consume_payment' => 'array',
        'consume_tag' => 'array'
    ];
}
