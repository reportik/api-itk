<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 18/05/2015
 * Time: 05:51 PM
 */

class BancosTransferencias extends Model{

    protected $table = 'BancosTransferencias';

    protected $primaryKey = 'BANT_TransferenciaId';

    public $timestamps = false;

    public $incrementing = false;

} 