<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="ConsumeList",
 *     type="object",
 *     required={"id", "firebase_id", "slug_name", "list_name", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="slug_name", type="string", description="Unique Identifier for consume list from list name"),
 *     @OA\Property(property="list_name", type="string", description="Name of the consume list"),
 *     @OA\Property(property="list_desc", type="string", description="Description of the consume list"),
 *     @OA\Property(property="list_tag", type="json", description="Tags associated with the consume list"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the consume was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the consume was updated"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the consume"),
 *     @OA\Property(property="updated_by", type="string", format="uuid",description="ID of the user who updated the consume")
 * )
 */

class ConsumeList extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'consume_list';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'slug_name', 'list_name', 'list_desc', 'list_tag', 'created_at', 'created_by', 'updated_at', 'updated_by'];
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
