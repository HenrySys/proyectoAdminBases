<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventario;
use App\Repositories\InventarioRepository;
use Illuminate\Support\Facades\Validator;

class InventarioController extends Controller
{
    protected $inventarioRepository;

    public function __construct(InventarioRepository $inventarioRepository)
    {
        $this->inventarioRepository = $inventarioRepository;
    }

    // Obtener todo el inventario
    public function index()
    {
        $data = Inventario::all();
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function show($id)
    {
        $data = Inventario::find($id);
        if (is_object($data)) {
            $response = array(
                "status" => 200,
                "message" => "Datos de Reserva",
                "data" => $data
            );
        } else {
            $response = array(
                "status" => 404,
                "message" => "Recurso no encontrado"
            );
        }
        return response()->json($response, $response['status']);
    }


    // Aumentar inventario
    public function aumentarInventario(Request $request)
    {
        $dataInput = $request->input('data', null);

        if ($dataInput) {
            $data = json_decode($dataInput, true);

            if (is_array($data)) {
                $rules = [
                    'idProducto' => 'required|integer',
                    'cantidad' => 'required|integer|min:1',
                ];
                $validator = Validator::make($data, $rules);

                if (!$validator->fails()) {
                    $result = $this->inventarioRepository->aumentarInventario($data);

                    if ($result) {
                        return response()->json([
                            'status' => 201,
                            'message' => 'Inventario aumentado correctamente',
                            'data' => $data
                        ]);
                    } else {
                        return response()->json([
                            'status' => 500,
                            'message' => 'Error al aumentar el inventario'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 406,
                        'message' => 'Datos erróneos',
                        'errors' => $validator->errors()
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Error en el formato de los datos JSON'
                ]);
            }
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No se encontró el objeto data'
            ]);
        }
    }

    // Disminuir inventario
    public function disminuirInventario(Request $request)
    {
        $dataInput = $request->input('data', null);

        if ($dataInput) {
            $data = json_decode($dataInput, true);

            if (is_array($data)) {
                $rules = [
                    'idProducto' => 'required|integer',
                    'cantidad' => 'required|integer|min:1',
                ];
                $validator = Validator::make($data, $rules);

                if (!$validator->fails()) {
                    $result = $this->inventarioRepository->disminuirInventario($data);

                    if ($result) {
                        return response()->json([
                            'status' => 201,
                            'message' => 'Inventario disminuido correctamente',
                            'data' => $data
                        ]);
                    } else {
                        return response()->json([
                            'status' => 500,
                            'message' => 'Error al disminuir el inventario'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 406,
                        'message' => 'Datos erróneos',
                        'errors' => $validator->errors()
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Error en el formato de los datos JSON'
                ]);
            }
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No se encontró el objeto data'
            ]);
        }
    }

    // Actualizar inventario
    public function actualizarInventario(Request $request)
    {
        $dataInput = $request->input('data', null);

        if ($dataInput) {
            $data = json_decode($dataInput, true);

            if (is_array($data)) {
                $rules = [
                    'idProducto' => 'required|integer',
                    'cantidad' => 'required|integer',
                    'ubicacion' => 'required|string|max:255',
                ];
                $validator = Validator::make($data, $rules);

                if (!$validator->fails()) {
                    $result = $this->inventarioRepository->actualizarInventario($data);

                    if ($result) {
                        return response()->json([
                            'status' => 201,
                            'message' => 'Inventario actualizado correctamente',
                            'data' => $data
                        ]);
                    } else {
                        return response()->json([
                            'status' => 500,
                            'message' => 'Error al actualizar el inventario'
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => 406,
                        'message' => 'Datos erróneos',
                        'errors' => $validator->errors()
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Error en el formato de los datos JSON'
                ]);
            }
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'No se encontró el objeto data'
            ]);
        }
    }
}
