<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;
    protected $table = 'factura';//definimos el nombre de la tabla
    protected $fillable = ['idPedido','idCliente','idEmpleado','descuento','total'];//definimos los campos que se pueden llenar


    public function pedido(){
        return $this->belongsTo('App\Models\Pedido','idPedido');//relacion de muchos a uno con la tabla pedido
    }

    public function empleado(){
        return $this->belongsTo('App\Models\Empleado','idEmpleado');//relacion de muchos a uno con la tabla empleado
    }

    public function detalleFactura(){
        return $this->hasMany('App\Models\DetalleFactura','idFactura');//relacion de uno a muchos con la tabla detalleFactura
    }

    public function cliente(){
        return $this->belongsTo('App\Models\Cliente','idCliente');//relacion de muchos a uno con la tabla cliente
    }
}
