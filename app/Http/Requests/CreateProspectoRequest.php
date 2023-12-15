<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateProspectoRequest extends Request {

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
            'CPRO_CodigoProspecto',
            'CPRO_NombreProspecto'=>'required',
            'CPRO_NombreComercial'=>'required',
            'CPRO_Calle'=>'required',
            'CPRO_NoExt'=>'numeric',
            'CPRO_NoInt'=>'numeric',
            'CPRO_Colonia',
            'CPRO_CodigoPostal'=>'numeric',
            'CPRO_Telefono'=>'numeric',

             'CPRO_CIU_CiudadId',
            'CPRO_EST_EstadoId',
               'CPRO_PAI_PaisId',

//               'CPRO_CMM_EstatusProspectoId',

//               'CPRO_EMP_ModificadoPorId',

            'CPRO_CallePrimeraCruce',
            'CPRO_CalleSegundaCruce'

		];
	}

}
