<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengguna extends Model
{
    protected $connection = 'dapodik';
    public $incrementing = false;
    public $keyType = 'string';
	protected $table = 'man_akses.pengguna';
	protected $primaryKey = 'pengguna_id';
    protected $guarded = [];
    public $timestamps = false;
}
