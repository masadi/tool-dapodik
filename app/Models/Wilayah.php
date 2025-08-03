<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    public $incrementing = false;
	public $keyType = 'string';
    protected $connection = 'dapodik';
	protected $table = 'ref.mst_wilayah';
	protected $primaryKey = 'kode_wilayah';
}
