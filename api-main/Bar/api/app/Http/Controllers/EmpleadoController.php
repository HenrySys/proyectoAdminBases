<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empleado;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Repositories\EmpleadoRepository;
use App\Repositories\UserRepository;

class EmpleadoController extends Controller
{
    private $empleadoRepo;
    private $userRepo;

    public function __construct(EmpleadoRepository $empleadoRepo, UserRepository $userRepo)
    {
        $this->empleadoRepo = $empleadoRepo;
        $this->userRepo = $userRepo;
    }


    public function index()
    {
        $data = Empleado::all(); //obtenemos todos los registros de la tabla vehiculo y los guardamos en la variable data
        $response = array(
            "status" => 200, //estado de la respuesta
            "message" => "Todos los registros de los Empleados", //mensaje de la respuesta
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
                // Extraemos los datos de 'usuario' y 'empleado' del arreglo
                $userData = isset($data['usuario']) ? array_map('trim', $data['usuario']) : [];
                $employeeData = isset($data['empleado']) ? array_map('trim', $data['empleado']) : [];

                // Reglas de validación
                $userRules = [
                    'username' => 'required',
                    'email' => 'required|email',
                    'password_hash' => 'required',
                ];
                $clientRules = [
                    'nombre' => 'required',
                    'apellido' => 'required',
                    'telefono' => 'required',
                ];

                // Validación de los datos de usuario y cliente
                $userValidation = Validator::make($userData, $userRules);
                $employeeValidation = Validator::make($employeeData, $clientRules);

                // Verificar si alguna de las validaciones falla
                if ($userValidation->fails() || $employeeValidation->fails()) {
                    $response = [
                        'status' => 406,
                        'message' => 'Datos inválidos',
                        'errors' => array_merge(
                            $userValidation->errors()->toArray(),
                            $employeeValidation->errors()->toArray()
                        )
                    ];
                } else {
                    // Hashear la contraseña
                    if (isset($userData['password_hash'])) {
                        $userData['password_hash'] = hash('sha256', $userData['password_hash']);
                    }

                    // Combinar los datos de usuario y cliente
                    $mergedData = array_merge($userData, $employeeData);

                    // Intentamos crear el usuario y cliente llamando al repositorio
                    $result = $this->userRepo->crearUsuarioEmpleado($mergedData);

                    if ($result) {
                        // Si la creación fue exitosa
                        $response = [
                            'status' => 201,
                            'message' => 'Usuario y Empleado creados correctamente',
                            'data' => $mergedData
                        ];
                    } else {
                        // Si hubo un error en la base de datos
                        $response = [
                            'status' => 500,
                            'message' => 'Error al crear el usuario y empleado en la base de datos'
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
        $data = Empleado::find($id); //buscamos un registro de la tabla cliente con el id recibido y lo guardamos en la variable data
        if (is_object($data)) { //verificamos si la variable data es un objeto
            $data = $data->load('users'); //cargamos los cliente relacionados con el cliente en la variable data

            $response = array(
                "status" => 200, //estado de la respuesta
                "message" => "Datos del Empleado", //mensaje de la respuesta
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

            $delete = $this->empleadoRepo->eliminarEmpleado($id); //buscamos un registro de la tabla producto con el id recibido y lo eliminamos y guardamos el resultado en la variable delete
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "Empleado eliminado" //mensaje de la respuesta
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
            $data['id'] = intval($data['id']);

            // Definimos las reglas de validación
            $rules = [
                'nombre' => 'required',
                'apellido' => 'required',
                'correo' => 'required|email', // Validación de formato de correo
                'direccio' => 'required',
                'telefono' => 'required|regex:/^[0-9]{10}$/' // Validación simple de un número de teléfono de 10 dígitos
            ];

            // Validamos los datos con las reglas
            $validator = Validator::make($data, $rules);

            if (!$validator->fails()) {
                // Buscamos el empleado a actualizar
                $empleado = Empleado::find($id);

                if (!$empleado) {
                    // Si no se encuentra el empleado, retorna un mensaje de error
                    return response()->json([
                        "status" => 404,
                        "message" => "Empleado no encontrado"
                    ], 404);
                }

                // Llamamos al procedimiento almacenado para modificar el empleado
                $resultado = $this->empleadoRepo->ModificarEmpleado($data);

                if ($resultado) {
                    // Retorna una respuesta exitosa
                    return response()->json([
                        "status" => 200,
                        "message" => "Empleado actualizado correctamente",
                        "data" => $data // Retornamos los datos del empleado actualizado
                    ], 200);
                } else {
                    // Manejo de error si la actualización falló
                    return response()->json([
                        "status" => 500,
                        "message" => "Error al actualizar el empleado en la base de datos"
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
