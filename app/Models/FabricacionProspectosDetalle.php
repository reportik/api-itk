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

class FabricacionProspectosDetalle extends Model{

    protected $table = 'FabricacionProspectosDetalle';

    protected $primaryKey = 'FAPD_DetalleId';

    public $timestamps = false;

    public $incrementing = false;

} 