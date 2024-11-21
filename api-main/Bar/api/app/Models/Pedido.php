<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $table = 'pedido';//definimos el nombre de la tabla
    protected $fillable = ['idUser','idCliente','idEmpleado','idMesa','fecha','hora','estado'];//definimos los campos que se pueden llenar

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'idUser')->withDefault();
    }

    // Relación con el modelo Cliente (un pedido pertenece a un cliente)
    public function cliente()
    {
        return $this->belongsTo('App\Models\Cliente', 'idCliente');
    }

    // Relación con el modelo Empleado (un pedido pertenece a un empleado)
    public function empleado()
    {
        return $this->belongsTo('App\Models\Empleado', 'idEmpleado');
    }

    // Relación con el modelo Mesa (un pedido pertenece a una mesa)
    public function mesa()
    {
        return $this->belongsTo('App\Models\Mesa', 'idMesa')->withDefault();
    }

    public function factura()
    {
        return $this->hasOne('App\Models\Factura', 'idPedido'); // relación uno a uno con la tabla factura
    }

}
