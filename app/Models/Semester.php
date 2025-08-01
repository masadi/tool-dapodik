<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $connection = 'dapodik';
	protected $table = 'ref.semester';
	protected $primaryKey = 'semester_id';
}
