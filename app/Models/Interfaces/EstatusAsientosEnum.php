<?php
namespace App\Models\Interfaces;

class EstatusAsientosEnum {
	const DESHABILITADO = 0; //cacelados
	const DISPONIBLE = 1;// solo para la tabla de asientos y está habilitado para todo publico
	const RESERVADO  = 2; //son para reservaciones tabla de tickets
	const COMPRADO = 3; // solo para tickets significa comprado y para asientos signifia para reservacion
	const TEMPORADA = 4; //
	const TAQUILLA = 5; // Si se compró en taquilla tickets y tickets_asiento
	const VERIFICADO = 6;// verificaos para tickets y tickets_asiento
}
