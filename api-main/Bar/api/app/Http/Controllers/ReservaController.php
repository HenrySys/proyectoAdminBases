<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use Illuminate\Support\Facades\Validator;
use App\Repositories\ReservaRepository;


class ReservaController extends Controller
{
    protected $reservaRepo;

    public function __construct(ReservaRepository $reservaRepo)
    {
        $this->reservaRepo = $reservaRepo;
    }

    public function index()
    {
        $data = Reserva::all(); //obtenemos todos los registros de la tabla vehiculo y los guardamos en la variable data
        $response = array(
            "status" => 200, //estado de la respuesta
            "message" => "Todos los registros de las Reservas", //mensaje de la respuesta
            "data" => $data //datos de la respuesta que en este caso son todos los registros de la tabla vehiculo
        );
        return response()->json($response, 200); //retornamos la respuesta en formato json con un estado 200
    }
    public function store(Request $request)
    {
        // Obtenemos los datos del request en formato JSON y los guardamos en la variable $data_input; si no hay datos, se guarda un null
        $data_input = $request->input('data', null);

        if ($data_input) {
            // Decodificamos los datos JSON y los guardamos en la variable $data
            $data = json_decode($data_input, true);

            if (is_array($data)) {
                // Eliminamos los espacios en blanco de los datos recibidos
                $data = array_map('trim', $data);

                // Convertimos los valores necesarios a su tipo correcto
                $data['idUser'] = intval($data['idUser']);
                $data['idMesa'] = intval($data['idMesa']);

                // Definimos las reglas de validación para los datos
                $rules = [
                    'idUser' => 'required|integer',
                    'idMesa' => 'required|integer',
                    'fecha' => 'required|date', // Validación de que sea una fecha
                    'hora' => 'required' // Puede incluir más validaciones según formato de hora
                ];

                // Validamos los datos con las reglas definidas
                $validator = Validator::make($data, $rules);

                // Verificamos si la validación tiene errores
                if (!$validator->fails()) {
                    // Intentamos crear la reserva llamando al repositorio
                    $result = $this->reservaRepo->crearReserva($data);

                    // Verificamos si la creación de la reserva fue exitosa
                    if ($result) {
                        return response()->json([
                            "status" => 201,
                            "message" => "Reserva creada",
                            "data" => $result // Incluimos los datos de la reserva creada en la respuesta
                        ], 201);
                    } else {
                        // Si la creación no fue exitosa, devolvemos un error de servidor
                        return response()->json([
                            "status" => 500,
                            "message" => "Error al crear la reserva de la base de datos"
                        ], 500);
                    }
                } else {
                    // Si hay errores de validación, los devolvemos en la respuesta
                    return response()->json([
                        "status" => 406,
                        "message" => "Datos inválidos",
                        "errors" => $validator->errors()
                    ], 406);
                }
            } else {
                // Error en el formato de los datos JSON
                return response()->json([
                    "status" => 400,
                    "message" => "Error en el formato de los datos JSON"
                ], 400);
            }
        } else {
            // No se encontró el objeto data en el request
            return response()->json([
                "status" => 400,
                "message" => "No se encontró el objeto data"
            ], 400);
        }
    }


    public function show($id)
    {
        $data = Reserva::find($id); //buscamos un registro de la tabla cliente con el id recibido y lo guardamos en la variable data
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
        if (isset($id)) { //isset = verifica si una variable esta definida, en este caso si el id esta definido

            $delete = $this->reservaRepo->eliminarReserva($id);//buscamos un registro de la tabla category con el id recibido y lo eliminamos y guardamos el resultado en la variable delete
            //$delete=Category::destroy($id); //otra forma de eliminar un registro
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "Reserva eliminada" //mensaje de la respuesta
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
        // Obtenemos los datos del request en formato JSON y los guardamos en la variable $data_input; si no hay datos, se guarda un null
        $data_input = $request->input('data', null);

        if ($data_input) {
            // Decodificamos los datos JSON y los guardamos en la variable $data
            $data = json_decode($data_input, true);

            if (is_array($data)) {
                // Eliminamos los espacios en blanco de los datos recibidos
                $data = array_map('trim', $data);

                // Convertimos los valores necesarios a su tipo correcto
                $data['idMesa'] = intval($data['idMesa']);
                $data['idReserva'] = intval($data['idReserva']);

                // Definimos las reglas de validación para los datos
                $rules = [
                    'idMesa' => 'required|integer',
                    'idReserva' => 'required|integer',
                    'fecha' => 'required',
                    'hora' => 'required',
                ];

                // Validamos los datos con las reglas definidas
                $validator = Validator::make($data, $rules);

                // Verificamos si la validación tiene errores
                if (!$validator->fails()) {
                    // Intentamos buscar la reserva que se desea actualizar
                    $reserva = Reserva::find($id);

                    // Si no se encuentra la reserva, retornamos un mensaje de error
                    if (!$reserva) {
                        return response()->json([
                            "status" => 404,
                            "message" => "Reserva no encontrada"
                        ], 404);
                    }

                    // Intentamos actualizar la reserva con los nuevos datos
                    $updated = $this->reservaRepo->modificarReserva($id, $data);

                    // Verificamos si la actualización fue exitosa
                    if ($updated) {
                        return response()->json([
                            "status" => 200,
                            "message" => "Reserva actualizada correctamente",
                            "data" => $updated
                        ], 200);
                    } else {
                        // Si la actualización no fue exitosa, devolvemos un error de servidor
                        return response()->json([
                            "status" => 500,
                            "message" => "Error al actualizar la reserva"
                        ], 500);
                    }
                } else {
                    // Si hay errores de validación, los devolvemos en la respuesta
                    return response()->json([
                        "status" => 406,
                        "message" => "Datos inválidos",
                        "errors" => $validator->errors()
                    ], 406);
                }
            } else {
                // Error en el formato de los datos JSON
                return response()->json([
                    "status" => 400,
                    "message" => "Error en el formato de los datos JSON"
                ], 400);
            }
        } else {
            // No se encontró el objeto data en el request
            return response()->json([
                "status" => 400,
                "message" => "No se encontró el objeto data"
            ], 400);
        }
    }
}
