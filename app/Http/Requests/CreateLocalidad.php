<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateLocalidad extends Request {

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

            'LOC_CodigoLocalidad',
            'LOC_Nombre',
            'LOC_ALM_AlmacenId',
            // 'LOC_CMM_CtaPredInvId',
            'LOC_Planear'
            // 'LOC_LocalidadGeneral'=>'required|unique:users,email',
            // 'LOC_Eliminado'

        ];
    }

}
