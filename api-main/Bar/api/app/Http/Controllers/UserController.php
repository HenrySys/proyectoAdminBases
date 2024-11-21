<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Http\Response;
use App\Models\User;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Validator;
use App\Repositories\UserRepository;

class UserController extends Controller
{
    protected $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }
    public function index()
    {
        $users = User::all();
        $response = [
            'status' => 200,
            'data' => $users
        ];
        return response()->json($response, $response['status']);
    }

    public function show($id)
    {
        $user = User::find($id);
        if ($user) {
            $response = [
                'status' => 200,
                'data' => $user
            ];
        } else {
            $response = [
                'status' => 404,
                'message' => 'User not found'
            ];
        }
        return response()->json($response, $response['status']);
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
                    'username' => 'required',
                    'email' => 'required|email',
                    'password_hash' => 'required',

                ];

                // Validamos los datos con las reglas definidas
                $isValid = Validator::make($data, $rules);

                if (!$isValid->fails()) {

                    if (isset($data['password_hash'])) {
                        $data['password_hash'] = hash('sha256', $data['password_hash']);
                    }
                    // Intentamos crear el usuario llamando al repositorio
                    $result = $this->userRepo->crearUsuario($data);

                    if ($result) {
                        // Respuesta en caso de éxito
                        $response = [
                            "status" => 201, // Estado de la respuesta
                            "message" => "Usuario creado", // Mensaje de éxito
                            "data" => $result // Datos de la respuesta, asumiendo que devuelve el usuario creado
                        ];
                    } else {
                        // Error en la base de datos al crear el usuario
                        $response = [
                            "status" => 500, // Estado de la respuesta
                            "message" => "Error al crear el usuario en la base de datos" // Mensaje de error
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


    public function update(Request $request)
    {
        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);
            $rules = [
                'username' => 'required|alpha',
                'email' => 'required|email',
                'password_hash' => 'required',

            ];
            $isValid = Validator::make($data, $rules);
            if (!$isValid->fails()) {

                $jwt = new JwtAuth();
                $old = $jwt->checkToken($request->header('bearertoken'), true)->iss;
                $user = User::find($old);
                $user->name = $data['name'];
                $user->email = $data['email'];
                $user->password = hash('sha256', $data['password']);
                $user->rol = $data['rol'];
                $user->phone = $data['phone'];
                $user->lastName = $data['lastName'];
                $user->address = $data['address'];
                $user->image = $data['image'];
                $user->save();
                $response = array(
                    'status' => 201,
                    'message' => 'Usuario modificado',
                    'data' => $user
                );
            } else {
                $response = array(
                    'status' => 406,
                    'message' => 'Datos inválidos',
                    'errors' => $isValid->errors()
                );
            }
        } else {
            $response = array(
                'status' => 400,
                'message' => 'No se encontró el objeto data'
            );
        }
        return response()->json($response, $response['status']);
    }

    public function updateRol(Request $request, $id)
    {
        $data_input = $request->input('data', null);
        if ($data_input) {
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);
            $rules = [
                'rol' => 'required'
            ];
            $isValid = \validator($data, $rules);
            if (!$isValid->fails()) {
                $user = User::find($id);
                $user->rol = $data['rol'];
                $user->save();
                $response = array(
                    'status' => 201,
                    'message' => 'Usuario modificado',
                    'data' => $user
                );
            } else {
                $response = array(
                    'status' => 406,
                    'message' => 'Datos inválidos',
                    'errors' => $isValid->errors()
                );
            }
        } else {
            $response = array(
                'status' => 400,
                'message' => 'No se encontró el objeto data'
            );
        }
        return response()->json($response, $response['status']);
    }


    public function destroy($id)
    {
        $user = User::find($id);
        if ($user) {

            $delete = $this->userRepo->deleteUsuario($id);
            if ($delete) {
                $response = [
                    'status' => 200,
                    'message' => 'Usuario eliminado'
                ];
            } else {
                $response = [
                    'status' => 500,
                    'message' => 'Error al eliminar el usuario'
                ];
            }
        } else {
            $response = [
                'status' => 404,
                'message' => 'User not found'
            ];
        }
        return response()->json($response, $response['status']);
    }

    public function login(Request $request)
    {
        $data_input = $request->input('data', null);
        $data = json_decode($data_input, true);

        // Verificar que $data sea un arreglo antes de usar array_map
        if (is_array($data)) {
            $data = array_map('trim', $data);
        } else {
            return response()->json([
                'status' => 400,
                'message' => 'Error en el formato de los datos JSON'
            ], 400);
        }

        $rules = ['email' => 'required', 'password_hash' => 'required'];
        $isValid = Validator::make($data, $rules);

        if (!$isValid->fails()) {
            $jwt = new JwtAuth();
            $response = $jwt->getToken($data['email'], $data['password_hash']);

            return response()->json($response);
        } else {
            $response = [
                'status' => 406,
                'message' => 'Error en la validación de los datos',
                'errors' => $isValid->errors(),
            ];
            return response()->json($response, 406);
        }
    }




    public function getIdentity(Request $request)
    {
        $jwt = new JwtAuth();
        $token = $request->header('bearertoken');
        if (isset($token)) {
            $response = $jwt->checkToken($token, true);
        } else {
            $response = [
                'status' => 404,
                'message' => 'Token (bearertoken) no encontrado',
            ];
        }
        return response()->json($response);
    }
}
