<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JabatanPtk extends Model
{
    protected $connection = 'dapodik';
	protected $table = 'ref.jabatan_ptk';
	protected $primaryKey = 'jabatan_ptk_id';
}
