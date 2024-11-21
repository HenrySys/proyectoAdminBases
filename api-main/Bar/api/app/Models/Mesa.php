<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;
    protected $table = 'mesa';//definimos el nombre de la tabla
    protected $fillable = ['numero','capacidad','estado'];//definimos los campos que se pueden llenar

    public function pedidos()
    {
        return $this->hasMany('App\Models\Pedido', 'idMesa'); // relación uno a muchos con la tabla pedido
    }

    public function reservas()
    {
        return $this->hasMany('App\Models\Reserva', 'idMesa'); // relación uno a muchos con la tabla reserva
    }


}
