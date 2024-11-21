<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ClienteRepository
{
    public function crearCliente($data)
    {
        try{
        // Llamada al procedimiento almacenado
            $result = DB::statement('EXEC paCrearCliente ?,?,?,?,?', [
                $data['nombre'],
                $data['apellido'],
                $data['correo'],
                $data['direccion'],
                $data['telefono']
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        } catch (Exception $e) {
            // Log del error
            Log::error("Error al crear el cliente: " . $e->getMessage());
            return false; // Retorna false en caso de fallo
        }
    }
    public function modificarCliente($data)
    {
        try {
            $result = DB::statement('EXEC paModiicarCliente ?, ?, ?, ?, ?, ?, ?', [
                $data['id'],
                $data['cedula'],
                $data['nombre'],
                $data['apllido'],
                $data['correo'],
                $data['direccion'],
                $data['telefono']
            ]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al modificar el cliente: " . $e->getMessage());
            return false;
        }
    }
    public function eliminarCliente($id)
    {
        try{
            $result = DB::statement('EXEC paEliminarCliente ?', [$id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar el cliente: " . $e->getMessage());
            return false;
        }
    }

}
