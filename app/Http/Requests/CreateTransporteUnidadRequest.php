<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateTransporteUnidadRequest extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		return [
			//

            'TUN_CodigoUnidad',
            'TUN_CMM_TipoTransporteId',
	        'TUN_TUL_TransporteUnidadLineaId',
	        'TUN_Modelo',
	        'TUN_Placas',
	        'TUN_CMM_TipoCombustibleId',
	        'TUN_UnidadTransportePropia',
	        'TUN_Comentarios',
            'TUN_Fotografia',
            'TUN_Activo',
            'TUN_LOC_LocalidadId'





		];
	}

}
