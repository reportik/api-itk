<?php
/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 20/07/2015
 * Time: 02:31 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class AddendaComponente extends Model{

    protected $table = 'AddendasComponentes';

    protected $primaryKey = 'ACO_AddendaComponenteId';

    public $timestamps = false;

    public $incrementing = false;

} 