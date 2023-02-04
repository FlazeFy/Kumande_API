<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumeList extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'consume_list';
    protected $primaryKey = 'list_id';
    protected $fillable = ['list_id', 'list_name', 'list_desc', 'list_tag', 'created_at', 'updated_at'];
}
