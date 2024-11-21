<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class PedidoProductoRepository
{
    public function crearPedidoProducto($data)
    {
        try{
        // Llamada al procedimiento almacenado
            $result = DB::statement('EXEC paCrearPedidoProducto ?,?,?', [
                $data['idPedido'],
                $data['idProducto'],
                $data['descripcion'],
            ]);
            return $result; // Devuelve true si se ejecutó correctamente

        }catch(Exception $e){
            Log::error("Error al crear el pedido producto: " . $e->getMessage());
            return false;
        }
    }

    public function modificarPedidoProducto($data)
    {
        try{
            $result = DB::statement('EXEC paModiicarPedidoProducto ?, ? ', [
                $data['id'],
                $data['descripcion'],
            ]);
            return $result;
        }catch(Exception $e){
            Log::error("Error al modificar el pedido producto: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarPedidoProductoSP($id)
    { //  SP = SAVE PRODUCT
        try{
        $result  = DB::statement('EXEC paEliminarPedidoProductoSP ?', [$id]);
        return $result;
        }catch(Exception $e){
            Log::error("Error al eliminar el pedido producto: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarPedidoProductoNSP($id)
    { //  SP = SAVE PRODUCT
        try{
        $result  = DB::statement('EXEC paEliminarPedidoProductoNSP ?', [$id]);
        return $result;
        }catch(Exception $e){
            Log::error("Error al eliminar el pedido producto: " . $e->getMessage());
            return false;
        }
    }
}
