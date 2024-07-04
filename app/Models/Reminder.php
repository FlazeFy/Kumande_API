<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'reminder';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'reminder_name', 'reminder_type', 'reminder_context', 'reminder_body', 'reminder_attachment', 'created_at', 'created_by'];
    protected $casts = [
        'reminder_context' => 'array',
        'reminder_attachment' => 'array'
    ];
}
