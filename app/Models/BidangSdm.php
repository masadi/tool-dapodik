<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasCompositePrimaryKey;

class BidangSdm extends Model
{
    use HasCompositePrimaryKey;
    protected $connection = 'dapodik';
    public $incrementing = false;
    public $timestamps = false;
    public $keyType = 'string';
    protected $table = 'bidang_sdm';
	protected $primaryKey = ['ptk_id', 'bidang_studi_id', 'urutan'];
    protected $guarded = [];
}
