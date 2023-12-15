<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlantillasCxP extends Model {

    protected $table = 'PlantillasCxP';

    protected $primaryKey = 'PCXP_PlantillaCXPId';

    public $timestamps= false;

    public $incrementing = false;

    protected $hidden = ['PCXP_Timestamp'];

}
