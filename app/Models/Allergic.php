<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="Allergic",
 *     type="object",
 *     required={"id", "allergic_context", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="allergic_context", type="string", description="User's Allergic for Consume Analyze"),
 *     @OA\Property(property="allergic_desc", type="string", description="User's Allergic description for Consume Analyze"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the allergic was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the allergic was updated"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the allergic")
 * )
 */

class Allergic extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'allergic';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'allergic_context', 'allergic_desc', 'created_at', 'created_by', 'updated_at'];

    public static function createAllergic($data, $user_id) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = null;
        $data['created_by'] = $user_id;
        $data['id'] = Generator::getUUID();
            
        return Allergic::create($data);
    }

    public static function updateAllergicById($data, $user_id, $id) {
        $data['updated_at'] = date('Y-m-d H:i:s');

        return Allergic::where('id',$id)
            ->where('created_by',$user_id)
            ->update($data);
    }

    public static function deleteAllergicById($user_id, $id) {
        return Allergic::where('created_by', $user_id)->where('id', $id)->delete();
    }
}
