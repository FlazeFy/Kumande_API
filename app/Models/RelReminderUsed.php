<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="RelReminderUsed",
 *     type="object",
 *     required={"id", "reminder_id", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="reminder_id", type="string", format="uuid", description="Reminder ID"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the relation was created"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the relation")
 * )
 */

class RelReminderUsed extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'rel_reminder_used';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'reminder_id', 'created_by', 'created_at'];
}
