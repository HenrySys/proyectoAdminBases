<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;
    protected $table = 'proveedor';//definimos el nombre de la tabla
    protected $fillable = ['nombre','correo','telefono'];//definimos los campos que se pueden llenar

    public function productos()
    {
        return $this->hasMany('App\Models\Producto', 'idProveedor'); // relación uno a muchos con la tabla producto
    }
}
