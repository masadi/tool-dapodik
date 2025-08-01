<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    protected $connection = 'dapodik';
	protected $table = 'audit.logged_actions';
	protected $primaryKey = 'event_id';
}
