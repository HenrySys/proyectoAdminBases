<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\ProveedorRepository;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use App\Models\Proveedor;

class ProveedorController extends Controller
{

    protected $proveedorRepo;

    // Inyecta el repositorio a través del constructor
    public function __construct(ProveedorRepository $proveedorRepo)
    {
        $this->proveedorRepo = $proveedorRepo;
    }

    public function index()
    {
        $data = Proveedor::all(); //obtenemos todos los registros de la tabla proveedores y los guardamos en la variable data
        $response = array(
            "status" => 200, //estado de la respuesta
            "message" => "Todos los registros de los Proveedores", //mensaje de la respuesta
            "data" => $data //datos de la respuesta que en este caso son todos los registros de la tabla vehiculo
        );
        return response()->json($response, 200); //retornamos la respuesta en formato json con un estado 200
    }

    public function store(Request $request)
    {
        // Recibimos un request que contiene los datos a guardar
        $data_input = $request->input('data', null); // Obtenemos los datos en formato JSON

        if ($data_input) {
            $data = json_decode($data_input, true); // Decodificamos el JSON a un arreglo

            if (is_array($data)) {
                $data = array_map('trim', $data); // Eliminamos espacios en blanco de los datos

                // Definimos las reglas de validación
                $rules = [
                    'nombre' => 'required',
                    'correo' => 'required|email', // Validamos que sea un correo
                    'telefono' => 'required|regex:/^[0-9]+$/|min:8' // Validamos que sea numérico y tenga al menos 8 dígitos
                ];

                // Validamos los datos con las reglas definidas
                $isValid = Validator::make($data, $rules);

                if (!$isValid->fails()) {
                    // Intentamos crear el proveedor llamando al repositorio
                    $result = $this->proveedorRepo->crearProveedor($data);

                    if ($result) {
                        // Respuesta en caso de éxito
                        $response = [
                            "status" => 201, // Estado de la respuesta
                            "message" => "Proveedor creado", // Mensaje de éxito
                            "data" => $data // Datos de la respuesta
                        ];
                    } else {
                        // Error en la base de datos al crear el proveedor
                        $response = [
                            "status" => 500, // Estado de la respuesta
                            "message" => "Error al crear el proveedor en la base de datos" // Mensaje de error
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
        $data = Proveedor::find(id: $id); //buscamos un registro de la tabla cliente con el id recibido y lo guardamos en la variable data
        if (is_object($data)) { //verificamos si la variable data es un objeto
            $response = array(
                "status" => 200, //estado de la respuesta
                "message" => "Datos del Proveedor", //mensaje de la respuesta
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
        if (isset($id)) { //isset = verifica si una variable esta definida, en este caso si el id esta definido

            $delete = $this->proveedorRepo->eliminarProveedor($id); //buscamos un registro de la tabla producto con el id recibido y lo eliminamos y guardamos el resultado en la variable delete
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "Proveedor eliminada" //mensaje de la respuesta
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
            $data = array_map('trim', $data);

            // Convertimos valores a los tipos esperados
            $data['idProveedor'] = intval($data['idProveedor']);

            // Definimos las reglas de validación
            $rules = [
                'idProveedor' => 'required|integer|exists:proveedor,id', // Verificamos que el proveedor exista
                'nombre' => 'required',
                'correo' => 'required|email', // Validación para correo electrónico
                'telefono' => 'required'
            ];

            // Validamos los datos con las reglas
            $validator = Validator::make($data, $rules);

            if (!$validator->fails()) {
                // Buscamos el proveedor a actualizar
                $proveedor = Proveedor::find($id);

                if (!$proveedor) {
                    // Si no se encuentra el proveedor, retorna un mensaje de error
                    return response()->json([
                        "status" => 404,
                        "message" => "Proveedor no encontrado"
                    ], 404);
                }

                // Llamamos al procedimiento almacenado para modificar el proveedor
                $resultado = $this->proveedorRepo->ModificarProveedor( $data);

                if ($resultado) {
                    // Retorna una respuesta exitosa
                    return response()->json([
                        "status" => 200,
                        "message" => "Proveedor actualizado correctamente",
                        "proveedor" => $data // Retornamos los datos del proveedor actualizado
                    ], 200);
                } else {
                    // Manejo de error si la actualización falló
                    return response()->json([
                        "status" => 500,
                        "message" => "Error al actualizar el Proveedor en la base de datos"
                    ], 500);
                }
            } else {
                // Retorna los errores de validación
                return response()->json([
                    "status" => 406,
                    "message" => "Datos inválidos",
                    "errors" => $validator->errors()
                ], 406);
            }
        } else {
            // Retorna un mensaje de error si no se encuentra el objeto 'data'
            return response()->json([
                "status" => 400,
                "message" => "No se encontró el objeto 'data'"
            ], 400);
        }
    }
}
