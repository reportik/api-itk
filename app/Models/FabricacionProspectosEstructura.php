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

class FabricacionProspectosEstructura extends Model{

    protected $table = 'FabricacionProspectosEstructura';

    protected $primaryKey = 'FAPE_EstructuraId';

    public $timestamps = false;

    public $incrementing = false;

} 