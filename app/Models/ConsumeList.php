<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ConsumeList extends Model
{
    use HasFactory;
    use HasUuids;
    public $incrementing = false;

    protected $table = 'consume_list';
    protected $primaryKey = 'id';
    protected $fillable = ['list_code', 'list_name', 'list_desc', 'list_tag', 'created_at', 'updated_at'];
    protected $casts = [
        'list_tag' => 'array'
    ];
}
