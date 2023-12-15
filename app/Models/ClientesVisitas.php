<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientesVisitas extends Model {

    protected $table = 'ClientesVisitas';

    protected $primaryKey = 'CV_ClienteVisitaId';

    public $timestamps= false;

    public $incrementing = false;

}
