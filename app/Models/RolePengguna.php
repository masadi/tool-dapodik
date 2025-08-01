<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePengguna extends Model
{
    protected $connection = 'dapodik';
    public $incrementing = false;
    public $keyType = 'string';
	protected $table = 'man_akses.role_pengguna';
	protected $primaryKey = 'id_role_pengguna';
    protected $guarded = [];
    public $timestamps = false;
}
