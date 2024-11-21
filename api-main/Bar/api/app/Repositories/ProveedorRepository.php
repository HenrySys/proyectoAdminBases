<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProveedorRepository
{
    public function crearProveedor($data)
    {
        try{
        // Llamada al procedimiento almacenado
            $result = DB::statement('EXEC paCrearProveedor ?, ?, ?', [
                $data['nombre'],
                $data['correo'],
                $data['telefono']
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        } catch (Exception $e) {
            // Log del error
            Log::error("Error al crear el proveedor: " . $e->getMessage());
            return false; // Retorna false en caso de fallo
        }
    }

    public function modificarProveedor($data)
    {
        try{
            $result = DB::statement('EXEC paModificarProveedor ?, ?, ?, ?', [
                $data['idProveedor'],
                $data['nombre'],
                $data['correo'],
                $data['telefono']
            ]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al modificar el proveedor: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarProveedor($id)
    {
        try{
            $result = DB::statement('EXEC paEliminarProveedor ?', [$id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar el proveedor: " . $e->getMessage());
            return false;
        }
    }

}
