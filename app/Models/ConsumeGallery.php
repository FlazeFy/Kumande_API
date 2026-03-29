<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Helper
use App\Helpers\Generator;

/**
 * @OA\Schema(
 *     schema="Consume_Gallery",
 *     type="object",
 *     required={"id", "consume_id", "gallery_desc", "created_at"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="consume_id", type="string", format="uuid", description="Consume ID"),
 *     @OA\Property(property="gallery_desc", type="string", description="Description of the gallery"),
 *     @OA\Property(property="gallery_url", type="string", description="Firebase storage downloadable URL for the consume gallery image"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the gallery was created")
 * )
 */

class ConsumeGallery extends Model
{
    use HasFactory;
    public $incrementing = false;
    public $timestamps = false;
    protected $table = 'consume_gallery';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'consume_id', 'gallery_desc', 'gallery_url', 'created_at'];

    public static function createConsumeGallery($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['id'] = Generator::getUUID();
            
        return ConsumeGallery::create($data);
    }

    public static function updateConsumeGalleryById($data, $user_id, $id) {
        return ConsumeGallery::where('consume_gallery.id', $id)
            ->join('consume','consume.id','=','consume_gallery.consume_id')
            ->where('created_by',$user_id)
            ->update($data);
    }
}
