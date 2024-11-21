<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class FacturaRepository
{
    public function crearFactura($data)
    {
        try{
        // Llamada al procedimiento almacenado
            $result = DB::statement('EXEC paCrearFactura ?,?,?,?', [
                $data['descuento'],
                $data['idCliente'],
                $data['idPedido'],
                $data['idEmpleado']
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al crear la factura: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarFactura($id)
    {
        try{
            $result = DB::statement('EXEC paEliminarFactura ?', [$id]);
        return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al eliminar la factura: " . $e->getMessage());
            return false;
        }
    }
}
