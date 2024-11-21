<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mesa;
use Illuminate\Support\Facades\Validator;
use App\Repositories\MesaRepository;

class MesaController extends Controller
{
    protected $mesaRepo;

    // Inyecta el repositorio a través del constructor
    public function __construct(MesaRepository $mesaRepo)
    {
        $this->mesaRepo = $mesaRepo;
    }

    public function index()
    {
        $data = Mesa::all(); //obtenemos todos los registros de la tabla vehiculo y los guardamos en la variable data
        $response = array(
            "status" => 200, //estado de la respuesta
            "message" => "Todos los registros de las mesas", //mensaje de la respuesta
            "data" => $data //datos de la respuesta que en este caso son todos los registros de la tabla vehiculo
        );
        return response()->json($response, 200); //retornamos la respuesta en formato json con un estado 200
    }

    public function store(Request $request)
    {
        $data_input = $request->input('data', null); // Obtenemos los datos del request en formato JSON
        if ($data_input) {
            $data = json_decode($data_input, true); // Decodificamos los datos en formato JSON
            if (is_array($data)) {
                $data = array_map('trim', $data); // Eliminamos los espacios en blanco de los datos
                $data['numero'] = intval($data['numero']);
                $data['capacidad'] = intval($data['capacidad']);

                $rules = [
                    'numero' => 'required',
                    'capacidad' => 'required'
                ];

                $isValid = Validator::make($data, $rules); // Validamos los datos con las reglas definidas
                if (!$isValid->fails()) {
                    // Llamada al método del repositorio para crear la mesa usando el procedimiento almacenado
                    $result = $this->mesaRepo->crearMesa($data);

                    if ($result) {
                        // Si la creación fue exitosa
                        $response = [
                            "status" => 201,
                            "message" => "Mesa creada",
                            "mesa" => $data // Cambié "pedido" a "mesa" para reflejar mejor la información
                        ];
                    } else {
                        // Si hubo un error en la base de datos
                        $response = [
                            "status" => 500,
                            "message" => "Error al crear la mesa en la base de datos"
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

    public function show($id)
    {
        $data = Mesa::find($id); //buscamos un registro de la tabla cliente con el id recibido y lo guardamos en la variable data
        if (is_object($data)) { //verificamos si la variable data es un objeto
            //$data = $data->load('factura');
            $response = array(
                "status" => 200, //estado de la respuesta
                "message" => "Datos de Mesa", //mensaje de la respuesta
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
            $delete = $this->mesaRepo->eliminarMesa($id);            //$delete=Category::destroy($id); //otra forma de eliminar un registro
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "Mesa eliminada" //mensaje de la respuesta
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
                $data = array_map('trim', $data); // Eliminamos espacios en blanco de los datos
                $data['idMesa'] = intval($data['idMesa']); // Aseguramos que idMesa sea un entero

                // Definimos las reglas de validación
                $rules = [
                    'idMesa' => 'required|integer|exists:mesa,id', // Verificamos que la mesa exista
                    'numero' => 'required',
                    'capacidad' => 'required|integer', // Validamos que capacidad sea un entero
                    'estado' => 'required',
                ];

                // Validamos los datos con las reglas
                $isValid = Validator::make($data, $rules);

                if (!$isValid->fails()) {
                    // Buscamos la mesa a actualizar
                    $mesa = Mesa::find($id);

                    if (!$mesa) {
                        // Si no se encuentra la mesa, retorna un mensaje de error
                        return response()->json([
                            "status" => 404,
                            "message" => "Mesa no encontrada"
                        ], 404);
                    }

                    // Llamamos al procedimiento almacenado para modificar la mesa
                    $resultado = $this->mesaRepo->ModificarMesa($id, $data);

                    if ($resultado) {
                        // Retorna una respuesta exitosa
                        return response()->json([
                            "status" => 200,
                            "message" => "Mesa actualizada correctamente",
                            "data" => $data // Retornamos los datos de la mesa actualizada
                        ], 200);
                    } else {
                        // Manejo de error si la actualización falló
                        return response()->json([
                            "status" => 500,
                            "message" => "Error al actualizar la Mesa en la base de datos"
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
                // Retorna un mensaje de error si no se encuentra el objeto 'data'
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
