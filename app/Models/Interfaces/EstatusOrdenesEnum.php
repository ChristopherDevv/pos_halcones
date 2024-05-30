<?php


namespace App\Models\Interfaces;


class EstatusOrdenesEnum
{
 const CANCELLED = 0;
 const CREATED = 1;
 const PAYED = 2;
 const REQUESTED = 3;
 const ATENDED = 4;
 const SENDED = 5;
 const COMPLETED = 6;


 public function statusProducts($code)
 {

    switch ($code) {

        case 0:

            return "Cancelado";

            break;

        case 1:

            return "Creado";

            break;

        case 2:

            return "Pagado";

            break;

        case 3:

            return "Solicitado";

            break;

        case 4:

            return "Atendido";;

            break;

        case 5:

            return "Enviado";

            break;

        case 6:

            return "Completado";

            break;
    }
 }

 public function statusMembership($code)
 {

    switch ($code) {

        case 0:

            return "Cancelado";

            break;

        case 1:

            return "Creado";

            break;

        case 2:

            return "Pagado";

            break;

        case 3:

            return "Solicitado";

            break;

        case 4:

            return "En Proceso";;

            break;

        case 5:

            return "Listo Para Entrega";

            break;

        case 6:

            return "Completado";

            break;
    }
 }

}



