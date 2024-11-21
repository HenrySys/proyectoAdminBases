<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoProducto extends Model
{
    use HasFactory;
    protected $table = 'pedidoProducto';//definimos el nombre de la tabla
    protected $fillable = ['idPedido','idProducto','descripcion','estado'];//definimos los campos que se pueden llenar

    public function pedido(){
        return $this->belongsTo('App\Models\Pedido','idPedido');//relacion de muchos a uno con la tabla pedido
    }

    public function producto(){
        return $this->belongsTo('App\Models\Producto','idProducto');//relacion de muchos a uno con la tabla producto
    }

}
