<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JabatanTugas extends Model
{
    protected $connection = 'dapodik';
	public $keyType = 'string';
	protected $table = 'ref.jabatan_tugas_ptk';
	protected $primaryKey = 'jabatan_ptk_id';
}
