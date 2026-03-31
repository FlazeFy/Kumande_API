<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="BodyInfo",
 *     type="object",
 *     required={"id", "blood_pressure", "blood_glucose", "gout", "cholesterol", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="blood_pressure", type="string", description="SmartDoc Health Monitoring"),
 *     @OA\Property(property="blood_glucose", type="string", description="SmartDoc Health Monitoring"),
 *     @OA\Property(property="gout", type="string", description="SmartDoc Health Monitoring"),
 *     @OA\Property(property="cholesterol", type="string", description="SmartDoc Health Monitoring"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the body info was created"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the body info")
 * )
 */

class BodyInfo extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'body_info';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'blood_pressure', 'blood_glucose', 'gout', 'cholesterol', 'created_at', 'created_by'];

    public static function findLatestBodyInfo($user_id) {
        return BodyInfo::select('blood_pressure', 'blood_glucose', 'gout', 'cholesterol', 'created_at')
            ->where('created_by', $user_id)
            ->orderby('created_at', 'desc')
            ->first();
    }

    public static function findAllBodyInfo($user_id) {
        return BodyInfo::select('id', 'blood_pressure', 'blood_glucose', 'gout', 'cholesterol', 'created_at')
            ->where('created_by', $user_id)
            ->orderby('created_at', 'desc')
            ->get();
    }

    public static function findAllMaxMinBodyInfo($user_id) {
        return BodyInfo::selectRaw('
                MAX(blood_glucose) as max_blood_glucose, MIN(blood_glucose) as min_blood_glucose,
                MAX(gout) as max_gout, MIN(gout) as min_gout,
                MAX(cholesterol) as max_cholesterol, MIN(cholesterol) as min_cholesterol,
                AVG(CAST(SUBSTRING_INDEX(blood_pressure, "/", 1) AS UNSIGNED)) as avg_systolic,
                AVG(CAST(SUBSTRING_INDEX(blood_pressure, "/", -1) AS UNSIGNED)) as avg_diastolic
            ')
            ->where('created_by', $user_id)
            ->first();
    }

    public static function createBodyInfo($data, $user_id) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $user_id;
        $data['id'] = Generator::getUUID();
            
        return BodyInfo::create($data);
    }

    public static function deleteBodyInfoById($user_id, $id) {
        return BodyInfo::where('created_by', $user_id)->where('id', $id)->delete();
    }
}
