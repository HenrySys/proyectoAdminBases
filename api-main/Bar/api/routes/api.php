<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\DetalleFacturaController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\PedidoProductoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\InventarioController;


use App\Http\Middleware\ApiAuthMiddleware;


Route::prefix('tabbar')->group(
    function(){
        Route::post('/user/login',[UserController::class,'login']);
        Route::post('/user/store',[UserController::class,'store']);
        Route::post('/user/update',[UserController::class,'update']);//->middleware(ApiAuthMiddleware::class);
        Route::get('/user/index',[UserController::class,'index']);//->middleware(ApiAuthMiddleware::class);
        Route::get('/user/show/{id}',[UserController::class,'show']);//->middleware(ApiAuthMiddleware::class);
        Route::get('/user/getidentity',[UserController::class,'getIdentity']);
        Route::delete('/user/delete/{id}', [UserController::class, 'destroy']);//->middleware(ApiAuthMiddleware::class);


        Route::get('/categoria/index',[CategoriaController::class,'index']);
        Route::post('/categoria/store',[CategoriaController::class,'store']);
        Route::delete('/categoria/delete/{id}',[CategoriaController::class,'destroy']);

        Route::post('/producto/store',[ProductoController::class,'store']);
        Route::post('/producto/upload',[ProductoController::class,'uploadImage']);//->middleware(ApiAuthMiddleware::class);//ruta para el metodo upload del controlador vehiculo
        Route::get('/producto/getimage/{filename}',[ProductoController::class,'getImage']);
        Route::post('/producto/update/{id}',[ProductoController::class,'update']);

        Route::post('/cliente/store',[ClienteController::class,'store']);
        Route::get('/cliente/index',[ClienteController::class,'index']);

        Route::post('/empleado/store',[EmpleadoController::class,'store']);

        Route::post('/pedidoProducto/store',[PedidoProductoController::class,'store']);

        Route::delete('/pedidoProducto/deleteSP',[PedidoProductoController::class,'destroySP']);//->middleware(ApiAuthMiddleware::class);
        Route::delete('/pedidoProducto/deleteNSP',[PedidoProductoController::class,'destroyNSP']);//->middleware(ApiAuthMiddleware::class);

        Route::delete('/detalleFactura/deleteSP',[PedidoProductoController::class,'destroySP']);//->middleware(ApiAuthMiddleware::class);
        Route::delete('/detalleFactura/deleteNSP',[PedidoProductoController::class,'destroyNSP']);//->middleware(ApiAuthMiddleware::class);

        Route::post('/proveedor/store',[ProveedorController::class,'store']);
        Route::get('/proveedor/index',[ProveedorController::class,'index']);//->middleware(ApiAuthMiddleware::class);
        Route::delete('/proveedor/delete/{id}', [ProveedorController::class, 'destroy']);//->middleware(ApiAuthMiddleware::class);
        Route::get('/proveedor/show/{id}',[ProveedorController::class,'show']);//->middleware(ApiAuthMiddleware::class);

        Route::post('/mesa/store',[mesaController::class,'store']);

        Route::post('/inventario/aumentar', [InventarioController::class, 'aumentarInventario']);
        Route::post('/inventario/disminuir', [InventarioController::class, 'disminuirInventario']);
        Route::post('/inventario/actualizar', [InventarioController::class, 'actualizarInventario']);
        Route::get('/inventario/index', [InventarioController::class, 'index']);
        Route::get('/inventario/show/{id}', [InventarioController::class, 'show']);




        Route::resource('/user',UserController::class,['except'=>['create','edit','store']]);//->middleware(ApiAuthMiddleware::class);
        Route::resource('/producto',ProductoController::class,['except'=>['create','edit','store','update']]);//->middleware(ApiAuthMiddleware::class);
        Route::resource('/factura',FacturaController::class,['except'=>['create','edit',]]);//->middleware(ApiAuthMiddleware::class);
        Route::resource('/detallefactura',DetalleFacturaController::class,['except'=>['create','edit',]]);//->middleware(ApiAuthMiddleware::class);
        Route::resource('/mesa',mesaController::class,['except'=>['create','edit','store']]);//->middleware(ApiAuthMiddleware::class);
        Route::resource('/pedido',PedidoController::class,['except'=>['create','edit',]]);//->middleware(ApiAuthMiddleware::class);
        Route::resource('/pedidoProducto',PedidoProductoController::class,['except'=>['create','edit','store']]);//->middleware(ApiAuthMiddleware::class);
        Route::resource('/reserva',ReservaController::class,['except'=>['create','edit',]]);//->middleware(ApiAuthMiddleware::class);
        //Route::resource('/cliente',ClienteController::class,['except'=>['create','edit',]])->middleware(ApiAuthMiddleware::class);
        Route::resource('/empleado',EmpleadoController::class,['except'=>['create','edit',]]);//->middleware(ApiAuthMiddleware::class);

    }
);
