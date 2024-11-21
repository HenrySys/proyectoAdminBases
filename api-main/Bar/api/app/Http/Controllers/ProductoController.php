<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Repositories\ProductoRepository;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

use App\Models\Producto;

class ProductoController extends Controller
{

    protected $productoRepo;

    // Inyecta el repositorio a través del constructor
    public function __construct(ProductoRepository $productoRepo)
    {
        $this->productoRepo = $productoRepo;
    }

    public function index()
    {
        $data = Producto::all(); //obtenemos todos los registros de la tabla producto y los guardamos en la variable data
        $response = array(
            "status" => 200, //estado de la respuesta
            "message" => "Todos los registros de los Productos", //mensaje de la respuesta
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
                // Extraemos los datos de 'producto' y 'inventario'
                $productData = isset($data['producto']) ? array_map('trim', $data['producto']) : [];
                $inventoryData = isset($data['inventario']) ? array_map('trim', $data['inventario']) : [];

                $productData['precio'] = floatval($productData['precio']);
                $productData['idCategoria'] = intval($productData['idCategoria']);
                $productData['idProveedor'] = intval($productData['idProveedor']);
                $inventoryData['cantidad'] = intval($inventoryData['cantidad']);
                
                
    
                // Reglas de validación para el producto
                $productRules = [
                    'nombre' => 'required',
                    'descripcion' => 'required',
                    'precio' => 'required|numeric', // Validación numérica para el precio
                ];
    
                // Reglas de validación para el inventario (si es necesario)
                $inventoryRules = [
                    'cantidad' => 'required|integer|min:0', // Validación para la cantidad
                    'ubicacion' => 'required', // Validación para la cantidad

                ];
    
                // Validación de los datos del producto y del inventario
                $productValidation = Validator::make($productData, $productRules);
                $inventoryValidation = Validator::make($inventoryData, $inventoryRules);
    
                // Verificar si alguna de las validaciones falla
                if ($productValidation->fails() || $inventoryValidation->fails()) {
                    $response = [
                        'status' => 406,
                        'message' => 'Datos inválidos',
                        'errors' => array_merge(
                            $productValidation->errors()->toArray(),
                            $inventoryValidation->errors()->toArray()
                        )
                    ];
                } else {
                    // Realizamos el merge de los datos (producto + inventario)
                    $mergedData = array_merge($productData, $inventoryData);

    
                    // Llamamos al procedimiento almacenado para crear el producto e inventario en la base de datos
                    $result = $this->productoRepo->crearProducto($mergedData);
    
                    if ($result) {
                        // Si el procedimiento almacenado fue exitoso
                        $response = [
                            'status' => 201,
                            'message' => 'Producto y Inventario creados correctamente',
                            'data' => $result // Puede incluir detalles del producto creado, si el procedimiento lo devuelve
                        ];
                    } else {
                        // Error al crear el producto o inventario en la base de datos
                        $response = [
                            'status' => 500,
                            'message' => 'Error al crear el producto o inventario'
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
        $data = Producto::find($id); //buscamos un registro de la tabla cliente con el id recibido y lo guardamos en la variable data
        if (is_object($data)) { //verificamos si la variable data es un objeto
            $response = array(
                "status" => 200, //estado de la respuesta
                "message" => "Datos del Producto", //mensaje de la respuesta
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
        if (isset($id) ) { //isset = verifica si una variable esta definida, en este caso si el id esta definido

            $delete = $this->productoRepo->eliminarProducto($id); //buscamos un registro de la tabla producto con el id recibido y lo eliminamos y guardamos el resultado en la variable delete
            if ($delete) { //verificamos si la variable delete es verdadera
                $response = array(
                    "status" => 200, //estado de la respuesta
                    "message" => "Producto eliminada" //mensaje de la respuesta
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
        // Obtenemos los datos del request en formato JSON y los guardamos en la variable $data_input; si no hay datos, se guarda como null
        $data_input = $request->input('data', null);

        if ($data_input) {
            // Decodificamos los datos JSON a un array y eliminamos espacios en blanco
            $data = json_decode($data_input, true);
            $data = array_map('trim', $data);

            // Convertimos valores a los tipos esperados
            $data['precio'] = floatval($data['precio']);

            // Definimos las reglas de validación
            $rules = [
                'nombre' => 'required',
                'descripcion' => 'required',
                'precio' => 'required|numeric|min:0.01', // Validación numérica para el precio
                'idCategoria' => 'required|integer', // Verificamos que la categoría exista
                //'imgen' => 'required|integer'
            ];

            // Validamos los datos con las reglas
            $validator = Validator::make($data, $rules);

            if (!$validator->fails()) {
                // Buscamos el producto a actualizar
                $producto = Producto::find($id);

                if (!$producto) {
                    // Si no se encuentra el producto, retorna un mensaje de error
                    return response()->json([
                        "status" => 404,
                        "message" => "Producto no encontrado"
                    ], 404);
                }

                // Llamamos al procedimiento almacenado para modificar el producto
                $resultado = $this->productoRepo->ModificarProducto( $data);

                if ($resultado) {
                    // Retorna una respuesta exitosa
                    return response()->json([
                        "status" => 200,
                        "message" => "Producto actualizado correctamente",
                        "producto" => $resultado // Suponiendo que el procedimiento devuelve el producto actualizado
                    ], 200);
                } else {
                    // Manejo de error si la actualización falló
                    return response()->json([
                        "status" => 500,
                        "message" => "Error al actualizar el producto en la base de datos"
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


    public function uploadImage(Request $request)
    {
        $isValid = \Validator(
            $request->all(),
            ['file0' => 'required|image|mimes:jpg,jpeg,png,gif,svg']
        );
        if (!$isValid->fails()) {
            $image = $request->file('file0');
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            Storage::disk('productos')->put($filename, File::get($image));
            $response = array(
                "status" => 201,
                "message" => "Imagen guardada correctamente",
                "filename" => $filename,
            );
        } else {
            $response = array(
                "status" => 406,
                "message" => "Error: no se encontro la imagen",
                "errors" => $isValid->errors()
            );
        }
        return response()->json($response, $response['status']);
    }

    public function getImage($filename)
    {
        if (isset($filename)) {
            $exist = Storage::disk('productos')->exists($filename);
            if ($exist) {
                $file = Storage::disk('productos')->get($filename);
                return new Response($file, 200);
            } else {
                $response = array(
                    "status" => 404,
                    "message" => "No Existe la imagen"
                );
            }
        } else {
            $response = array(
                "status" => 406,
                "message" => "No se definio el nombre de la imagen"
            );
        }
        return response()->json($response);
    }

    public function updateImage(Request $request, string $filename)
    {
        $isValid = Validator::make(
            $request->all(),
            ['file0' => 'required|imgen|mimes:jpg,jpeg,png,gif,svg']
        );
        if (!$isValid->fails()) {
            $image = $request->file('file0');
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            Storage::disk('productos')->put($filename, File::get($image));
            $response = array(
                "status" => 201,
                "message" => "Imagen guardada correctamente",
                "filename" => $filename,
            );
        } else {
            $response = array(
                "status" => 406,
                "message" => "Error: no se encontro la imagen",
                "errors" => $isValid->errors()
            );
        }
        return response()->json($response, $response['status']);
    }

    public function destroyImage($filename)
    {
        if (isset($filename)) {
            $exist = Storage::disk('producto')->exists($filename);
            if ($exist) {
                Storage::disk('producto')->delete($filename);
                $response = array(
                    "status" => 201,
                    "message" => "Imagen eliminada correctamente"
                );
            } else {
                $response = array(
                    "status" => 404,
                    "message" => "No Existe la imagen"
                );
            }
        } else {
            $response = array(
                "status" => 406,
                "message" => "No se definio el nombre de la imagen"
            );
        }
        return response()->json($response);
    }
}
