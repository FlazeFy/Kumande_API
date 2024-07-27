<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Schema(
 *     schema="CountCalorie",
 *     type="object",
 *     required={"id", "firebase_id", "weight", "height", "result", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="firebase_id", type="string", description="Firebase Firestore Doc ID"),
 *     @OA\Property(property="weight", type="integer", description="Weight of user in kg"),
 *     @OA\Property(property="height", type="integer", description="Height of user in cm"),
 *     @OA\Property(property="result", type="integer", description="Daily calorie needed for the user in cal, can input manually or count in the FE"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the count calorie was created"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the count calorie"),
 * )
 */

class CountCalorie extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $table = 'count_calorie';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = ['id', 'firebase_id', 'weight', 'height', 'result', 'created_at', 'created_by'];

}
