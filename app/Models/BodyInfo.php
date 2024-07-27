<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="BodyInfo",
 *     type="object",
 *     required={"id", "blood_pressure", "blood_glucose", "gout", "cholesterol", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
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
    protected $fillable = ['id', 'blood_pressure', 'blood_glucose', 'gout', 'cholesterol', 'created_at', 'created_by'];
}
