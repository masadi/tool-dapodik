<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PtkTerdaftar extends Model
{
    public $incrementing = false;
	public $keyType = 'string';
    protected $connection = 'dapodik';
	protected $table = 'ptk_terdaftar';
	protected $primaryKey = 'ptk_terdaftar_id';
    protected $guarded = [];
    public $timestamps = false;
    public function tahun_ajaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id', 'tahun_ajaran_id');
    }
}
