<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayakPip extends Model
{
    protected $connection = 'dapodik';
	protected $table = 'ref.alasan_layak_pip';
	protected $primaryKey = 'id_layak_pip';
}
