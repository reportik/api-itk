<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CatalogoContable extends Model {

    protected $table = 'CatalogoContable';

    protected $primaryKey = 'CC_CatalogoContableId';

    public $timestamps = false;

    public $incrementing = false;
}
