<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Payment",
 *     type="object",
 *     required={"id", "consume_id", "payment_method", "created_at", "created_by"},
 * 
 *     @OA\Property(property="id", type="string", format="uuid", description="Primary Key"),
 *     @OA\Property(property="consume_id", type="string", description="Consume ID"),
 *     @OA\Property(property="payment_method", type="string", description="Method of the payment"),
 *     @OA\Property(property="payment_price", type="integer", description="Ammount of the consume price in Rupiah"),
 * 
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Timestamp when the payment was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Timestamp when the payment was updated"),
 *     @OA\Property(property="created_by", type="string", format="uuid",description="ID of the user who created the payment"),
 * )
 */

class Payment extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'payment';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'consume_id', 'payment_method', 'payment_price', 'created_at', 'updated_at', 'created_by'];
}
