<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factura;
use App\Repositories\FacturaRepository;
use Illuminate\Support\Facades\Validator;

class FacturaController extends Controller
{
    protected $facturaRepo;

    public function __construct(FacturaRepository $facturaRepo)
    {
        $this->facturaRepo = $facturaRepo;
    }
    public function index()
    {
        $data = Factura::all(); //obtenemos todos los registros de la tabla vehiculo y los guardamos en la variable data
        $response = array(
            "status" => 200, //estado de la respuesta
            "message" => "Todos los registros de Facturas", //mensaje de la respuesta
            "data" => $data //datos de la respuesta que en este caso son todos los registros de la tabla vehiculo
        );
        return response()->json($response, 200); //retornamos la respuesta en formato json con un estado 200
    }

    public function store(Request $request)
    {
        // Recibimos un request que contendrá los datos a guardar
        $data_input = $request->input('data', null); // Obtenemos los datos del request en formato JSON

        if ($data_input) {
            // Decodificamos los datos JSON y los guardamos en la variable $data
            $data = json_decode($data_input, true);

            if (is_array($data)) {
                // Eliminamos los espacios en blanco de los datos
                $data = array_map('trim', $data);

                $data['idEmplaado'] = intval($data['idEmplaado']);
                $data['idPedido'] = intval($data['idPedido']);
                $data['idCliente'] = intval($data['idCliente']);

                // Definimos las reglas de validación
                $rules = [
                    'idCliente' => 'required|exists:cliente,id', // Validación de existencia del cliente
                    'idPedido' => 'required|exists:pedido,id', // Validación de existencia del pedido
                    'idEmpleado' => 'required|exists:empleado,id' // Validación de existencia del emplado
                ];

                // Validamos los datos con las reglas definidas
                $validator = Validator::make($data, $rules);
                if (!$validator->fails()) {
                    // Llamamos al repositorio para crear la factura usando un procedimiento almacenado
                    $result = $this->facturaRepo->crearFactura($data);

                    if ($result) {
                        // Si la creación fue exitosa
                        return response()->json([
                            "status" => 201,
                            "message" => "Factura creada",
                            "factura" => $data
                        ], 201);
                    } else {
                        // Si hubo un error en la base de datos
                        return response()->json([
                            "status" => 500,
                            "message" => "Error al crear la factura en la base de datos"
                        ], 500);
                    }
                } else {
                    // Retorna los errores de validación
                    return response()->json([
                        "status" => 406, // Estado de la respuesta
                        "message" => "Datos inválidos", // Mensaje de la respuesta
                        "errors" => $validator->errors() // Datos de la respuesta que son los errores de validación
                    ], 406);
                }
            } else {
                // Retorna un mensaje de error si no se encuentra el formato esperado
                return response()->json([
                    "status" => 400, // Estado de la respuesta
                    "message" => "Error en el formato de los datos JSON" // Mensaje de la respuesta
                ], 400);
            }
        } else {
            // Retorna un mensaje de error si no se encuentra el objeto 'data'
            return response()->json([
                "status" => 400, // Estado de la respuesta
                "message" => "No se encontró el objeto 'data'" // Mensaje de la respuesta
            ], 400);
        }
    }


    public function show($id)
    {
        $data = Factura::find($id); //buscamos un registro de la tabla cliente con el id recibido y lo guardamos en la variable data
        if (is_object($data)) { //verificamos si la variable data es un objeto
            $data = $data->load('detalleFactura');
            $response = array(
                "status" => 200, //estado de la respuesta
                "message" => "Datos de factura", //mensaje de la respuesta
                "category" => $data //datos de la respuesta que en este caso es el objeto data
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
        if (isset($id)) { //isset = verifica si una variable esta definida, en este caso si el id esta definido

            $Fact = Factura::where('id', $id)->first(); // Busca la categoría por ID y la guarda en la variable category
            if (!$Fact) { //verifica si la variable category es falsa
                $response = [
                    "status" => 404,
                    "message" => "factura no encontrada"
                ];
                return response()->json($response, $response['status']);
            }

            $delete = $this->facturaRepo->eliminarFactura($id);
            //$delete=Category::destroy($id); //otra forma de eliminar un registro
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "factura eliminada" //mensaje de la respuesta
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
            // Decodificamos los datos JSON y los guardamos en la variable $data
            $data = json_decode($data_input, true);

            if (is_array($data)) {
                // Eliminamos los espacios en blanco de los datos
                $data = array_map('trim', $data);

                // Convertimos los valores a los tipos correctos
                $data['idEmpleado'] = intval($data['idEmpleado']);
                $data['idPedido'] = intval($data['idPedido']);
                $data['idCliente'] = intval($data['idCliente']);

                // Definimos las reglas de validación
                $rules = [
                    'idEmpleado' => 'required',
                    'idCliente' => 'required',
                    'idPedido' => 'required'
                ];

                // Validamos los datos con las reglas definidas
                $isValid = Validator::make($data, $rules);
                if (!$isValid->fails()) {
                    // Usamos Eloquent para buscar la factura por ID
                    $factura = Factura::find($id);

                    if (!$factura) {
                        // Si no se encuentra la factura, retorna un mensaje de error
                        $response = [
                            "status" => 404,
                            "message" => "Factura no encontrada"
                        ];
                    } else {

                        // Usamos el repositorio para guardar la factura actualizada
                        $result = $this->facturaRepo->crearFactura($factura);

                        if ($result) {
                            // Si la actualización fue exitosa
                            $response = [
                                "status" => 200,
                                "message" => "Factura actualizada correctamente",
                                "factura" => $factura // Retornamos el objeto actualizado
                            ];
                        } else {
                            // Si hubo un error en la base de datos
                            $response = [
                                "status" => 500,
                                "message" => "Error al actualizar la factura en la base de datos"
                            ];
                        }
                    }
                } else {
                    // Retorna los errores de validación
                    $response = [
                        "status" => 406, // Estado de la respuesta
                        "message" => "Datos inválidos", // Mensaje de la respuesta
                        "errors" => $isValid->errors() // Datos de la respuesta que son los errores de validación
                    ];
                }
            } else {
                // Retorna un mensaje de error si no se encuentra el formato esperado
                $response = [
                    "status" => 400, // Estado de la respuesta
                    "message" => "Error en el formato de los datos JSON" // Mensaje de la respuesta
                ];
            }
        } else {
            // Retorna un mensaje de error si no se encuentra el objeto 'data'
            $response = [
                "status" => 400, // Estado de la respuesta
                "message" => "No se encontró el objeto 'data'" // Mensaje de la respuesta
            ];
        }

        return response()->json($response, $response['status']); // Retornamos la respuesta en formato JSON
    }
}
