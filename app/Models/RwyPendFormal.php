<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RwyPendFormal extends Model
{
    protected $connection = 'dapodik';
    public $incrementing = false;
    public $timestamps = false;
    protected $guarded = [];
    public $keyType = 'string';
    protected $table = 'rwy_pend_formal';
	protected $primaryKey = 'riwayat_pendidikan_formal_id';
}
