<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class InventarioRepository
{
    public function aumentarInventario($data)
    {
        try{
        // Llamada al procedimiento almacenado
            $result = DB::statement('EXEC paAumentarInventario ?, ?', [
                $data['idProducto'],
                $data['cantidad']
            ]);
        return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al aumentar el inventario: " . $e->getMessage());
            return false;
        }
    }

    public function disminuirInventario($data)
    {
        try{
            $result =  DB::statement('EXEC paDisminuirInventario ?, ?', [
                $data['idProducto'],
                $data['cantidad']
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al disminuir el inventario: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarInventario($data)
    {
        try{
        $result = DB::statement('EXEC paActualizarInventario ?,?,?', [
            $data['idProducto'],
            $data['cantidad'],
            $data['ubicacion'],

        ]);
        return $result; // Devuelve true si se ejecutó correctamente
        }catch(Exception $e){
            Log::error("Error al actualizar el inventario: " . $e->getMessage());
            return false;
        }
    }
}
