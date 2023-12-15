<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmbarquesDevoluciones extends Model {

    protected $table = 'EmbarquesDevoluciones';

    protected $primaryKey = 'ED_DevolucionId';

    public $timestamps = false;

    public $incrementing=false;

}
