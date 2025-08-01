<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesertaDidik extends Model
{
    public $incrementing = false;
	public $keyType = 'string';
    //protected $connection = 'dapodik';
	protected $table = 'peserta_didik';
	protected $primaryKey = 'peserta_didik_id';
    protected $guarded = [];
    public $timestamps = false;
    public function kelas(){
        return $this->hasOneThrough(
            RombonganBelajar::class,
            AnggotaRombel::class,
            'peserta_didik_id', // Foreign key on the cars table...
            'rombongan_belajar_id', // Foreign key on the owners table...
            'peserta_didik_id', // Local key on the mechanics table...
            'rombongan_belajar_id' // Local key on the cars table...
        );
    }
    public function kelayakan_pip()
    {
        return $this->belongsTo(LayakPip::class, 'id_layak_pip', 'id_layak_pip');
    }
    public function periodik()
    {
        return $this->hasOne(Periodik::class, 'peserta_didik_id', 'peserta_didik_id');
    }
    public function registrasi()
    {
        return $this->hasOne(RegistrasiPesertaDidik::class, 'peserta_didik_id', 'peserta_didik_id');
    }
}
