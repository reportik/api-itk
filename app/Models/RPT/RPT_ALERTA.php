<?php

namespace App\Models\RPT;

use DB;
use App\Models\UsuariosRPT;
use App\Events\UserNotifyEvent;
use App\Models\Concerns\UsesUuid;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;

class RPT_ALERTA extends Model 
{
    use UsesUuid;
    protected $table = 'RPT_ALE_Alertas';
    protected $primaryKey = 'ALE_Id';
    public $timestamps = false;

    public function sendEmail($filas)
    {
        $correos_db = DB::select("SELECT e.EMP_CorreoElectronico as correo FROM RPT_ALE_UsuariosAlertas ua
                inner join Empleados e on RIGHT('000' + CONVERT(varchar, e.EMP_CodigoEmpleado), 4)  = RIGHT('000' + CONVERT(varchar, ua.UA_EMP_CodigoEmpleado), 4) 
                where ua.UA_notificarCorreo = 1 
                and e.EMP_CorreoElectronico is not null 
                and e.EMP_CorreoElectronico <> ''
                and ua.UA_ALE_AlertaId = ?
            ",[$this->ALE_Id]);

       if (count($correos_db)>0) {
            $correos = array_column($correos_db, 'correo');
            $encabezados = array_keys((array) $filas[0]);
            $ale_nombre = $this->ALE_nombre;
            $ale_asunto = $this->ALE_correo_asunto;
            $ale_texto = $this->ALE_correo_texto;

            if (count($filas) == 1) {
                extract((array) $filas[0]);
                eval("\$ale_asunto = \"$ale_asunto\";");
                eval("\$ale_texto = \"$ale_texto\";");
            } 
            Mail::send(
                'plantillas.email_alerta_resumen',
                compact('encabezados', 'filas', 'ale_nombre', 'ale_asunto', 'ale_texto'),
                function ($msj) use ($correos, $ale_asunto) {
                    $msj->subject($ale_asunto); //ASUNTO DEL CORREO
                    $msj->to($correos); //Correo del destinatario
                }
            );
       }
    }

    public function sendNotification($rpt_id, $filas)
    {
        $usuarios_db = DB::select("SELECT ua.UA_EMP_CodigoEmpleado as codigo, e.EMP_Fotografia as foto FROM RPT_ALE_UsuariosAlertas ua
            inner join Empleados e on RIGHT('000' + CONVERT(varchar, e.EMP_CodigoEmpleado), 4)  = RIGHT('000' + CONVERT(varchar, ua.UA_EMP_CodigoEmpleado), 4) 
            where ua.UA_notificarInterno = 1 and ua.UA_ALE_AlertaId = ?
            ", [$this->ALE_Id]);
        $tituloMensaje = $this->ALE_notificacion_title;
        $cuerpoMensaje = $this->ALE_notificacion_body;

        if (count($filas) == 1) {
            extract((array) $filas[0]);
           
            eval("\$tituloMensaje = \"$tituloMensaje\";");
            eval("\$cuerpoMensaje = \"$cuerpoMensaje\";");
            
        } 
            $tipoMensaje = 'alert'; //alert es una ventana, toast es una notificacion discreta
            $accionMensaje = $this->ALE_notificacion_accion;
            $accionMensaje .= (is_null($rpt_id) || ($rpt_id) == '')? '': '?rpt_id='.$rpt_id;

        foreach ($usuarios_db as $empleado) {
            
            $user = UsuariosRPT::where('nomina', $empleado->codigo)->first();
            $fotoMensaje = (is_null($empleado->foto)) ? 'SIN FOTO.png' : $empleado->foto;

            $details = [
                'title' =>  $tituloMensaje,
                'body' => $cuerpoMensaje,
                'type' => $tipoMensaje,
                'foto' => $fotoMensaje,
                'action' => $accionMensaje,
                'aplicativo' => 'web'
            ];

            $user->storeNewNotification($details);

            //para PUSHER:
            $userId = $user->id;
            event(new UserNotifyEvent($userId, $details));
        }
        //para Firebase
        //$user->fcmNotification($details);
        return response()->json([
            "status" => true,
            "message" => "Notificado"
        ]);
    
    }

    public function process()
    {
        $result = DB::select($this->ALE_consulta);       
        if (count($result) > 0) {
            if ($this->ALE_enviar_resumen) {
                self::sendEmail($result);
                self::sendNotification(null, $result);
            } else {                
                foreach ($result as $value) {
                    $data[0] = $value; // toArray -----------------------------
                    $rpt_id = $value->{$this->ALE_notificacion_parametro};
                    self::sendEmail($data);
                    self::sendNotification($rpt_id, $data);
                }
            }

        } 
        
    }
}
