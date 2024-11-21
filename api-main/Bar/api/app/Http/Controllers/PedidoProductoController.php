<?php

namespace App\Http\Controllers;

use App\Models\PedidoProducto;
use Illuminate\Http\Request;
use App\Models\PedidoServicio;
use Illuminate\Support\Facades\Validator;
use App\Repositories\PedidoProductoRepository;

class PedidoProductoController extends Controller
{
    protected $pedidoProductoRepo;

    // Inyecta el repositorio a través del constructor
    public function __construct(PedidoProductoRepository $pedidoProductoRepo)
    {
        $this->pedidoProductoRepo = $pedidoProductoRepo;
    }

    public function index()
    {
        $data = PedidoProducto::all(); //obtenemos todos los registros de la tabla vehiculo y los guardamos en la variable data
        $response = array(
            "status" => 200, //estado de la respuesta
            "message" => "Todos los registros de los Pedidos Productos", //mensaje de la respuesta
            "data" => $data //datos de la respuesta que en este caso son todos los registros de la tabla vehiculo
        );
        return response()->json($response, 200); //retornamos la respuesta en formato json con un estado 200
    }

    public function store(Request $request)
    {
        // Recibimos un request que contiene los datos a guardar
        $data_input = $request->input('data', null); // Obtenemos los datos del request en formato JSON

        if ($data_input) {
            $data = json_decode($data_input, true); // Decodificamos el JSON a un arreglo

            if (is_array($data)) {
                $data = array_map('trim', $data); // Eliminamos los espacios en blanco de los datos

                // Convertimos ciertos campos a enteros
                $data['idPedido'] = intval($data['idPedido']);
                $data['idProducto'] = intval($data['idProducto']);

                // Definimos las reglas de validación
                $rules = [
                    'idPedido' => 'required',
                    'idProducto' => 'required',

                ];

                // Validamos los datos con las reglas definidas
                $isValid = Validator::make($data, $rules);

                if (!$isValid->fails()) {
                    // Intentamos crear el pedidoProducto llamando al repositorio
                    $result = $this->pedidoProductoRepo->crearPedidoProducto($data);

                    if ($result) {
                        // Respuesta en caso de éxito
                        $response = [
                            "status" => 201, // Estado de la respuesta
                            "message" => "pedidoProducto creado", // Mensaje de éxito
                            "data" => $data // Datos de la respuesta
                        ];
                    } else {
                        // Error en la base de datos al crear el pedidoProducto
                        $response = [
                            "status" => 500, // Estado de la respuesta
                            "message" => "Error al crear el pedidoProducto en la base de datos" // Mensaje de error
                        ];
                    }
                } else {
                    // Respuesta en caso de fallo en la validación
                    $response = [
                        "status" => 406, // Estado de la respuesta
                        "message" => "Datos inválidos", // Mensaje de error
                        "errors" => $isValid->errors() // Errores de validación
                    ];
                }
            } else {
                // Error en el formato del JSON
                $response = [
                    "status" => 400, // Estado de la respuesta
                    "message" => "Error en el formato de los datos JSON" // Mensaje de error
                ];
            }
        } else {
            // No se encontró el objeto data
            $response = [
                "status" => 400, // Estado de la respuesta
                "message" => "No se encontró el objeto data" // Mensaje de error
            ];
        }

        return response()->json($response, $response['status']); // Retornamos la respuesta en formato JSON con su estado
    }



    public function show($id)
    {
        $data = PedidoProducto::find($id); //buscamos un registro de la tabla cliente con el id recibido y lo guardamos en la variable data
        if (is_object($data)) { //verificamos si la variable data es un objeto
            $data = $data->load('pedido');
            $data = $data->load('producto');
            $response = array(
                "status" => 200, //estado de la respuesta
                "message" => "Datos de pedidoProducto", //mensaje de la respuesta
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

    public function destroySP($id)
    {
        if (isset($id)) { //isset = verifica si una variable esta definida, en este caso si el id esta definido
            $delete = $this->pedidoProductoRepo->eliminarPedidoProductoSP($id);; //buscamos un registro de la tabla PedidoProducto con el id recibido y lo eliminamos y guardamos el resultado en la variable delete
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "PedidoProducto eliminado" //mensaje de la respuesta
                );
                return response()->json($response, $response['status']); //retornamos la respuesta en formato json con el estado de la respuesta
            } else {
                $response = array(
                    "status" => 400, //estado de la respuesta
                    "message" => "No se pudo eliminar el recurso, compruebe que exista" //mensaje de la respuesta
                );
            }
        } else {
            $response = array(
                "status" => 406, //estado de la respuesta
                "message" => "Falta el identificador del recurso a eliminar" //mensaje de la respuesta
            );
        }
        return response()->json($response, $response['status']); //retornamos la respuesta en formato json con el estado de la respuesta
    }
    public function destroyNSP($id)
    {
        if (isset($id)) { //isset = verifica si una variable esta definida, en este caso si el id esta definido
            $delete = $this->pedidoProductoRepo->eliminarPedidoProductoNSP($id);; //buscamos un registro de la tabla PedidoProducto con el id recibido y lo eliminamos y guardamos el resultado en la variable delete
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "PedidoProducto eliminado" //mensaje de la respuesta
                );
                return response()->json($response, $response['status']); //retornamos la respuesta en formato json con el estado de la respuesta
            } else {
                $response = array(
                    "status" => 400, //estado de la respuesta
                    "message" => "No se pudo eliminar el recurso, compruebe que exista" //mensaje de la respuesta
                );
            }
        } else {
            $response = array(
                "status" => 406, //estado de la respuesta
                "message" => "Falta el identificador del recurso a eliminar" //mensaje de la respuesta
            );
        }
        return response()->json($response, $response['status']); //retornamos la respuesta en formato json con el estado de la respuesta
    }

    public function update(Request $request, $id)
    {
        // Obtenemos los datos del request en formato JSON
        $data_input = $request->input('data', null);

        if ($data_input) {
            // Decodificamos los datos JSON a un array y eliminamos espacios en blanco
            $data = json_decode($data_input, true);
            if (is_array($data)) {
                $data = array_map('trim', $data); // Eliminamos los espacios en blanco de los datos
                $data['idPedido'] = intval($data['idPedido']);
                $data['idProducto'] = intval($data['idProducto']);

                // Definimos las reglas de validación
                $rules = [
                    'idPedido' => 'required|integer',
                    'idProducto' => 'required|integer',
                    'descripcion' => 'required' // Validación para la cantidad
                ];

                // Validamos los datos con las reglas
                $isValid = Validator::make($data, $rules);
                if (!$isValid->fails()) {
                    // Buscamos el pedidoProd con Eloquent que deseas actualizar
                    $pedidoProd = PedidoProducto::find($id);

                    if (!$pedidoProd) {
                        // Si no se encuentra el pedidoProd, retorna un mensaje de error
                        return response()->json([
                            "status" => 404,
                            "message" => "Pedido Producto no encontrado"
                        ], 404);
                    }

                    // Llamamos al procedimiento almacenado para modificar el pedido producto
                    $resultado = $this->pedidoProductoRepo->ModificarPedidoProducto($id, $data);

                    if ($resultado) {
                        // Retorna una respuesta exitosa
                        return response()->json([
                            "status" => 200,
                            "message" => "Pedido Producto actualizado correctamente",
                            "data" => $data
                        ], 200);
                    } else {
                        // Manejo de error si la actualización falló
                        return response()->json([
                            "status" => 500,
                            "message" => "Error al actualizar el Pedido Producto en la base de datos"
                        ], 500);
                    }
                } else {
                    // Retorna los errores de validación
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
                "message" => "No se encontró el objeto 'data'"
            ], 400);
        }
    }
}
