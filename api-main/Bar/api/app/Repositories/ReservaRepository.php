<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ReservaRepository
{
    public function crearReserva($data)
    {
        try{
        // Llamada al procedimiento almacenado
            $result = DB::statement('EXEC paCrearReserva ?, ?, ?, ?', [
                $data['idUser'],
                $data['idMesa'],
                $data['fecha'],
                $data['hora']
            ]);
            return $result; // Devuelve true si se ejecutó correctamente
        } catch (Exception $e) {
            // Log del error
            Log::error("Error al crear la reserva: " . $e->getMessage());
            return false; // Retorna false en caso de fallo
        }
    }

    public function modificarReserva($data)
    {
        try{
            $result = DB::statement('EXEC paModificarReserva ?, ?, ?, ?, ?', [
                $data['idReserva'],
                $data['idUser'],
                $data['idMesa'],
                $data['fecha'],
                $data['hora']
            ]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al modificar el reserva: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarReserva($id)
    {
        try{
            $result = DB::statement('EXEC paEliminarReserva ?', [$id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar la reserva: " . $e->getMessage());
            return false;
        }
    }

}
