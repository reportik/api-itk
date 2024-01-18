<?php namespace App\Models\RPT;

use Illuminate\Database\Eloquent\Model;
use DB;
class RPT_PROV extends Model
{
    protected $table = 'RPT_ProvisionCXC';
    protected $primaryKey = 'PCXC_ID';
    public $timestamps = false;

    public function scopeActive($query, $ov)
    {
        return $query->where('PCXC_OV_Id', $ov)->where('PCXC_Activo', 1)
        ->where('PCXC_Eliminado', 0);
    }
    public function scopePrimero($query, $ov)
    {
        return $query->where('PCXC_OV_Id', $ov)->where('PCXC_Activo', 1)
            ->where('PCXC_Eliminado', 0)->orderBy('PCXC_ID', 'ASC')->first();
    }
    public function scopeActiveOV($query, $ov)
    {
        return $query->where('PCXC_OV_Id', $ov)->where('PCXC_Activo', 1)
            ->where('PCXC_Eliminado', 0)->orderBy('PCXC_ID', 'ASC')->lists('PCXC_ID');
            
    }

}
