<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleLog extends Model
{
    protected $fillable = [
        'command_name',
        'status',
        'execution_date',
        'error_message',
    ];
}
