<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pedido;
use App\Repositories\PedidoRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    protected $pedidoRepo;

    public function __construct(PedidoRepository $pedidoRepo)
    {
        $this->pedidoRepo = $pedidoRepo;
    }

    public function index()
    {
        $data = Pedido::all(); //obtenemos todos los registros de la tabla Pedido y los guardamos en la variable data
        $response = array(
            "status" => 200, //estado de la respuesta
            "message" => "Todos los registros de los Pedidos", //mensaje de la respuesta
            "data" => $data //datos de la respuesta que en este caso son todos los registros de la tabla vehiculo
        );
        return response()->json($response, 200); //retornamos la respuesta en formato json con un estado 200
    }

    public function store(Request $request)
{
    $data_input = $request->input('data', null); // Obtenemos los datos en formato JSON
    if ($data_input) {
        $data = json_decode($data_input, true); // Decodificamos el JSON a un arreglo
        if (is_array($data)) {
            $data = array_map('trim', $data); // Eliminamos espacios en blanco
            $data['idUser'] = intval($data['idUser'] ?? null);
            $data['idCliente'] = intval($data['idCliente']);
            $data['idEmpleado'] = intval($data['idEmpleado']);
            $data['idMesa'] = intval($data['idMesa'] ?? null);

            $rules = [
                'idCliente' => 'required',
                'idEmpleado' => 'required',
                'fecha' => 'required',
                'hora' => 'required',
                'estado' => 'required'
            ];
            $isValid = Validator::make($data, $rules);

            if (!$isValid->fails()) {
                // Intentamos crear el pedido llamando al repositorio
                $pedidoCreado = $this->pedidoRepo->crearPedido($data);

                if ($pedidoCreado) {
                    // Realizar una consulta para obtener el último pedido registrado
                    $ultimoPedido = DB::table('Pedido') // Nombre de la tabla
                        ->where('idCliente', $data['idCliente'])
                        ->where('idEmpleado', $data['idEmpleado'])
                        ->where('fecha', $data['fecha'])
                        ->orderByDesc('id') // Asegurarse de obtener el más reciente
                        ->first();

                    if ($ultimoPedido) {
                        return response()->json([
                            "status" => 201,
                            "message" => "Pedido creado",
                            "pedido" => $ultimoPedido // Retorna el objeto completo con el ID
                        ], 201);
                    } else {
                        return response()->json([
                            "status" => 404,
                            "message" => "No se pudo recuperar el pedido creado"
                        ], 404);
                    }
                } else {
                    return response()->json([
                        "status" => 500,
                        "message" => "Error al crear el pedido en la base de datos"
                    ], 500);
                }
            } else {
                return response()->json([
                    "status" => 406,
                    "message" => "Datos inválidos",
                    "errors" => $isValid->errors()
                ], 406);
            }
        } else {
            return response()->json([
                "status" => 400,
                "message" => "Error en el formato de los datos JSON"
            ], 400);
        }
    } else {
        return response()->json([
            "status" => 400,
            "message" => "No se encontró el objeto data"
        ], 400);
    }
}

    public function show($id)
    {
        $data = Pedido::find($id); //buscamos un registro de la tabla pedido con el id recibido y lo guardamos en la variable data
        if (is_object($data)) { //verificamos si la variable data es un objeto

            $response = array(
                "status" => 200, //estado de la respuesta
                "message" => "Datos de Reserva", //mensaje de la respuesta
                "data" => $data //datos de la respuesta que en este caso es el objeto data
            );
        } else {
            $response = array(
                "status" => 404, //estado de la respuesta
                "message" => "Recurso no encontrado" //mensaje de la respuesta
            );
        }
        return response()->json($response, $response['status']); //retornamos la respuesta en formato json con el estado de la respuesta
    }

    public function destroy($id)
    {
        if (isset($id)) { // Verifica si el ID está definido
            // Llama al método del repositorio para eliminar el pedido usando el procedimiento almacenado
            $delete = $this->pedidoRepo->eliminarPedido($id);

            if ($delete) { // Verifica si la eliminación fue exitosa
                $response = [
                    "status" => 200, // Estado de la respuesta
                    "message" => "Pedido eliminado" // Mensaje de la respuesta
                ];
            } else {
                $response = [
                    "status" => 400, // Estado de la respuesta
                    "message" => "No se pudo eliminar el recurso, compruebe que exista" // Mensaje de error
                ];
            }
        } else {
            $response = [
                "status" => 406, // Estado de la respuesta
                "message" => "Falta el identificador del recurso a eliminar" // Mensaje de error
            ];
        }

        return response()->json($response, $response['status']); // Retorna la respuesta en formato JSON con el estado
    }


    public function update(Request $request, $id)
    {
        $data_input = $request->input('data', null); // Obtenemos los datos del request en formato JSON
        if ($data_input) {
            $data = json_decode($data_input, true); // Decodificamos los datos en formato JSON
            if (is_array($data)) {
                $data = array_map('trim', $data); // Eliminamos los espacios en blanco de los datos
                $rules = [
                    'estado' => 'required'
                ];
                $isValid = Validator::make($data, $rules); // Validamos los datos con las reglas definidas
                if (!$isValid->fails()) {
                    // Añadimos el ID del pedido a los datos
                    $data['idPedido'] = $id;

                    // Llamada al método del repositorio para actualizar el pedido usando el procedimiento almacenado
                    $result = $this->pedidoRepo->modificarPedido($data);

                    if ($result) { // Verificamos si la actualización fue exitosa
                        $response = [
                            "status" => 200,
                            "message" => "Pedido actualizado correctamente",
                            "pedido" => $data
                        ];
                    } else {
                        $response = [
                            "status" => 400, // Estado de error
                            "message" => "No se pudo actualizar el pedido" // Mensaje de error
                        ];
                    }
                } else {
                    $response = [
                        "status" => 406, // Estado de la respuesta
                        "message" => "Datos inválidos", // Mensaje de error
                        "errors" => $isValid->errors() // Errores de validación
                    ];
                }
            } else {
                $response = [
                    "status" => 400, // Estado de error
                    "message" => "Error en el formato de los datos JSON" // Mensaje de error
                ];
            }
        } else {
            $response = [
                "status" => 400, // Estado de error
                "message" => "No se encontró el objeto data" // Mensaje de error
            ];
        }

        return response()->json($response, $response['status']); // Retornamos la respuesta en formato JSON
    }

}
