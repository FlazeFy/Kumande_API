<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;
    use HasUuids;
    public $incrementing = false;

    protected $table = 'budget';
    protected $primaryKey = 'id';
    protected $fillable = ['budget_code', 'budget_total', 'budget_month_year', 'budget_over', 'budget_status', 'created_at', 'updated_at', 'achieve_at'];
    protected $casts = [
        'budget_month_year' => 'array',
        'budget_status' => 'array'
    ];
}
