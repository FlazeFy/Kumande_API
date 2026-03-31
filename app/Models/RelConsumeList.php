<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Models
use App\Models\Payment;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="RelConsumeList",
 *     type="object",
 *     required={"id", "consume_id", "list_id", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="consume_id", type="string", format="uuid", description="Consume ID"),
 *     @OA\Property(property="list_id", type="string", format="uuid", description="List ID"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the relation was created"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the relation")
 * )
 */

class RelConsumeList extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'rel_consume_list';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'consume_id', 'list_id', 'created_at', 'created_by'];

    public static function isRelConsumeListExist($consume_slug, $list_id) {
        return RelConsumeList::join('consume', 'consume.id', '=', 'rel_consume_list.consume_id')
            ->where('slug_name', $consume_slug)
            ->where('list_id', $list_id)
            ->exists();
    }

    public static function findRelConsumeList($user_id, $consume_list_id) {
        $res = RelConsumeList::selectRaw("
                consume.id as consume_id, rel_consume_list.id, consume.slug_name, consume_name, consume_type, 
                CAST(REPLACE(JSON_EXTRACT(consume_detail, '$[0].calorie'), '\"', '') as unsigned) as calorie, 
                REPLACE(JSON_EXTRACT(consume_detail, '$[0].provide'), '\"', '') as provide, 
                consume_from
            ")
            ->join('consume','consume.id','=','rel_consume_list.consume_id')
            ->where('rel_consume_list.created_by', $user_id)
            ->where('list_id', $consume_list_id)
            ->get();

        foreach($res as $idx => $dt) {
            $pyt = Payment::selectRaw('CAST(AVG(payment_price) as unsigned) as average_price')
                ->where('consume_id', $dt->consume_id)
                ->groupby('consume_id')
                ->first();

            $res[$idx]->average_price = $pyt ? $pyt->average_price : null;
        }

        return $res;
    }
    
    public static function createRelConsumeList($data, $user_id) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;
        $data['id'] = Generator::getUUID();
            
        return RelConsumeList::create($data);
    }

    public static function deleteRelConsumeListByContextId($user_id, $context_id, $context_col) {
        return RelConsumeList::where('created_by', $user_id)->where($context_col, $context_id)->delete();
    }
}
