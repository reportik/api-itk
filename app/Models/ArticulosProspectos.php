<?php
/**
 * @version 1.0
 * @create Juan Antonio Gómez
 * @date 16/02/2017
 * @updater -
 *
 * History
 * v1.1 - Se programo la funcionalidad general.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArticulosProspectos extends Model{

    protected $table = 'ArticulosProspectos';

    protected $primaryKey = 'ARTP_ArticuloProspectoId';

    public $timestamps = false;

    public $incrementing = false;

} 