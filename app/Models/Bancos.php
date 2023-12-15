<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * Created by PhpStorm.
 * User: App-01
 * Date: 18/05/2015
 * Time: 05:51 PM
 */

class Bancos extends Model{

    protected $table = 'Bancos';

    protected $primaryKey = 'BAN_BancoId';

    public $timestamps = false;

    public $incrementing = false;

} 