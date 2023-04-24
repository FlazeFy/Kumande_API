<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumeList extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'consume_list';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'slug_name', 'list_name', 'list_desc', 'list_tag', 'created_at', 'created_by', 'updated_at', 'updated_by'];
    protected $casts = [
        'list_tag' => 'array'
    ];

    public static function getAvailableListName($check, $id){
        $csl = ConsumeList::select('list_name')
            ->where('created_by', $id)
            ->where('list_name', $check)
            ->get();
        
        if(count($csl) > 0){
            $res = false;
        } else {
            $res = true;
        }

        return $res;
    }
}
