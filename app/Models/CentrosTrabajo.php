<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentrosTrabajo extends Model {

	protected $hidden = ['CET_Timestamp'];
	
    protected $table = 'CentrosTrabajo';

    protected $primaryKey = 'CET_CentroTrabajoId';

    public $timestamps = false;

    public $incrementing = false;    
}
