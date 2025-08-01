<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPtk extends Model
{
    protected $connection = 'dapodik';
	protected $table = 'ref.jenis_ptk';
	protected $primaryKey = 'jenis_ptk_id';
}
