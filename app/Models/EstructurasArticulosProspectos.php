<?php
/**
 * @version 1.0
 * @create Juan Antonio Gómez
 * @date 21/04/2017
 * @updater -
 *
 * History
 * v1.1 - Se programo la funcionalidad general.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstructurasArticulosProspectos extends Model{

    protected $table = 'EstructurasArticulosProspectos';

    protected $primaryKey = 'EARP_EstructuraId';

    public $timestamps = false;

    public $incrementing = false;

} 