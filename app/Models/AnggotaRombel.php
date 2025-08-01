<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AnggotaRombel extends Model
{
    use HasUuids;
    protected $connection = 'dapodik';
	protected $table = 'anggota_rombel';
	protected $primaryKey = 'anggota_rombel_id';
    protected $guarded = [];
    public $timestamps = false;
}
