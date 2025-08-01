<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $connection = 'dapodik';
	protected $table = 'ref.tahun_ajaran';
	protected $primaryKey = 'tahun_ajaran_id';
}
