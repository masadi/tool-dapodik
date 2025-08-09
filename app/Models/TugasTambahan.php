<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TugasTambahan extends Model
{
    use HasUuids;
    protected $connection = 'dapodik';
	protected $table = 'tugas_tambahan';
	protected $primaryKey = 'ptk_tugas_tambahan_id';
    protected $guarded = [];
    public $timestamps = false;
}
