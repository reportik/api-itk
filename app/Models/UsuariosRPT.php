<?php namespace App\Models;

use Carbon\Carbon;
use App\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Http\Controllers\Sistema\DAOGeneralController;
use App\Notifications\PNotification;
use App\Models\FCM_Tokens;

class UsuariosRPT extends Model {
    use Notifiable;
    protected $table = 'RPT_Usuarios';
    protected $primaryKey = 'id';
    public $fcmAplicativo = 'mi-nomina';
    public $timestamps = false;

    public function setFmcAplicativo($fcmAplicativo)
    {
        $this->fcmAplicativo = $fcmAplicativo;
        $fcm = FCM_Tokens::where('RPT_Usuario_Id', $this->id)->where('Aplicativo', $this->fcmAplicativo)->first();
        return $fcm->fcm_token;
    }

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
    public function fcmNotification($details)
    {
        //$this->fcmAplicativo = $aplicativo;
        return $this->notify(new PNotification($details));

    }

     /**
    * Assuming that you have a database table which stores device tokens.
    */
    // public function deviceTokens(): HasMany
    // {
    //     return $this->hasMany(DeviceToken::class);
    // }
    
    public function routeNotificationForFCM($notification)//: string|array|null
    {
        //$this->fcmAplicativo = $fcmAplicativo;
        return FCM_Tokens::where('RPT_Usuario_Id', $this->id)
        ->whereRaw("Aplicativo IN(". $notification->details['aplicativo'].")")
        ->pluck('fcm_token')->toArray();
        // return $fcm->fcm_token;
        // return $this->fcm_token;
        // return $this->deviceTokens->pluck('fcm_token')->toArray();
    }
    
    /**
    * Optional method to determine which message target to use
    * We will use TOKEN type when not specified
    * @see \Kreait\Firebase\Messaging\MessageTarget::TYPES
    */
    public function routeNotificationForFCMTargetType($notification)//: ?string
    {
        return \Kreait\Firebase\Messaging\MessageTarget::TOKEN;
    }
    
    /**
    * Optional method to determine which Firebase project to use
    * We will use default project when not specified
    */
    // public function routeNotificationForFCMProject($notification)//: ?string
    // {
    //     return config('firebase.default');
    // }

    
}