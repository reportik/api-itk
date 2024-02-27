<?php namespace App\Models;

use App\Models\DatabaseNotification;
use App\Http\Controllers\Sistema\DAOGeneralController;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class UsuariosRPT extends Model {

    protected $table = 'RPT_Usuarios';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function getFoto()
    {
        $foto = DB::select("SELECT EMP_Fotografia from Empleados where EMP_CodigoEmpleado = ?", [$this->nomina]);
        if (count($foto) == 1) {
            return $foto[0]->EMP_Fotografia;
        } else {
            return 'SIN FOTO.png';
        }
    }
    public function storeNewNotification($details)
    {
        date_default_timezone_set('America/Mexico_City');
        $dao = new DAOGeneralController();
        $databaseNotification = new DatabaseNotification();
        //$databaseNotification->created_at = date('d-m-Y H:i:s');
        //$databaseNotification->updated_at = date('d-m-Y H:i:s');
        $databaseNotification->id = $dao->nuevoId();
        $databaseNotification->notifiable_id = $this->id;
        $databaseNotification->notifiable_type = 'Muliix\User';
        $databaseNotification->type = 'Invtek\Notifications\RPT_Notification';
        $databaseNotification->data = collect($details);
        $databaseNotification->save();
    }
}