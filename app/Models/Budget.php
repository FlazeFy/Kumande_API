<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'budget';
    protected $primaryKey = 'id';
    protected $fillable = ['id', 'firebase_id', 'budget_total', 'budget_month_year', 'budget_over', 'is_over', 'created_at', 'created_by', 'updated_at', 'updated_by', 'over_at', 'deleted_at', 'deleted_by'];
    protected $casts = [
        'budget_month_year' => 'array',
    ];
}
