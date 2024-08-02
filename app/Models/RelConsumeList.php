<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
