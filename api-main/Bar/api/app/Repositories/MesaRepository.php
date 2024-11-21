<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use \Exception;

class MesaRepository
{
    public function crearMesa($data)
    {
        try{
        // Llamada al procedimiento almacenado
            $result = DB::statement('EXEC paCrearMesa ?,?,?', [
                $data['numero'],
                $data['capacidad'],
                $data['estado'],
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al crear la mesa: " . $e->getMessage());
            return false;
        }
    }

    public function modificarMesa($data)
    {
        try{
            $result =  DB::statement('EXEC paModificarMesa ?, ?, ?, ?', [
                $data['idMesa'],
                $data['numero'],
                $data['capacidad'],
                $data['estado'],
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al modificar la mesa: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarMesa($id)
    {
        try{
            $result = DB::statement('EXEC paEliminarMesa ?', [$id]);
            return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al eliminar la mesa: " . $e->getMessage());
            return false;
        }
    }
}
