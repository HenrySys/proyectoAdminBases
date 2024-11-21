<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class DetalleFacturaRepository
{
    public function crearDetalleFactura($data)
    {
        try {
            $result = DB::statement('EXEC paCrearDetalleFactura ?, ?', [
                $data['idFactura'],
                $data['idPedidoProducto'],
            ]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al crear el detalle de la factura: " . $e->getMessage());
            return false;
        }
    }

    public function modificarDetalleFactura($data)
    {
        try {
            $result = DB::statement('EXEC paModificarDetalleFactura ?, ?', [
                $data['idDetalleFactura'],
                $data['descuento']
            ]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al modificar el detalle de la factura: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarDetalleFacturaSP($id)
    {
        try {
            $result = DB::statement('EXEC paEliminarDetalleFacturaSP ?', [$id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar el detalle de la factura: " . $e->getMessage());
            return false;
        }
    }

    public function eliminarDetalleFacturaNSP($id)
    {
        try {
            $result = DB::statement('EXEC paEliminarDetalleFacturaNSP ?', [$id]);
            return $result;
        } catch (Exception $e) {
            Log::error("Error al eliminar el detalle de la factura: " . $e->getMessage());
            return false;
        }
    }
}
