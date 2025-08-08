<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusKepegawaian extends Model
{
    protected $connection = 'dapodik';
	protected $table = 'ref.status_kepegawaian';
	protected $primaryKey = 'status_kepegawaian_id';
}
