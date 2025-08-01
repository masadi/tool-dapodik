<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrasiPesertaDidik extends Model
{
    public $incrementing = false;
	public $keyType = 'string';
    protected $connection = 'dapodik';
	protected $table = 'registrasi_peserta_didik';
	protected $primaryKey = 'registrasi_id';
    protected $guarded = [];
    public $timestamps = false;
}
