<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public function crearUsuario($data)
    {
        try{
            $result = DB::statement('EXEC paCrearUsuarioAdmin ?,?,?', [
                $data['username'],
                $data['email'],
                $data['password_hash']
            ]);
            return $result;
        }catch(Exception $e){
            Log::error("Error al crear el usuario: " . $e->getMessage());
            return false;
        }
    }
    public function crearUsuarioCliente($data)
    {
        try{
            $result = DB::statement('EXEC paCrearUsuarioCliente ?,?,?,?,?,?,?', [
                $data['username'],
                $data['email'],
                $data['password_hash'],
                $data['nombre'],
                $data['apellido'],
                $data['direccion'],
                $data['telefono'],
            ]);
            return $result;
        }catch(Exception $e){
            Log::error("Error al crear el usuario-cliente: " . $e->getMessage());
            return false;
        }
    }

    public function crearUsuarioEmpleado($data)
    {
        try{
            $result = DB::statement('EXEC paCrearUsuarioEmpleado ?,?,?,?,?,?', [
                $data['username'],
                $data['email'],
                $data['password_hash'],
                $data['nombre'],
                $data['apellido'],
                $data['telefono'],
            ]);
            return $result;
        }catch(Exception $e){
            Log::error("Error al crear el usuario-empleado: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUsuario($id)
    {
        try{
            $result = DB::statement('EXEC paEliminarUsuario ?', [$id]);
            return $result;
        }catch(Exception $e){
            Log::error("Error al eliminar el usuario: " . $e->getMessage());
            return false;
        }
    }


}
