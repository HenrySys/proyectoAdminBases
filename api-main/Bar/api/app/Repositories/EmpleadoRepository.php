<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class EmpleadoRepository
{
    public function crearEmpleado($data)
    {
        try{
        // Llamada al procedimiento almacenado
            $result = DB::statement('EXEC paCrearEmpleado ?,?,?,?,?,?', [
                $data['nombre'],
                $data['apellido'],
                $data['correo'],
                $data['telefono']
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al crear el empleado: " . $e->getMessage());
            return false;
        }
    }

    public function modificarEmpleado($data)
    {
        try{
            $result =  DB::statement('EXEC paModificarEmpleado ?, ?, ?, ?, ?', [
                $data['id'],
                $data['nombre'],
                $data['apellido'],
                $data['correo'],
                $data['telefono']
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al modificar el empleado: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarEmpleado($id)
    {
        try{
            $result = DB::statement('EXEC paEliminarEmpleado ?', [$id]);
        return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al eliminar el empleado: " . $e->getMessage());
            return false;
        }
    }
}
