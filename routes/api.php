<?php

use Illuminate\Http\Request;
use App\Events\PublicMessage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Http\Controllers\api\TicketsController;
use App\Http\Controllers\api\AuthController as auth;
use App\Http\Controllers\api\UsersController as user;
use App\Http\Controllers\api\ChangePasswordController;
use App\Http\Controllers\api\GrupoController as grupos;
use App\Http\Controllers\api\AforosController as aforos;
use App\Http\Controllers\api\ConfigController as config;
use App\Http\Controllers\api\OrdersController as orders;
use App\Http\Controllers\api\SorteoController as sorteo;
use App\Http\Controllers\api\EventosController as eventos;
use App\Http\Controllers\api\TicketsController as tickets;
use App\Http\Controllers\api\AsientosController as asientos;
use App\Http\Controllers\api\ImagenesController as imagenes;
use App\Http\Controllers\api\NoticiasController as noticias;
use App\Http\Controllers\api\PartidosController as partidos;
use App\Http\Controllers\api\PasswordResetRequestController;
use App\Http\Controllers\api\MembresiaController as membresia;
use App\Http\Controllers\api\ProductosController as productos;
use App\Http\Controllers\api\CategoriasController as categorias;
use App\Http\Controllers\api\DescuentosController as descuentos;

use App\Http\Controllers\api\PosicionesController as posiciones;
use App\Http\Controllers\api\ResultadosController as resultados;
use App\Http\Controllers\api\SucursalesController as sucursales;
use App\Http\Controllers\api\PaqueteriasController as paquterias;
use App\Http\Controllers\api\IndicadoresController as indicadores;
use App\Http\Controllers\api\UbicacionesController as ubicaciones;

use App\Http\Controllers\api\ReservationsController as reservaciones;
use App\Http\Controllers\api\PrecioAsientoController as precioAsiento;
use App\Http\Controllers\api\RegistroCajasController as registroCajas;
use App\Http\Controllers\api\TipoBeneficioController as tipoBeneficio;
use App\Http\Controllers\api\DistribucionesController as distribuciones;

use App\Http\Controllers\api\PedidoAtendidoEventCtrl as pedidoAtendidoEvent;
use App\Http\Controllers\api\PreciosMembresiaController as preciosMembresia;
use App\Http\Controllers\api\TemporadaPartidoController as temporadaPartido;
use App\Http\Controllers\api\CodigosDescuentoController as codigosDescuentos;
use App\Http\Controllers\api\CajasRegistradorasController as cajasRegistradoras;
use App\Http\Controllers\api\ProductoEnviadoEventController as productoEnviadoEvent;
use App\Http\Controllers\api\MetodosCobroYComisionController as metodosCobroYComision;
use App\Http\Controllers\api\EvidenciaSorteoPartidoController as evidenciaSorteoPartido;
use App\Http\Controllers\api\PointOfSale\BucketVendorProductController;
use App\Http\Controllers\api\PointOfSALE\ClothingCategoryController;
use App\Http\Controllers\api\PointOfSALE\ClothingSizeController;
use App\Http\Controllers\api\PointOfSale\GlobalComboController;
use App\Http\Controllers\api\PointOfSALE\GlobalInventoryController;
use App\Http\Controllers\api\PointOfSale\GlobalPaymentTypeController;
use App\Http\Controllers\api\PointOfSale\GlobalTypeCardPaymentController;
use App\Http\Controllers\api\PointOfSALE\InventoryTransactionTypeController;
use App\Http\Controllers\api\PointOfSale\PosCashRegisterController;
use App\Http\Controllers\api\PointOfSale\PosCashRegisterTypeController;
use App\Http\Controllers\api\PointOfSale\PosMovementTypeController;
use App\Http\Controllers\api\PointOfSale\PosProductCategoryController;
use App\Http\Controllers\api\PointOfSale\PosProductSubcategoryController;
use App\Http\Controllers\api\PointOfSale\PosProductWarehouseController;
use App\Http\Controllers\api\PointOfSale\PosTicketCancelationController;
use App\Http\Controllers\api\PointOfSale\PosTicketController;
use App\Http\Controllers\api\PointOfSale\PosTicketStatusController;
use App\Http\Controllers\api\PointOfSale\PosUnitMeasurementController;
use App\Http\Controllers\api\PointOfSale\ProductsForBucketvendorController;
use App\Http\Controllers\api\PointOfSale\StadiumLocationController;
use App\Http\Controllers\api\PointOfSale\WarehouseProductCatalogController;
use App\Http\Controllers\api\PointOfSale\WarehouseProductInventoryController;
use App\Http\Controllers\api\PointOfSale\WarehouseProductUpdateController;
use App\Http\Controllers\api\PointOfSALE\WarehouseSupplierController;
use App\Http\Controllers\api\pointofsale\WarehouseTransactionAcknowledgmentController;
use App\Http\Controllers\api\Wallet\WalletAccountController;
use App\Http\Controllers\api\Wallet\WalletAccountRoleController;
use App\Http\Controllers\api\Wallet\WalletCurrencyController;
use App\Http\Controllers\api\Wallet\WalletExchangeRateController;
use App\Http\Controllers\api\Wallet\WalletRechargeAmountController;
use App\Http\Controllers\api\Wallet\WalletTransactionController;
use App\Http\Controllers\api\Wallet\WalletTransactionStatusController;
use App\Http\Controllers\api\Wallet\WalletTransactionTypeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// rutas de autorización
Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [auth::class,'login']);
    Route::post('register', [auth::class,'register']);
    Route::post('reset', [auth::class,'postReset']);
    Route::post('logout', [auth::class,'logout']);
    Route::post('refresh', [auth::class,'refresh']);
    Route::post('me', [auth::class,'me']);
    Route::post('/reset-password-request', [PasswordResetRequestController::class, 'sendPasswordResetEmail']);
    Route::post('/change-password', [ChangePasswordController::class, 'passwordResetProcess'])->name('change.password');
    Route::get('/reset-password-form',[PasswordResetRequestController::class, 'showPasswordResetForm'])->name('form.password');

});

Route::group(['prefix'=>'grupos'],function(){
    Route::get('/constante-cortesia',[grupos::class,'constantCortesy']);
    Route::get('/constante-consigna',[grupos::class,'constantConsign']);
    Route::get('/constante-reservacion',[grupos::class,'constantReservation']);
});

Route::group(['prefix'=> 'config'], function () {
    Route::get('show',[config::class,'show']);
    Route::get('show/paypal',[config::class,'showIdPaypal']);
});

Route::group(['prefix'=>'user'],function(){

    Route::get('/{id}',[user::class,'showUers'])->middleware(['jwt.verify']);// show
    Route::put('/{id}',[user::class,'update'])->middleware(['jwt.verify']);


    // ZurielDA
        Route::get('/app/cliente',[user::class,'showUsersClients']);
        Route::get('/app/generos',[user::class,'showUsersGenres']);
        Route::post('/app/membresia',[user::class,'userMembership'])->middleware(['jwt.verify']);


        // Pendiente
        Route::post('/app/sorteo',[user::class,'storageRaffleUser'])->middleware(['jwt.verify']);
        Route::get('{id_user}/app/sorteo',[user::class,'showRaffleUser']);
    //
});

Route::group(['prefix'=>'noticias'],function(){
    Route::get('/',[noticias::class,'index']);// List
    Route::get('/{id}',[noticias::class,'show'])->middleware(['jwt.verify']); // show
    Route::post('/',[noticias::class,'store'])->middleware(['jwt.verify']);
    Route::put('/{id}',[noticias::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/{id}',[noticias::class,'destroy'])->middleware(['jwt.verify']);
});
Route::group(['prefix' => 'ubicaciones'], function() {
    Route::post('/',[ubicaciones::class,'store']);
    Route::get('/estados',[ubicaciones::class,'getEstados']);
    Route::get('/municipios/{estadoId}',[ubicaciones::class,'getMunicipios']);
    Route::get('/localidades/{municipioId}',[ubicaciones::class,'getLocalidades']);
    Route::get('/',[ubicaciones::class,'list']);
    Route::delete('/{id}',[ubicaciones::class,'destroy']);
    Route::put('/{id}',[ubicaciones::class,'update']);
});

Route::group(['prefix'=>'eventos'],function(){
    Route::get('/',[eventos::class,'index']);
    Route::get('/{id}',[eventos::class,'show'])->middleware(['jwt.verify']); // show
    Route::post('/',[eventos::class,'store'])->middleware(['jwt.verify']);
    Route::post('/participacion',[eventos::class,'addParticipation'])->middleware(['jwt.verify']);
    Route::put('/{id}',[eventos::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/{id}',[eventos::class,'destroy'])->middleware(['jwt.verify']);
});

Route::group(['prefix'=>'tickets'],function(){

    Route::get('/',[tickets::class,'index']);
    Route::get('/{id}',[tickets::class,'show'])->middleware(['jwt.verify']);
    Route::get('/user/{id}',[tickets::class,'findByIdUser'])->middleware(['jwt.verify']);
    Route::get('/qr/data',[tickets::class,'generateQr']);

    Route::post('/validate',[tickets::class,'validateTicket'])->middleware(['jwt.verify']);

    Route::post('/validate/byid',[tickets::class,'validateTicketById'])->middleware(['jwt.verify']);
    Route::get('/find/seat',[tickets::class,'findBySeat']);
    Route::post('/',[tickets::class,'store'])->middleware(['jwt.verify']);
    Route::put('/{id}',[tickets::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/{id}',[tickets::class,'destroy'])->middleware(['jwt.verify']);
    Route::delete('/seat/{id}',[tickets::class,'destroyForSeat'])->middleware(['jwt.verify']);
    Route::post('/payed',[tickets::class,'updatePayed'])->middleware(['jwt.verify']);
    Route::post('/aviable/verify',[tickets::class,'aviabledTicket'])->middleware(['jwt.verify']);
    Route::put('/seat/delete-seat-ticket',[tickets::class,'destroySeatOfTicket'])->middleware(['jwt.verify']);
    Route::get('/corteCaja/{idPartido}/{idUser}',[tickets::class,'boxCutGameUser']);
    Route::get('/corte-caja/general/{idUser}',[tickets::class,'boxCut']);

    Route::post('/abono',[tickets::class,'storeSubscription'])->middleware(['jwt.verify']);

    /**
    *
    *Chrsistoper Patiño
    *
    */

    Route::get('/tickets-seatcodes/{eventId}/{seatCode}', [TicketsController::class, 'ticketSeatCodes'])->name('ticket.seatcodes');
    Route::delete('delete-seat-ticket/{ticketId}/{seatId}', [TicketsController::class, 'deleteSeatFromTicket'])->name('delete.seat.ticket')->middleware(['jwt.verify']);
    Route::delete('/cancelar-ticket/{ticketId}',[TicketsController::class,'cancelTicket'])->name('cancel.ticket')->middleware(['jwt.verify']);
    Route::post('/transfer-seats-of-ticket',[TicketsController::class,'transferSeatOfTicket'])->middleware(['jwt.verify']);
});

Route::group(['prefix'=>'asientos'],function(){
    Route::get('/',[asientos::class,'index']);
    Route::get('/inf',[asientos::class,'info']);
    Route::get('/{id}',[asientos::class,'index']);
    Route::get('/service/find',[asientos::class,'findAsientoBy'])->middleware(['jwt.verify']);
    Route::get('/service/taquilla/find',[asientos::class,'taquillaFindAsientoBy'])->middleware(['jwt.verify']);
    Route::post('/',[asientos::class,'buildAsientos']);
    Route::get('/disable/{idAforo}',[asientos::class,'disableSeats']);
    Route::post('/',[asientos::class,'disable']);
    Route::get('/asientos/count',[asientos::class,'countSeats']);
    Route::get('/aviables/seats',[asientos::class,'getAviableSeat']);
    Route::get('/aviables/user/seats',[asientos::class,'getAviableSeatForUser']);
    Route::get('/all',[asentos::class,'getAviableSeatForUser']);
    Route::get('/prices/seats',[asientos::class,'getPricesSeat']);
    Route::put('/{id}',[asientos::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/{id}',[asientos::class,'destroy'])->middleware(['jwt.verify']);
});

Route::group(['prefix'=>'categorias'],function(){
    Route::get('/',[categorias::class,'index']);
    Route::get('/subcategorias',[categorias::class,'subCategorias']);
    Route::get('/{id}',[categorias::class,'show'])->middleware(['jwt.verify']);
    Route::post('/',[categorias::class,'store'])->middleware(['jwt.verify']);
    Route::put('/{id}',[categorias::class,'update']);
    Route::delete('/{id}',[categorias::class,'destroy']);
});

Route::group(['prefix'=>'partidos'],function(){
    Route::get('/',[partidos::class,'index']);
    Route::get('/get-current-partido',[partidos::class,'getCurrentPartido']);
    Route::get('/{id}',[partidos::class,'show'])->middleware(['jwt.verify']);
    Route::get('/mostrar/{id}',[partidos::class,'mostrar'])->middleware(['jwt.verify']);
    Route::get('/all/index',[partidos::class,'all'])->middleware(['jwt.verify']);
    Route::get('/all/date',[partidos::class,'getAllDateOfGames']);
    Route::post('/',[partidos::class,'store'])->middleware(['jwt.verify']);
    Route::put('/{id}',[partidos::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/{id}',[partidos::class,'destroy'])->middleware(['jwt.verify']);
});

Route::group(['prefix'=>'posiciones'],function(){
    Route::get('/',[posiciones::class,'index']);
    Route::get('/{id}',[posiciones::class,'show'])->middleware(['jwt.verify']);
    Route::post('/',[posiciones::class,'store'])->middleware(['jwt.verify']);
    Route::put('/{id}',[posiciones::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/{id}',[posiciones::class,'destroy'])->middleware(['jwt.verify']);
});

Route::group(['prefix'=>'resultados'],function(){
    Route::get('/',[resultados::class,'index']);
    Route::get('/{id}',[resultados::class,'show'])->middleware(['jwt.verify']);
    Route::post('/',[resultados::class,'store'])->middleware(['jwt.verify']);
    Route::put('/{id}',[resultados::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/{id}',[resultados::class,'destroy'])->middleware(['jwt.verify']);
});

Route::group(['prefix'=>'productos'],function(){
    Route::get('/',[productos::class,'index']);
    Route::get('/{id}',[productos::class,'show']);
    Route::get('/categorias/page',[productos::class,'page']);
    Route::get('/tallas/all',[productos::class,'tallas']);
    Route::post('/',[productos::class,'store'])->middleware(['jwt.verify']);
    Route::get('/retiros-ingresos/page',[productos::class,'getRetirosIngresos']);
    Route::post('/tallas/save',[productos::class,'guardarTalla']);
    Route::put('/{id}',[productos::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/{id}',[productos::class,'destroy'])->middleware(['jwt.verify']);


    // ZurielDA
        Route::delete('/tallas/{id}',[productos::class,'destroyTalla'])->middleware(['jwt.verify']);
        Route::put('/tallas/{id}',[productos::class,'updateTalla'])->middleware(['jwt.verify']);
    //

});

Route::group(['prefix' => 'orders'], function() {
    Route::post('/',[orders::class,'store'])->middleware(['jwt.verify']);
    Route::get('/',[orders::class,'index']);
    Route::get('/page',[orders::class,'page']);
    Route::get('/{id}',[orders::class,'show']);
    Route::get('/cortecaja/{idUser}',[orders::class,'corteDeCaja']);

    // ZurielDA
        Route::get('/cortecajausuario/{idUser}',[orders::class,'corteDeCajaUsuario']);
        Route::post('/membresia',[orders::class,'storeMembership'])->middleware(['jwt.verify']);
    //

    Route::get('/indicadores',[orders::class,'indicadores']);
    Route::delete('/{id}',[orders::class,'delete']);
    Route::put('/{id}',[orders::class,'edit']);
});


Route::group(['prefix'=>'images'],function(){
    Route::get('/',[imagenes::class,'index']);
    Route::get('/{id}',[imagenes::class,'show'])->middleware(['jwt.verify']);
    Route::post('/',[imagenes::class,'store'])->middleware(['jwt.verify']);
    Route::post('/upload',[imagenes::class,'uploadImage']);
    Route::post('/videos/upload',[imagenes::class,'uploadVideo'])->middleware(['jwt.verify']);
    Route::put('/{id}',[imagenes::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/{id}',[imagenes::class,'destroy'])->middleware(['jwt.verify']);

    // ZurielDA
        Route::get('/{carpeta}/{nombre}',[imagenes::class,'getImageBlob']);
    //

});

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});
Route::group(['prefix'=>'paqueteria'],function(){
    Route::get('/',[paquterias::class,'index']);
    Route::post('/',[paquterias::class,'store']);
    Route::put('/{id}',[paquterias::class,'update']);
    Route::delete('/{id}',[paquterias::class,'delete']);
});

Route::group(['prefix' => 'notificaciones'],function (){
   Route::post('/producto-enviado',[productoEnviadoEvent::class,'send'])->middleware(['jwt.verify']);
   Route::post('/producto-atendido',[pedidoAtendidoEvent::class,'send'])->middleware(['jwt.verify']);
});

Route::group(['prefix' => 'sucursales'],function (){
    Route::get('/',[sucursales::class,'index']);
    Route::post('/',[sucursales::class,'store'])->middleware(['jwt.verify']);
    Route::put('/',[sucursales::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/',[sucursales::class,'destroy'])->middleware(['jwt.verify']);
});

Route::group(['prefix' => 'reservaciones'], function (){
   Route::get('/',[reservaciones::class,'index']);
    Route::get('/user/{id}',[reservaciones::class,'index']);
   Route::post('/',[reservaciones::class,'store'])->middleware(['jwt.verify']);
   Route::put('/{id}',[reservaciones::class,'update'])->middleware(['jwt.verify']);
   Route::delete('/{id}',[reservaciones::class,'destroy'])->middleware(['jwt.verify']);
});

Route::group(['prefix' => 'distribuciones'], function (){
    Route::get('/',[distribuciones::class,'index']);
    Route::post('/',[distribuciones::class,'store'])->middleware(['jwt.verify']);
    Route::put('/{id}',[distribuciones::class,'update'])->middleware(['jwt.verify']);
    Route::delete('/{id}',[distribuciones::class,'destroy'])->middleware(['jwt.verify']);
});


Route::group(['prefix' => 'aforos'], function () {
    Route::get('/',[aforos::class,'index']);
    Route::post('/',[aforos::class,'store']);
});

Route::get('/activate-task', function () {
    Artisan::call('schedule:run');
    dd('schedule works!');
});




/**
 *
 * ZurielDA
 *
 */

    Route::group(['prefix' => 'indicadores'], function()
    {
        Route::get('/ventas/{fecha_inicial}/{fecha_final}',[indicadores::class,'SalesForDate']);
        Route::get('/productos/vendidos/{fecha_inicial}/{fecha_final}',[indicadores::class,'ProductsSale']);
        Route::get('/partido-boletos/{id}',[indicadores::class,'matchWithTicketsAndSeatTickets']);

        Route::get('/partido/{id}/tickets',[indicadores::class,'ticketsForMatch']);
        Route::get('/partido/{id}/asistencia',[indicadores::class,'attendanceForMatch']);

        
        /**
         *
         *Chrsistoper Patiño
        *
        */
        Route::get('/tikets-vendidos',[indicadores::class,'ticketsSold'])->name('ticketsSold');
        Route::get('/codigos-asiento/{email}/{eventId}', [indicadores::class, 'findSeatCode'])->name('findSeatCode');
    });


    Route::group(['prefix'=>'/descuentos'],function()
    {
        Route::post('/crear',[descuentos::class,'create_all_people'])->middleware(['jwt.verify']);
        Route::get('/activos',[descuentos::class,'active_all_people'])->middleware(['jwt.verify']);
        Route::post('/codigos',[codigosDescuentos::class,'storage'])->middleware(['jwt.verify']);
        Route::get('/codigos/{code}/existe',[codigosDescuentos::class,'verifyCode']);
    });


    Route::group(['prefix'=>'/membresia'],function()
    {
        // Membresia
        Route::get('/',[membresia::class,'index']);
        Route::get('/numerocontrol/{numberControl}',[membresia::class,'membershipNumberControl']);
        Route::get('/{id}',[membresia::class,'show']);
        Route::post('/',[membresia::class,'storage'])->middleware(['jwt.verify']);
        Route::put('/{id}',[membresia::class,'update'])->middleware(['jwt.verify']);
        Route::delete('/{id}',[membresia::class,'destroy'])->middleware(['jwt.verify']);

    });

    Route::group(['prefix'=>'/membresias'],function()
    {
        // Precios
        Route::get('/precio',[preciosMembresia::class,'index']);
        Route::get('/precio/{id}',[preciosMembresia::class,'show']);
        Route::post('/precio',[preciosMembresia::class,'storage'])->middleware(['jwt.verify']);
        Route::put('/precio/{id}',[preciosMembresia::class,'update'])->middleware(['jwt.verify']);
        Route::delete('/precio/{id}',[preciosMembresia::class,'destroy'])->middleware(['jwt.verify']);
    });




    Route::get('sucursal/{idBranchOffice}/caja-registradora/{idCashRegister}/sales-summary',[cajasRegistradoras::class,'cashCut'])->middleware(['jwt.verify']);

    Route::group(['prefix'=>'/cajas-registradoras'],function()
    {
        Route::get('',[cajasRegistradoras::class,'index']);
        Route::get('/{id}',[cajasRegistradoras::class,'show'])->middleware(['jwt.verify']);
        Route::post('',[cajasRegistradoras::class,'store'])->middleware(['jwt.verify']);
        Route::put('/{id}',[cajasRegistradoras::class,'update'])->middleware(['jwt.verify']);
        Route::delete('/{id}',[cajasRegistradoras::class,'destroy'])->middleware(['jwt.verify']);
    });

    Route::group(['prefix'=>'/registros-cajas'],function()
    {
        Route::get('',[registroCajas::class,'index']);
        Route::get('{id}',[registroCajas::class,'show']);
        Route::post('',[registroCajas::class,'store'])->middleware(['jwt.verify']);
        Route::put('/{id}',[registroCajas::class,'update'])->middleware(['jwt.verify']);
        Route::delete('/{id}',[registroCajas::class,'destroy'])->middleware(['jwt.verify']);
    });

    Route::group(['prefix'=>'/metodo-cobro'],function()
    {
        Route::get('',[metodosCobroYComision::class,'index']);
        Route::get('/{id}',[metodosCobroYComision::class,'show']);
        Route::post('',[metodosCobroYComision::class,'store'])->middleware(['jwt.verify']);
        Route::put('/{id}',[metodosCobroYComision::class,'update'])->middleware(['jwt.verify']);
        Route::delete('/{id}',[metodosCobroYComision::class,'destroy'])->middleware(['jwt.verify']);
    });

    Route::group(['prefix'=>'/precio-asiento'],function()
    {
        Route::get('',[precioAsiento::class,'index']);
        Route::post('',[precioAsiento::class,'store'])->middleware(['jwt.verify']);
        Route::put('/{id}',[precioAsiento::class,'update'])->middleware(['jwt.verify']);
        Route::delete('/{id}',[precioAsiento::class,'destroy'])->middleware(['jwt.verify']);
    });

    Route::group(['prefix'=>'/v-1/asientos'],function()
    {
        Route::get('/temporada/{idSeason}',[asientos::class,'showAllSeat']);
        Route::put('',[asientos::class,'updateSeatPrice'])->middleware(['jwt.verify']);
        Route::put('/estatus/actualizar',[asientos::class,'updateStatusSeatSeason'])->middleware(['jwt.verify']);
        Route::post('',[asientos::class,'store'])->middleware(['jwt.verify']);
        Route::delete('/{id}',[asientos::class,'destroy'])->middleware(['jwt.verify']);

    });

    Route::put('/searchQr',[tickets::class,'changeSeatAcquisition'])->middleware(['jwt.verify']);

    Route::group(['prefix'=>'/sorteos'],function()
    {
        Route::get('',[sorteo::class,'index']);
        Route::get('/{id}',[sorteo::class,'show']);
        Route::get('{id}/participantes',[sorteo::class,'showUsersRaffle']);
        Route::get('/{id}/evidencias',[sorteo::class,'showEvidenceUsers']);
        Route::post('',[sorteo::class,'storeRaffle'])->middleware(['jwt.verify']);
        Route::post('/evidencias',[evidenciaSorteoPartido::class,'store'])->middleware(['jwt.verify']);
    });



    Route::group(['prefix'=>'/temporada'],function()
    {
        Route::get('',[temporadaPartido::class,'index']);
        Route::get('/{id}',[temporadaPartido::class,'show']);
        Route::post('',[temporadaPartido::class,'store'])->middleware(['jwt.verify']);
        Route::put('/{id}',[temporadaPartido::class,'update'])->middleware(['jwt.verify']);
        Route::delete('/{id}',[temporadaPartido::class,'destroy'])->middleware(['jwt.verify']);
    });


    /* 
    *
    * Routes Point of Sale by Christoper Patiño
    *
    */
    Route::group(['prefix' => '/v1/pos'], function () {
        /* 
        * Routes by satdium location
        */
        Route::get('/stadium-locations-show',[StadiumLocationController::class,'index'])->name('stadium.locations.index.api');
        Route::post('/stadium-locations-store',[StadiumLocationController::class,'store'])->name('stadium.locations.store.api');

        /* 
        * Routes by pos product warehouse
        */
        Route::get('/product-warehouses-show',[PosProductWarehouseController::class,'index'])->name('product.warehouses.index.api');
        Route::post('/product-warehouses-store',[PosProductWarehouseController::class,'store'])->name('product.warehouses.store.api');

        /* 
        * Route by pos cash register types
        */
        Route::get('/cash-register-types-show',[PosCashRegisterTypeController::class,'index'])->name('cash.register.types.index.api');
        Route::post('/cash-register-types-show-by-warehouse',[PosCashRegisterTypeController::class,'indexByWarehouse'])->name('cash.register.types.by.warehouse.api');
        Route::post('/cash-register-types-store',[PosCashRegisterTypeController::class,'store'])->name('cash.register.types.store.api');
        Route::post('/cash-register-types-associate-to-warehouse',[PosCashRegisterTypeController::class,'associateToWarehouse'])->name('cash.register.types.associate.to.warehouse.api');

        /* 
        * Routes by pos cash register
        */
        Route::post('/cash-register-open',[PosCashRegisterController::class,'openPosCashRegister'])->name('cash.register.open.api');
        Route::post('/cash-register-close',[PosCashRegisterController::class,'closePosCashRegister'])->name('cash.register.close.api');
        Route::post('/cash_register-are-open',[PosCashRegisterController::class,'posCashRegisterAreOpen'])->name('cash.register.are.open.api');
        Route::post('/cash-register-general-history',[PosCashRegisterController::class,'posCashRegisterGeneralHistory'])->name('cash.register.general.history.api');
        Route::post('/summary-pos-of-warehouse', [PosCashRegisterController::class,'summaryPosOfWarehouse'])->name('summary.pos.of.warehouse.api');
        Route::post('/summary-combos-sold',[PosCashRegisterController::class,'summaryCombosSold'])->name('summary.combos.sold.api');
        Route::post('/summary-products-sold-in-combos',[PosCashRegisterController::class,'summaryProductsSoldInCombos'])->name('summary.products.sold.in.combos.api');
        Route::post('/summary-sale-without-combo',[PosCashRegisterController::class,'summarySaleWithoutCombo'])->name('summary.sale.without.combo.api');

        /* 
        * Routes of Movement types by pos cash register
        */
        Route::get('/movement-types-show',[PosMovementTypeController::class,'index'])->name('movement.types.index.api');
        Route::post('/movement-types-store',[PosMovementTypeController::class,'store'])->name('movement.types.store.api');

        /* 
        * Routes of payment types by pos cash register
        */
        Route::get('/payment-types-show',[GlobalPaymentTypeController::class,'index'])->name('payment.types.index.api');
        Route::post('/payment-types-store',[GlobalPaymentTypeController::class,'store'])->name('payment.types.store.api');

        /* 
        * Routes of global type card payment
        */
        Route::get('/global-card-payment-types-show',[GlobalTypeCardPaymentController::class,'index'])->name('global.card.payment.types.index.api');
        Route::post('/global-card-payment-types-store',[GlobalTypeCardPaymentController::class,'store'])->name('global.card.payment.types.store.api');

        /* 
        * Routes of tickets statuses by pos cash register
        */
        Route::get('/ticket-statuses-show',[PosTicketStatusController::class,'index'])->name('ticket.statuses.index.api');
        Route::post('/ticket-statuses-store',[PosTicketStatusController::class,'store'])->name('ticket.statuses.store.api');

        /* 
        * Routes of pos products categories by pos cash register
        */
        Route::get('/product-categories-show',[PosProductCategoryController::class,'index'])->name('product.categories.index.api');
        Route::post('/product-categories-store',[PosProductCategoryController::class,'store'])->name('product.categories.store.api');
        Route::post('/show-product-categories-by-warehouse',[PosProductCategoryController::class,'showProductCategoriesByWarehouse'])->name('show.product.categories.by.warehouse.api');
        Route::post('/associate-product-category-to-warehouse',[PosProductCategoryController::class,'associateProductCategoryToWarehouse'])->name('associate.product.category.to.warehouse.api');
        Route::post('/show-subcategories-by-category',[PosProductCategoryController::class,'showSubcategoriesByCategory'])->name('show.subcategories.by.category.api');

        /*
        * Routes of pos products subcategories by pos cash register 
        */
        Route::get('/product-subcategories-show',[PosProductSubcategoryController::class,'index'])->name('product.subcategories.index.api');
        Route::post('/product-subcategories-store',[PosProductSubcategoryController::class,'store'])->name('product.subcategories.store.api');
        Route::post('/associate-product-subcategory-to-category',[PosProductSubcategoryController::class,'associateProductSubcategoriToCategory'])->name('associate.product.subcategory.to.category.api');
        
        /* 
        * Routes of pos unit measurements by warehouse product catalogs
        */
        Route::get('/unit-measurements-show',[PosUnitMeasurementController::class,'index'])->name('unit.measurements.index.api');
        Route::post('/unit-measurements-store',[PosUnitMeasurementController::class,'store'])->name('unit.measurements.store.api');

        /* 
        * Routes of clothing categories by pos product warehouses
        */
        Route::get('/clothing-categories-show',[ClothingCategoryController::class,'index'])->name('show.clothing.category.api');

        /* 
        * Routes of clothing sizes by pos product warehouses
        */
        Route::get('/clothing-sizes-show',[ClothingSizeController::class,'index'])->name('show.clothing.size.api');

        /* 
        * Routes of warehouse product catalogs by pos product warehouses
        */
        Route::post('/warehouse-product-catalogs-show',[WarehouseProductCatalogController::class,'index'])->name('warehouse.product.catalogs.index.api');
        Route::post('/warehouse-product-catalogs-store',[WarehouseProductCatalogController::class,'store'])->name('warehouse.product.catalogs.store.api');
       // Route::post('/warehouse-product-inventories-store',[WarehouseProductInventoryController::class,'store'])->name('warehouse.product.inventories.store.api');
        Route::put('/warehouse-product-catalogs-update',[WarehouseProductUpdateController::class,'update'])->name('warehouse.product.catalogs.update.api');
       // Route::delete('/warehouse-product-catalogs-delete',[WarehouseProductCatalogController::class,'destroy'])->name('warehouse.product.catalogs.delete.api');
        Route::post('/associate-product-catalog-to-subcategory',[WarehouseProductCatalogController::class,'associateProductCatalogToSubcategory'])->name('associate.product.to.subcategory.api');
        Route::post('/show-relationships-by-product-warehouse', [WarehouseProductInventoryController::class,'showRelationshipsByProductWarehouse'])->name('show.relationships.by.product.warehouse.api');

        /* 
        * Routes of bucket vendor products by pos product warehouses
        */
        Route::post('/bucket-vendor-products-store',[BucketVendorProductController::class,'store'])->name('bucket.vendor.products.store.api');
        Route::post('/assing-product-to-bucket-vendor',[BucketVendorProductController::class,'assignProductToBucketVendor'])->name('assing.product.to.bucket.vendor.api');
        
        /* 
        * Routes of invetory transaction types by global inventories
        */
        Route::get('/inventory-transaction-types-show',[InventoryTransactionTypeController::class,'index'])->name('inventory.transaction.types.index.api');
        Route::post('/inventory-transaction-types-store',[InventoryTransactionTypeController::class,'store'])->name('inventory.transaction.types.store.api');

        /* 
        * Routes of warehouse suppliers by global inventories
        */
        Route::get('/warehouse-suppliers-show',[WarehouseSupplierController::class,'index'])->name('warehouse.suppliers.index.api');
        Route::post('/warehouse-suppliers-store',[WarehouseSupplierController::class,'store'])->name('warehouse.suppliers.store.api');
        
        /* 
        * Routes of Warehouse transaction acknowledgements by global inventories
        */
        Route::post('/warehouse-transaction-acknowledgements-show',[WarehouseTransactionAcknowledgmentController::class,'index'])->name('warehouse.transaction.acknowledgements.index.api');
        Route::post('/warehouse-transaction-acknowledgements-store',[WarehouseTransactionAcknowledgmentController::class,'store'])->name('warehouse.transaction.acknowledgements.store.api');
        Route::post('/warehouse-transaction-acknowledgements-show-by-date',[WarehouseTransactionAcknowledgmentController::class,'showByDate'])->name('warehouse.transaction.acknowledgements.show.by.date.api');
        Route::post('/download-global-inventory-movement-acknowledgment',[WarehouseTransactionAcknowledgmentController::class,'downloadGlobalInventoryMovementAcknowledgment'])->name('download.global.inventory.movement.acknowledgment.api');
        Route::post('/finalize-acknowledgement',[WarehouseTransactionAcknowledgmentController::class,'finalizeAcknowledgement'])->name('finaliz.acknowledgement.api');

        /* 
        * Routes of Global inventories by stadium location
        */
        Route::post('/global-inventories-show',[GlobalInventoryController::class,'index'])->name('global.inventories.index.api');
        Route::post('/global-inventories-store-transactions',[GlobalInventoryController::class,'storeTransactions'])->name('global.inventories.store.transactions.api');
        Route::post('/summary-global-inventory', [PosCashRegisterController::class, 'summaryGlobalInventory'])->name('summary.global.inventory.api');
        Route::post('/summary-pos-inventory-warehouse',[PosCashRegisterController::class,'summaryPosInventoryOfWarehouse'])->name('summary.pos.inventory.of.warehouse.api');

        /* 
        * Routes of Global combos by product warehouse
        */
        Route::get('/global-combos-show',[GlobalComboController::class,'index'])->name('global.combos.index.api');
        //Route::post('/global-combos-store',[GlobalComboController::class,'store'])->name('global.combos.store.api');

        /* 
        * Routes of pos ticket by product warehouse
        */
        Route::post('/store-pos-sale-ticket', [PosTicketController::class, 'storePosSaleTicket'])->name('store.pos.sale.ticket.api');
        /*  
        * Routes of cancelation of pos ticket by product warehouse
        */
        Route::post('/pos-cancel-product-by-ticket', [PosCashRegisterController::class, 'posCancelProductbyBucketVendor'])->name('pos.cancel.product.by.ticket.api');
        Route::post('/pos-cancel-ticket', [PosTicketCancelationController::class, 'posCancelTicket'])->name('pos.cancel.ticket.api');

        /* 
        * Routes of bucket vendor by product warehouse
        */
        Route::get('/bucket-vendors-show',[ProductsForBucketvendorController::class,'index'])->name('bucket.vendors.index.api');
        Route::post('/bucket-vendors-store',[ProductsForBucketvendorController::class,'store'])->name('bucket.vendors.store.api');
        Route::post('/pass-products-to-bucketvendor',[WarehouseProductInventoryController::class,'passProductsToBucketvendor'])->name('pass.products.to.bucketvendor.api');
        Route::post('/show-products-by-bucketvendor',[ProductsForBucketvendorController::class,'showProductsByBucketvendor'])->name('show.products.by.bucketvendor.api');
        Route::post('/return-products-to-warehouse', [WarehouseProductInventoryController::class, 'returnProductsToWarehouse'])->name('return.products.to.warehouse.api');
        Route::post('/show-sales-by-bucketvendor', [ProductsForBucketvendorController::class,'showSalesByBucketvendor'])->name('show.sales.by.bucketvendor.api');
    });

    /* 
    *
    * Routes of wallet by Christoper Patiño
    *
    */
    Route::group(['prefix' => '/v1/wallet'], function () {
        /* 
        * Routes of wallet currencies by wallet
        */
        Route::get('/wallet-currencies-show',[WalletCurrencyController::class,'index'])->name('wallet.currencies.index.api');
        Route::post('/wallet-currencies-store',[WalletCurrencyController::class,'store'])->name('wallet.currencies.store.api');

        /* 
        * Routes of wallet exchange rates by wallet
        */
        Route::get('/wallet-exchange-rates-show',[WalletExchangeRateController::class,'index'])->name('wallet.exchange.rates.index.api');
        Route::post('/wallet-exchange-rates-store',[WalletExchangeRateController::class,'store'])->name('wallet.exchange.rates.store.api');

        /* 
        * Routes of wallet account roles by wallet
        */
        Route::get('/wallet-account-roles-show',[WalletAccountRoleController::class,'index'])->name('wallet.account.roles.index.api');
        Route::post('/wallet-account-roles-store',[WalletAccountRoleController::class,'store'])->name('wallet.account.roles.store.api');

        /* 
        * Routes of wallet accounts by wallet
        */
       /*  Route::get('/wallet-accounts-show',[WalletAccountController::class,'index'])->name('wallet.accounts.index.api'); */
        Route::post('/show-history-wallet-account',[WalletAccountController::class,'showHistoryWalletAccount'])->name('show.history.wallet.account.api');
        Route::post('/wallet-accounts-store',[WalletAccountController::class,'storeWalletAccount'])->name('wallet.accounts.store.api');
        Route::post('/generic-wallet-accounts-store',[WalletAccountController::class,'storeWalletAccountWithoutUser'])->name('generic.wallet.accounts.store.api');
        Route::post('/associate-wallet-account-to-user',[WalletAccountController::class,'associateWalletAccountToUser'])->name('associate.wallet.account.to.user.api');
        Route::get('/index-wallet-account-without-user', [WalletAccountController::class, 'indexWalletAccountWithoutUser'])->name('index.wallet.account.without.user.api');
       
        /* 
        * Routes of wallet transaction statuses by wallet
        */
        Route::get('/wallet-transaction-statuses-show',[WalletTransactionStatusController::class,'index'])->name('wallet.transaction.statuses.index.api');
        Route::post('/wallet-transaction-statuses-store',[WalletTransactionStatusController::class,'store'])->name('wallet.transaction.statuses.store.api');

        /* 
        * Routes of wallet transaction types by wallet
        */
        Route::get('/wallet-transaction-types-show',[WalletTransactionTypeController::class,'index'])->name('wallet.transaction.types.index.api');
        Route::post('/wallet-transaction-types-store',[WalletTransactionTypeController::class,'store'])->name('wallet.transaction.types.store.api');

        /* 
        * Routes of wallet recharge amounts by wallet
        */
        Route::get('/wallet-recharge-amounts-show',[WalletRechargeAmountController::class,'index'])->name('wallet.recharge.amounts.index.api');
        Route::post('/wallet-recharge-amounts-store',[WalletRechargeAmountController::class,'store'])->name('wallet.recharge.amounts.store.api');
        Route::put('/wallet-recharge-amounts-change-status',[WalletRechargeAmountController::class,'changeStatus'])->name('wallet.recharge.amounts.change.status.api');

        /* 
        * Routes of Wallet Transactions by wallet
        */
        Route::post('/handle-Wallet-account-recharge', [WalletTransactionController::class, 'handleWalletAccountRecharge'])->name('handle.wallet.account.recharge');
    });




