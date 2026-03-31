<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="Tag",
 *     type="object",
 *     required={"id", "tag_slug", "tag_name", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="tag_slug", type="string", description="Slug of the tag"),
 *     @OA\Property(property="tag_name", type="string", description="Name of the tag"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the tag was created"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the tag")
 * )
 */

class Tag extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'tag';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'tag_slug', 'tag_name', 'created_at', 'created_by'];

    public static function findAllTag($user_id = null, $paginate) {
        $select = $user_id ? "id, tag_name, tag_slug, created_at" : "tag_name, tag_slug";
        $res = Tag::selectRaw($select);

        if ($user_id) $res = $res->where('created_by', $user_id);

        return $res->orderby($user_id ? 'created_at' : 'tag_name', $user_id ? 'desc' : 'asc')->paginate($paginate);
    }

    public static function getRandom($null) {
        return $null === 0 ? Tag::inRandomOrder()->first() : null;
    }

    public static function createTag($data, $user_id) {
        $data['tag_slug'] = Generator::getSlug($data['tag_name'], 'tag'); 
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;
        $data['id'] = Generator::getUUID();
            
        return Tag::create($data);
    }

    public static function deleteTagById($user_id, $id) {
        return Tag::where('created_by', $user_id)->where('id', $id)->delete();
    }
}
