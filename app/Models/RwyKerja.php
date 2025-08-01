<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RwyKerja extends Model
{
    protected $connection = 'dapodik';
    public $incrementing = false;
    public $timestamps = false;
    public $keyType = 'string';
    protected $table = 'rwy_kerja';
	protected $primaryKey = 'rwy_kerja_id';
    protected $guarded = [];
}
