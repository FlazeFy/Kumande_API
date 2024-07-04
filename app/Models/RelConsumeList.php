<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelConsumeList extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'rel_consume_list';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'consume_id', 'list_id', 'created_at', 'created_by'];
}
