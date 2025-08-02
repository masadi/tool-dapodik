<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sekolah extends Model
{
    public $incrementing = false;
	public $keyType = 'string';
	protected $table = 'sekolah';
	protected $primaryKey = 'sekolah_id';
	protected $guarded = [];
    public function longitudinal()
    {
        return $this->hasOne(SekolahLongitudinal::class, 'sekolah_id', 'sekolah_id');
    }
    public function pengguna()
    {
        return $this->hasOne(Pengguna::class, 'sekolah_id', 'sekolah_id');
    }
    public function yayasan()
    {
        return $this->belongsTo(Yayasan::class, 'yayasan_id', 'yayasan_id');
    }
}
