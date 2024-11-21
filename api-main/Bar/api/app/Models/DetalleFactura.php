<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleFactura extends Model
{
    use HasFactory;
    protected $table = 'detallefactura';//definimos el nombre de la tabla
    protected $fillable = ['idFactura','idProducto','subtotal'];//definimos los campos que se pueden llenar

    public function factura(){
        return $this->belongsTo('App\Models\Factura','idFactura');//relacion de muchos a uno con la tabla factura
    }

    public function producto(){
        return $this->belongsTo('App\Models\Producto','idProducto');//relacion de muchos a uno con la tabla producto
    }
}
