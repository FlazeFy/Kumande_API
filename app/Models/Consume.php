<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consume extends Model
{
    use HasFactory;

    protected $table = 'consume';
    protected $primaryKey = 'consume_id';
    protected $fillable = ['consume_id', 'consume_type', 'consume_name', 'consume_from', 'consume_payment', 'consume_favorite', 'consume_comment', 'consume_createdAt', 'consume_updatedAt'];
}
