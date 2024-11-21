<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class PedidoRepository
{
    public function crearPedido($data)
    {
        try {
            // Llamada al procedimiento almacenado
            $result = DB::select('EXEC paCrearPedido ?,?,?,?,?,?,?', [
                $data['idUser'],
                $data['idCliente'],
                $data['idMesa'],
                $data['idEmpleado'],
                $data['fecha'],
                $data['hora'],
                $data['estado']

            ]);

            return $result; // Devuelve true si se ejecutó correctamente
        } catch (Exception $e) {
            // Log del error
            Log::error("Error al crear el pedido: " . $e->getMessage());
            return false; // Retorna false en caso de fallo
        }
    }

    public function modificarPedido($data)
    {
        try {
            // Llamada al procedimiento almacenado para modificar el pedido
            $result = DB::statement('EXEC paModificarPedido ?, ?, ?, ?, ?, ?, ?', [
                $data['idPedido'],
                $data['idUser'],
                $data['idCliente'],
                $data['idMesa'],
                $data['idEmpleado'],
                $data['fecha'],
                $data['hora'],
                $data['estado']
            ]);

            return $result; // Devuelve true si se ejecutó correctamente
        } catch (Exception $e) {
            Log::error("Error al modificar el pedido: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarPedido($id)
    {
        try {
            // Llamada al procedimiento almacenado para eliminar el pedido
            $result = DB::statement('EXEC paEliminarPedido ?', [$id]);

            return $result; // Devuelve true si se ejecutó correctamente
        } catch (Exception $e) {
            Log::error("Error al eliminar el pedido: " . $e->getMessage());
            return false;
        }
    }
}
