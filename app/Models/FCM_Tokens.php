<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FCM_Tokens extends Model {  
  
    protected $table = 'RPT_ALE_FCM_tokens';

    protected $primaryKey = 'fcm_token';

    public $timestamps = false;

    public $incrementing = false;

    protected $fillable = [
        'RPT_Usuario_Id',
        'Aplicativo',
        'fcm_token',
        'fecha_creacion',
        'Eliminado'
    ];
}