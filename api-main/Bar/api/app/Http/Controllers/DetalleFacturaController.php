<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetalleFactura;
use App\Repositories\DetalleFacturaRepository;
use Illuminate\Support\Facades\Validator;

class DetalleFacturaController extends Controller
{

    protected $detalleFacRepo;

    public function __construct(DetalleFacturaRepository $detalleFacRepo)
    {
        $this->detalleFacRepo = $detalleFacRepo;
    }
    public function index()
    {
        $data = DetalleFactura::all(); //obtenemos todos los registros de la tabla vehiculo y los guardamos en la variable data
        $response = array(
            "status" => 200, //estado de la respuesta
            "message" => "Todos los registros de los Pedidos", //mensaje de la respuesta
            "data" => $data //datos de la respuesta que en este caso son todos los registros de la tabla vehiculo
        );
        return response()->json($response, 200); //retornamos la respuesta en formato json con un estado 200
    }

    public function store(Request $request)
    { //recibimos un request que contendra los datos a guardar
        $data_imput = $request->input('data', null); //obtenemos los datos del request en formato json  y los guardamos en la variable data_input si no hay datos se guarda un null
        if ($data_imput) {
            $data = json_decode($data_imput, true); //decodificamos los datos en formato json y los guardamos en la variable data
            if (is_array($data)) {
                $data = array_map('trim', $data); //eliminamos los espacios en blanco de los datos
                $data['idFactura'] = intval($data['idFactura']);
                $data['idProducto'] = intval($data['idFactura']);

                $rules = [
                    'idFactura' => 'required',
                    'idProducto' => 'required',
                ];
                $isValid = Validator::make($data, $rules); //validamos los datos con las reglas definidas en la variable rules
                if (!$isValid->fails()) {

                    $result = $this->detalleFacRepo->crearDetalleFactura($data);

                    if ($result) {
                        // Si la creación fue exitosa
                        $response = [
                            "status" => 201,
                            "message" => "Pedido creado",
                            "pedido" => $data
                        ];
                    } else {
                        // Si hubo un error en la base de datos
                        $response = [
                            "status" => 500,
                            "message" => "Error al crear el pedido en la base de datos"
                        ];
                    }
                } else {
                    $response = array(
                        "status" => 406, //estado de la respuesta
                        "message" => "Datos invalidos", //mensaje de la respuesta
                        "errors" => $isValid->errors() //datos de la respuesta que en este caso son los errores de validacion
                    );
                }
            } else {
                $response = array(
                    "status" => 400, //estado de la respuesta
                    "message" => "Error en el formato de los datos JSON", //mensaje de la respuesta
                );
            }
        } else {
            $response = array(
                "status" => 400, //estado de la respuesta
                "message" => "No se encontro el objeto data" //mensaje de la respuesta
            );
        }
        return response()->json($response, $response['status']); //retornamos la respuesta en formato json con el estado de la respuesta

    }

    public function show($id)
    {
        $data = DetalleFactura::find($id); //buscamos un registro de la tabla cliente con el id recibido y lo guardamos en la variable data
        if (is_object($data)) { //verificamos si la variable data es un objeto
            $data = $data->load('factura');
            $data = $data->load('producto');
            $response = array(
                "status" => 200, //estado de la respuesta
                "message" => "Datos de RedetalleFactura", //mensaje de la respuesta
                "detalle" => $data //datos de la respuesta que en este caso es el objeto data
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

            $detalleFact = DetalleFactura::where('id', $id)->first(); // Busca la categoría por ID y la guarda en la variable category
            if (!$detalleFact) { //verifica si la variable category es falsa
                $response = [
                    "status" => 404,
                    "message" => "DetalleReserva no encontrada"
                ];
                return response()->json($response, $response['status']);
            }

            $delete = $this->detalleFacRepo->eliminarDetalleFacturaSP($id);
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "DetalleReserva eliminada" //mensaje de la respuesta
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

            $detalleFact = DetalleFactura::where('id', $id)->first(); // Busca la categoría por ID y la guarda en la variable category
            if (!$detalleFact) { //verifica si la variable category es falsa
                $response = [
                    "status" => 404,
                    "message" => "DetalleReserva no encontrada"
                ];
                return response()->json($response, $response['status']);
            }

            $delete = $this->detalleFacRepo->eliminarDetalleFacturaNSP($id);
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "DetalleReserva eliminada" //mensaje de la respuesta
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
        $data_input = $request->input('data', null); // Obtenemos los datos del request en formato JSON y los guardamos en la variable data_input; si no hay datos, se guarda un null

        if ($data_input) {
            $data = json_decode($data_input, true); // Decodificamos los datos JSON y los guardamos en la variable $data

            if (is_array($data)) {
                // Eliminamos los espacios en blanco de los datos recibidos
                $data = array_map('trim', $data);

                // Convertimos los valores necesarios a su tipo correcto
                $data['idFactura'] = intval($data['idFactura']);
                $data['idProducto'] = intval($data['idProducto']);

                // Definimos las reglas de validación para los datos
                $rules = [
                    'idFactura' => 'required|integer|exists:factura,id', // Verifica que la factura exista en la base de datos
                    'idProducto' => 'required|integer|exists:producto,id' // Verifica que el producto exista en la base de datos
                ];

                // Validamos los datos con las reglas definidas
                $validator = Validator::make($data, $rules);

                // Verificamos si la validación tiene errores
                if (!$validator->fails()) {
                    // Intentamos encontrar el detalle de factura
                    $detalleFactura = DetalleFactura::find($id);

                    // Si no se encuentra, devolvemos una respuesta de error
                    if (!$detalleFactura) {
                        return response()->json([
                            "status" => 404,
                            "message" => "DetalleFactura no encontrado"
                        ], 404);
                    }

                    // Llamamos al repositorio para realizar la actualización del detalle de factura
                    $detalleFact = $this->detalleFacRepo->modificarDetalleFactura($id, $data);

                    // Verificamos si se guardó correctamente
                    if ($detalleFact) {
                        // Retorna una respuesta exitosa con los datos actualizados
                        return response()->json([
                            "status" => 200,
                            "message" => "DetalleFactura actualizado correctamente",
                            "detalleFactura" => $detalleFact
                        ], 200);
                    } else {
                        // Si no se guardó correctamente, devolvemos un error
                        return response()->json([
                            "status" => 500,
                            "message" => "Error al actualizar el detalle de factura"
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
}
