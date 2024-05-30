<?php

use App\Http\Controllers\academiasController;
use App\Http\Controllers\BecasController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AlmacenesController;
use App\Http\Controllers\FinanzasController;
use App\Http\Controllers\BoletosController;
use App\Http\Controllers\IndicadoresController;
use App\Http\Controllers\PartidosController;
use App\Http\Controllers\TicketsController;

Route::get('/', function(){
    return View('auth.login');
});



Auth::routes();
Route::get('inicio', [HomeController::class,'index'])->middleware('auth')->name('inicio');
Route::get('indicador', [HomeController::class,'indexIndicadores'])->middleware('auth')->name('indicador');
Route::get('corte', [HomeController::class,'corte'])->middleware('auth');
Route::post('corte_carga', [HomeController::class,'corte_carga'])->middleware('auth');
Route::post('indicador_carga', [HomeController::class,'indicador_carga'])->middleware('auth')->name('indicador.carga');
Route::post('indicador_carga/second', [HomeController::class,'indicador_carga'])->middleware('auth')->name('indicador.carga.second');
Route::post('corte_resultado', [HomeController::class,'corte_resultado'])->middleware('auth');

//rutas para administrar usuarios
Route::prefix('user')->group(function(){
    Route::get('/new', function(){
        return View('auth.register');
       });

    Route::post('/validate',[LoginController::class,'loginOnPlatform']);
    Route::get('/exit',[LoginController::class,'logout'])->name('user.exit');
});

//rutas para administrar la parte financiera, ingresos, egresos etc.
Route::prefix('finanzas')->group(function(){
//INGRESOS
    Route::get('/nuevo_ingreso',[FinanzasController::class,'nuevoIngresoForm']);
    Route::post('/guardar_ingreso',[FinanzasController::class,'guardarIngresoPlatform']);
    Route::post('/buscarIngreso',[FinanzasController::class,'buscarIngresoPlatform']);
    Route::post('/actualizar_ingreso',[FinanzasController::class,'actualizarIngresoPlatform']);
    Route::post('/borrarIngreso',[FinanzasController::class,'borrarIngresoPlatform']);
    Route::get('/tipos_ingreso',[FinanzasController::class,'tipoIngresosForm']);
    Route::post('/guardarTipoIngreso',[FinanzasController::class,'guardarTipoIngreso']);

//EGRESOS
    Route::get('/nuevo_egreso', [FinanzasController::class, 'nuevoEgresoForm']);
    Route::post('/guardar_egreso', [FinanzasController::class, 'guardarEgresoPlatform']);
    Route::post('/buscarEgreso', [FinanzasController::class, 'buscarEgresoPlatform']);
    Route::post('/actualizar_egreso', [FinanzasController::class, 'actualizarEgresoPlatform']);
    Route::post('/borrar_egreso', [FinanzasController::class, 'borrarEgresoPlatform']);
});

//rutas para administrar el almacén y los productos o conceptos que se guardan ahí.
Route::prefix('almacen')->group(function(){
    //Productos
    Route::get('/nuevo_producto',[ProductosController::class,'nuevoProductoPlatform']);
    Route::get('/lista_productos',[ProductosController::class,'productosPlatform']);
    //almacenes
    Route::get('/nuevo_almacen',[AlmacenesController::class,'nuevoAlmacenPlatform']);
    Route::get('/transferencias',[AlmacenesController::class,'transferenciasPlatform']);
    Route::get('/lista_almacenes',[AlmacenesController::class,'almacenesPlatform']);
});

//ACADEMIAS
Route::prefix('academias')->group(function(){
    //Alumnos
    Route::get('/alumnos',[AcademiasController::class,'alumnosIndex']);
    Route::post('/guardar_alumno',[AcademiasController::class,'guardarAlumno']);
    Route::post('/leerAlumno',[AcademiasController::class,'leerAlumno']);
    Route::post('/actualizar_alumno',[AcademiasController::class,'actualizarAlumno']);
    Route::post('/borrar_alumno',[AcademiasController::class,'borrarAlumno']);
    //Becas
    Route::get('/becas',[BecasController::class,'becasIndex']);
    Route::post('/guardar_beca',[BecasController::class,'guardarBeca']);
});

//Boletaje
Route::prefix('boletos')->group(function(){
    Route::get('/validar',[BoletosController::class,'validar']);
    Route::post('/buscar',[BoletosController::class,'buscar']);
});

//abonos
Route::prefix('abonos')->group(function(){
    Route::get('/conteo',[BoletosController::class,'conteo']);
});

/**
 *
 *Chrsistoper Patiño
*
*/

//log de ventas de boletos (exportable)
Route::get('/exportable-tickets',[IndicadoresController::class,'indexLogVenta'])->name('tickets.exportable');
Route::get('/tikets-vendidos',[IndicadoresController::class,'ticketsSold'])->name('tickets.sold');

//encontar codigo de asiento
Route::get('/codigos-asiento', [IndicadoresController::class, 'indexCodigoAsientos'])->name('find.seatcode.index');
Route::get('/search-codigos-asiento', [IndicadoresController::class, 'findSeatCode'])->name('find.seatcode.search');

//cancelacion de boletos
Route::get('/cancelacion-boletos', [TicketsController::class, 'indexCancelacionBoletos'])->name('cancelacion.boletos.index');
Route::get('/tickets-seatcodes', [TicketsController::class, 'ticketSeatCodes'])->name('ticket.seatcodes.web');
Route::delete('/delete-seat-ticket', [TicketsController::class, 'deleteSeatFromTicket'])->name('delete.seat.ticket.web');
Route::delete('/cancelar-ticket',[TicketsController::class,'cancelTicket'])->name('cancel.ticket.web');
Route::delete('/cancelar-ticket-no-vendido',[TicketsController::class,'cancelTicket'])->name('cancel.ticket.novendido.web');
Route::delete('/cancelar-todos-ticket',[TicketsController::class,'cancelAllTicket'])->name('cancel.all.ticket.web');

//boletos no vendidos
Route::get('/boletos-no-vendidos', [TicketsController::class, 'indexBoletosNoVendidos'])->name('boletos.no.vendidos.index');
Route::get('/boletos-no-vendidos-search', [TicketsController::class, 'findUnsoldTickets'])->name('boletos.no.vendidos.search');

//tipo de poago
Route::post('/tipo-pago',[TicketsController::class,'changeTypePayment'])->name('tipo.pago.web');

//Actulizacion de campos de ticket
Route::post('/actualizar-campos-ticket',[TicketsController::class,'updateFieldsTicket'])->name('actualizar.campos.ticket.web');

//actualizacion de campos en partidos
Route::get('/index-to-update-partidos', [PartidosController::class, 'indexToUpdate'])->name('partidos.index.update.web');
Route::get('/partido-to-update', [PartidosController::class, 'partidoToUpdate'])->name('partido.to.update.web');
Route::put('/update-partido', [PartidosController::class, 'updatePartido'])->name('update.partido.web');
