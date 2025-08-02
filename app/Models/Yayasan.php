<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Yayasan extends Model
{
    public $incrementing = false;
	public $keyType = 'string';
    protected $connection = 'dapodik';
	protected $table = 'yayasan';
	protected $primaryKey = 'yayasan_id';
    public $timestamps = false;
}
