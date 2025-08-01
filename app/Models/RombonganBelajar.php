<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RombonganBelajar extends Model
{
    public $incrementing = false;
	public $keyType = 'string';
    protected $connection = 'dapodik';
	protected $table = 'rombongan_belajar';
	protected $primaryKey = 'rombongan_belajar_id';
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }
}
