<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $table = 'producto';//definimos el nombre de la tabla
    protected $fillable = ['idProveedor','idCategoria','nombre','descripcion','precio','imgen'];//definimos los campos que se pueden llenar


    public function categoria(){
        return $this->belongsTo('App\Models\Category','id'); //relacion de muchos a uno con la tabla categoria
    }

    public function proveedor(){
        return $this->belongsTo('App\Models\Proveedor','idProveedor'); //relacion de muchos a uno con la tabla proveedor
    }

    public function pedidos(){
        return $this->hasMany('App\Models\PedidoProducto','idProducto');   //relacion de uno a muchos con la tabla pedidoProducto
    }

    public function inventario(){
        return $this->hasOne('App\Models\Inventario','idProducto');   //relacion de uno a uno con la tabla inventario
    }

    public function detalleFactura(){
        return $this->hasMany('App\Models\DetalleFactura','idProducto');   //relacion de uno a muchos con la tabla detalleFactura
    }



}
