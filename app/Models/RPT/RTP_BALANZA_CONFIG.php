<?php namespace App\Models\RPT;

use Illuminate\Database\Eloquent\Model;

class RTP_BALANZA_CONFIG extends Model
{
  protected $table = 'dbo.RPT_RG_ConfiguracionTabla';
  
    public $timestamps = false; 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'RGC_BC_Cuenta_Id', 'RGC_tipo_renglon', 'RGC_hoja', 'RGC_tabla_titulo', 'RGC_tabla_linea', 'RGC_descripcion_cuenta', 'RGC_valor_default', 'RGC_fecha_alta', 'RGC_mostrar', 'RGC_estilo', 'RGC_multiplica', 'RGC_sociedad', 'RGC_BC_Cuenta_Id2', 'RGC_hojaDescripcion'
    ];
}
