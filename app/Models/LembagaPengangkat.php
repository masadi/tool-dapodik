<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LembagaPengangkat extends Model
{
    protected $connection = 'dapodik';
	protected $table = 'ref.lembaga_pengangkat';
	protected $primaryKey = 'lembaga_pengangkat_id';
}
