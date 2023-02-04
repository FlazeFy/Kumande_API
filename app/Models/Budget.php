<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $table = 'budget';
    protected $primaryKey = 'budget_id';
    protected $fillable = ['budget_id', 'budget_total', 'budget_month_year', 'budget_over', 'budget_status', 'created_at', 'updated_at', 'achieve_at'];
}
