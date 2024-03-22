<?php namespace App\Models;

use Carbon\Carbon;
use App\Models\DatabaseNotification;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Sistema\DAOGeneralController;
use Google\Client AS Google_Client;
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
    public function fcmNotification($aplicativo, $details)
    {
        $fcm = FCM_Tokens::where('RPT_Usuario_Id', $this->id)->where('Aplicativo', $aplicativo)->first();
        try {
        $credentialsFilePath = public_path('service_account.json');
        $client = new Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $apiurl = 'https://fcm.googleapis.com/v1/projects/mi-nomina-iteknia/messages:send';
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        $access_token = $token['access_token'];
        
        $headers = [
            "Authorization: Bearer". $access_token,
            "Content-Type: application/json"
        ];
        $test_data = [
            "title" => "TITLE_HERE",
            "description" => "DESCRIPTION_HERE",
        ];
        
        $data['data'] =  $test_data;
        
        $data['token'] = $fcm->fcm_token; // Retrive fcm_token from users table
        
        $payload['message'] = $data;
        $payload = json_encode($payload);
        
        $httpClient = $client->authorize();
        $res = $httpClient->post($apiurl, ['json' => $payload]);
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $apiurl);
    //     curl_setopt($ch, CURLOPT_POST, true);
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    //    // dd($ch);
    //        $res = curl_exec($ch);
         //curl_close($ch);
        if ($res) {
            return response()->json([
                'message' => 'Notification has been Sent'
            ]);
        } else {
                return response()->json([
                    'message' => $res
                ]); }
        } catch (\Exception $e) {
            DB::rollback();
            return ['Status' => 'Error', 'Mensaje' => 'Ocurrió un error al realizar el proceso.  Error: ' . $e->getMessage() . ". Linea: " . $e->getLine() . " . Clase: " . $e->getFile() . " . Code: " . $e->getCode()];
        }
            //throw $th;
    }
}