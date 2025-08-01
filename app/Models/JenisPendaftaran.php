<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPendaftaran extends Model
{
    protected $connection = 'dapodik';
	protected $table = 'ref.jenis_pendaftaran';
	protected $primaryKey = 'jenis_pendaftaran_id';
}
