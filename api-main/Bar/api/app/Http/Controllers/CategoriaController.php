<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Repositories\CategoriaRepository;
use Illuminate\Support\Facades\Validator;


class CategoriaController extends Controller
{
    protected $categoriaRepo;

    public function __construct(CategoriaRepository $categoriaRepo)
    {
        $this->categoriaRepo = $categoriaRepo;
    }

    public function index(){
        $data=Categoria::all();
        return response()->json([
            'code'=>200,
            'status'=>'success',
            'data'=>$data
        ]);
    }

    public function store(Request $request)
    {
        $data_input = $request->input('data', null); // Obtenemos los datos en formato JSON

        if ($data_input) {
            $data = json_decode($data_input, true); // Decodificamos el JSON a un arreglo
            if (is_array($data)) {
                $data = array_map('trim', $data); // Eliminamos espacios en blanco

                $rules = [
                    'nombre' => 'required',
                ];
                $isValid = Validator::make($data, $rules);

                if (!$isValid->fails()) {
                    // Intentamos crear la categoría llamando al repositorio
                    $result = $this->categoriaRepo->crearCategoria($data);

                    if ($result) {
                        // Si la creación fue exitosa
                        $response = [
                            'status' => 201,
                            'message' => 'Categoría creada correctamente',
                            'data' => $data
                        ];
                    } else {
                        // Si hubo un error en la base de datos
                        $response = [
                            'status' => 500,
                            'message' => 'Error al crear la categoría en la base de datos'
                        ];
                    }
                } else {
                    // Si la validación falla
                    $response = [
                        'status' => 406,
                        'message' => 'Datos erróneos',
                        'errors' => $isValid->errors()
                    ];
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

    public function destroy($id){
        if(isset($id) && !empty($id) && is_numeric($id)){
            $category=Categoria::find($id);
            if($category){
                $this->categoriaRepo->eliminarCategoria($id);
                $data=[
                    'data'=>$category,
                    'status'=>200,
                    'message'=>'Categoria eliminada correctamente'
                ];
            }else{
                $data=[
                    'data'=>null,
                    'status'=>404,
                    'message'=>'Categoria no encontrada'
                ];
            }
        }else{
            $data=[
                'data'=>null,
                'status'=>406,
                'message'=>'Datos erroneos'
            ];
        }
        return response()->json($data);
    }
}
