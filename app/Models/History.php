<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'history';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'history_type', 'history_context', 'created_at', 'created_by'];

    public static function getAllHistory($type, $user_id, $paginate) {
        $select_query = $type === "admin" ? 'history.id, username, history_type, history_context, history.created_at' : '*';
        
        $res = History::selectRaw($select_query);
        if ($type === "admin") $res = $res->join('users','users.id','=','history.created_by');
        if ($type === "user" || $user_id) $res = $res->where('created_by',$user_id);
        
        return $res->orderby('history.created_at', 'DESC')->paginate($paginate);
    }
}
