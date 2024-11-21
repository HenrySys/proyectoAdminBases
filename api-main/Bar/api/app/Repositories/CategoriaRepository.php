<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class CategoriaRepository
{
    public function crearCategoria($data)
    {
        try {

            $result = DB::statement('EXEC paCrearCategoria ?', [
                $data['nombre']
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        } catch (Exception $e) {
            // Log del error
            Log::error("Error al crear la categoria: " . $e->getMessage());
            return false; // Retorna false en caso de fallo
        }
    }

    public function modificarCategoria($data)
    {
        try{
            $result = DB::statement('EXEC paModificarCategoria ?, ?', [
                $data['idCategoria'],
                $data['nombre']
            ]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al modificar la categoria: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarCategoria($id)
    {
        try{
            $result = DB::statement('EXEC paEliminarCategoria ?', [$id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar la categoria: " . $e->getMessage());
            return false;
        }
    }
}
