<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ProductoRepository
{
    public function crearProducto($data)
    {
        try{
        // Llamada al procedimiento almacenado
            $result = DB::statement('EXEC paCrearProducto ?, ?, ?, ?, ?, ?, ?, ?', [
                $data['nombre'],
                $data['descripcion'],
                $data['precio'],
                $data['cantidad'],
                $data['idProveedor'],
                $data['idCategoria'],
                $data['ubicacion'],
                $data['imgen'],


            // $data['image']
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        } catch (Exception $e) {
            // Log del error
            Log::error("Error al crear el producto: " . $e->getMessage());
            return false; // Retorna false en caso de fallo
        }
    }

    public function modificarProducto($data)
    {
        try{
            $result = DB::statement('EXEC paModiicarProducto ?, ?, ?, ?, ?, ?, ?', [
                $data['idProducto'],
                $data['nombre'],
                $data['descripcion'],
                $data['precio'],
                $data['idProveedor'],
                $data['idCategoria'],
                $data['imgen']
            ]);
        return $result;
        } catch (Exception $e) {
            Log::error("Error al modificar el producto: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarProducto($id)
    {
        try{
            // Llamada al procedimiento almacenado para eliminar el producto
            $result = DB::statement('EXEC paEliminarProducto ?', [$id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar el producto: " . $e->getMessage());
            return false;
        }
    }

}
