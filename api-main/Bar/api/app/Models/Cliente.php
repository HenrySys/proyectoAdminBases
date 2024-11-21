<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    protected $table = 'cliente';//definimos el nombre de la tabla
    protected $fillable = ['idUsuario','nombre','apellido','correo','telefono','direccion'];//definimos los campos que se pueden llenar

    public function usuario(){
        return $this->belongsTo('App\Models\User','idUsuario');//relacion de muchos a uno con la tabla usuario
    }

    public function pedidos(){
        return $this->hasMany('App\Models\Pedido','idCliente');//relacion de uno a muchos con la tabla pedido
    }

    public function facturas(){
        return $this->hasMany('App\Models\Factura','idCliente');//relacion de uno a muchos con la tabla factura
    }

    public function reservas (){
        return $this->hasMany('App\Models\Reserva','idCliente');//relacion de uno a muchos con la tabla reserva
    }
}
