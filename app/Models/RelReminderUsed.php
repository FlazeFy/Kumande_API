<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelReminderUsed extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'rel_reminder_used';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'reminder_id', 'user_id', 'created_at'];
}
