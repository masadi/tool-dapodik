<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCompositePrimaryKey;

class SekolahLongitudinal extends Model
{
    //use HasCompositePrimaryKey;
    protected $connection = 'dapodik';
	protected $table = 'sekolah_longitudinal';
	//protected $primaryKey = ['sekolah_id', 'semester_id'];
    
    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id', 'semester_id');
    }
}
