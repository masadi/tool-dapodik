<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCompositePrimaryKey;

class Periodik extends Model
{
    use HasCompositePrimaryKey;
    protected $connection = 'dapodik';
    public $incrementing = false;
    public $keyType = 'string';
	protected $table = 'peserta_didik_longitudinal';
	protected $primaryKey = ['peserta_didik_id', 'semester_id'];
    protected $guarded = [];
    public $timestamps = false;
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }
}
