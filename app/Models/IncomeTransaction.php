<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeTransaction extends Model
{
    protected $fillable = ['item_code', 'month', 'year', 'amount'];
}