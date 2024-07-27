<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Tag",
 *     type="object",
 *     required={"id", "tag_slug", "tag_name", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
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
    protected $fillable = ['id', 'tag_slug', 'tag_name', 'created_at', 'created_by'];
}
