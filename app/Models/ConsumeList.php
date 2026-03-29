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
 * )
 */

class ConsumeList extends Model
{
    use HasFactory;
    public $incrementing = false;
    protected $table = 'consume_list';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'slug_name', 'list_name', 'list_desc', 'list_tag', 'created_at', 'created_by', 'updated_at'];
    protected $casts = [
        'list_tag' => 'array'
    ];

    public static function getAvailableListName($check, $id) {
        return !ConsumeList::where('created_by', $id)->where('list_name', $check)->exists();
    }

    public static function getRandom($user_id) {
        return ConsumeList::where('created_by',$user_id)->inRandomOrder()->first();
    }

    public static function createConsumeList($data, $user_id) {
        $data['slug_name'] = Generator::getSlug($data['list_name'], "consume_list");
        $data['created_at'] = $data['created_at'] ?? date("Y-m-d H:i:s");
        $data['updated_at'] = null;
        $data['created_by'] = $user_id;
        $data['id'] = Generator::getUUID();
            
        return ConsumeList::create($data);
    }

    public static function updateConsumeListById($data, $user_id, $id) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return ConsumeList::where('id', $id)
            ->where('created_by', $user_id)
            ->update($data);
    }

    public static function deleteConsumeListById($user_id, $id) {
        return ConsumeList::where('created_by', $user_id)->where('id', $id)->delete();
    }
}
