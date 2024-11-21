<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Repositories\ClienteRepository;
use App\Repositories\UserRepository;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ClienteController extends Controller
{

    protected $clienteRepo;
    protected $userRepo;

    public function __construct(ClienteRepository $clienteRepo, UserRepository $userRepo)
    {
        $this->clienteRepo = $clienteRepo;
        $this->userRepo = $userRepo;
    }


    public function index()
    {
        $data = Cliente::all(); //obtenemos todos los registros de la tabla vehiculo y los guardamos en la variable data
        $response = array(
            "status" => 200, //estado de la respuesta
            "message" => "Todos los registros de los cliente", //mensaje de la respuesta
            "data" => $data //datos de la respuesta que en este caso son todos los registros de la tabla vehiculo
        );
        return response()->json($response, 200); //retornamos la respuesta en formato json con un estado 200
    }

    public function store(Request $request)
    {
        // Obtener los datos completos de la solicitud (con la clave 'data')
        $data_input = $request->input('data', null); // Obtenemos los datos en formato JSON

        if ($data_input) {
            $data = json_decode($data_input, true); // Decodificamos el JSON a un arreglo

            if (is_array($data)) {
                // Extraemos los datos de 'usuario' y 'cliente'
                $userData = isset($data['usuario']) ? array_map('trim', $data['usuario']) : [];
                $clientData = isset($data['cliente']) ? array_map('trim', $data['cliente']) : [];

                // Reglas de validación
                $userRules = [
                    'username' => 'required',
                    'email' => 'required|email',
                    'password_hash' => 'required',
                ];
                $clientRules = [
                    'nombre' => 'required',
                    'apellido' => 'required',
                    'direccion' => 'required',
                    'telefono' => 'required',
                ];

                // Validación de los datos de usuario y cliente
                $userValidation = Validator::make($userData, $userRules);
                $clientValidation = Validator::make($clientData, $clientRules);

                // Verificar si alguna de las validaciones falla
                if ($userValidation->fails() || $clientValidation->fails()) {
                    $response = [
                        'status' => 406,
                        'message' => 'Datos inválidos',
                        'errors' => array_merge(
                            $userValidation->errors()->toArray(),
                            $clientValidation->errors()->toArray()
                        )
                    ];
                } else {
                    // Hashear la contraseña
                    if (isset($userData['password_hash'])) {
                        $userData['password_hash'] = hash('sha256', $userData['password_hash']);
                    }

                    // Combinar los datos de usuario y cliente
                    $mergedData = array_merge($userData, $clientData);

                    // Intentamos crear el usuario y cliente llamando al repositorio
                    $result = $this->userRepo->crearUsuarioCliente($mergedData);

                    if ($result) {
                        // Si la creación fue exitosa
                        $response = [
                            'status' => 201,
                            'message' => 'Usuario y Cliente creados correctamente',
                            'data' => $mergedData
                        ];
                    } else {
                        // Si hubo un error en la base de datos
                        $response = [
                            'status' => 500,
                            'message' => 'Error al crear el usuario y cliente en la base de datos'
                        ];
                    }
                }
            } else {
                $response = [
                    'status' => 400,
                    'message' => 'Error en el formato de los datos JSON'
                ];
            }
        } else {
            $response = [
                'status' => 400,
                'message' => 'No se encontró el objeto data'
            ];
        }

        return response()->json($response, $response['status']);
    }


    public function show($id)
    {
        $data = Cliente::find($id); //buscamos un registro de la tabla cliente con el id recibido y lo guardamos en la variable data
        if (is_object($data)) { //verificamos si la variable data es un objeto
            $response = array(
                "status" => 200, //estado de la respuesta
                "message" => "Datos de cliente", //mensaje de la respuesta
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

            $cliente = Cliente::where('id', $id)->first(); // Busca la categoría por ID y la guarda en la variable category
            if (!$cliente) { //verifica si la variable category es falsa
                $response = [
                    "status" => 404,
                    "message" => "cliente no encontrado"
                ];
                return response()->json($response, $response['status']);
            }

            // Verifica si hay posts relacionados


            $delete = Cliente::where('id', $id)->delete(); //buscamos un registro de la tabla cliente con el id recibido y lo eliminamos y guardamos el resultado en la variable delete
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "cliente eliminado" //mensaje de la respuesta
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

                // Definimos las reglas de validación
                $rules = [
                    'cedula' => 'required',
                    'nombre' => 'required',
                    'apellido' => 'required',
                    'correo' => 'required|email', // Validación de formato de correo
                    'telefono' => 'required|regex:/^[0-9]{10}$/', // Validación simple de un número de teléfono de 10 dígitos
                    'direccion' => 'required'
                ];

                // Validamos los datos con las reglas
                $validator = Validator::make($data, $rules);

                if (!$validator->fails()) {
                    // Buscamos el cliente a actualizar
                    $cliente = Cliente::find($id);

                    if (!$cliente) {
                        // Si no se encuentra el cliente, retorna un mensaje de error
                        return response()->json([
                            "status" => 404,
                            "message" => "Cliente no encontrado"
                        ], 404);
                    }

                    // Llamamos al procedimiento almacenado para modificar el cliente
                    $resultado = $this->clienteRepo->ModificarCliente($data, $id);

                    if ($resultado) {
                        // Retorna una respuesta exitosa
                        return response()->json([
                            "status" => 200,
                            "message" => "Cliente actualizado correctamente",
                            "cliente" => $data // Retornamos los datos del cliente actualizado
                        ], 200);
                    } else {
                        // Manejo de error si la actualización falló
                        return response()->json([
                            "status" => 500,
                            "message" => "Error al actualizar el cliente en la base de datos"
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
                // Retorna un mensaje de error si el formato de los datos JSON es incorrecto
                return response()->json([
                    "status" => 400,
                    "message" => "Error en el formato de los datos JSON"
                ], 400);
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
