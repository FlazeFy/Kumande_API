<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'payment';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'consume_id', 'payment_method', 'payment_price', 'is_payment', 'created_at', 'updated_at', 'deleted_at'];
}
