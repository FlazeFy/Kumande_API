<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Allergic",
 *     type="object",
 *     required={"id", "allergic_context", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
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
    protected $fillable = ['id', 'allergic_context', 'allergic_desc', 'created_at', 'created_by', 'updated_at'];
}
