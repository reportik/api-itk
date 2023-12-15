<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LotesCajas extends Model {

    protected $table = 'LotesCajas';

    protected $primaryKey = 'LCA_LoteCajaId';

    public $timestamps = false;

    public $incrementing = false;

}
