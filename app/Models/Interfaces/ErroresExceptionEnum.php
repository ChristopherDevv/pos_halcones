<?php


namespace App\Models\Interfaces;


class ErroresExceptionEnum
{
    private $message;
    private $code;

    public function __construct($message, $code){
        $this->setCode($code);
        $this->setMessage($message);
    }

    public static  function ERROR_PROCESS_INSERT(){
        return  new ErroresExceptionEnum('Error al insertar el objeto','ERROR_PROCESS_INSERT');
    }
    public static  function SUCCESS_PROCESS_INSERT(){
        return  new ErroresExceptionEnum('Se ha guardado el objeto','SUCCESS_PROCESS_INSERT');
    }
    public static  function ERROR_PROCESS_UPDATE(){
        return  new ErroresExceptionEnum('Error al actualizar el objeto','ERROR_PROCESS_UPDATE');
    }
    public static  function SUCCESS_PROCESS_UPDATE(){
        return  new ErroresExceptionEnum('Se ha actualizado el objeto','SUCCESS_PROCESS_UPDATE');
    }
    public static  function ERROR_PROCESS_SHOW(){
        return  new ErroresExceptionEnum('Error al obtener el objeto','ERROR_PROCESS_SHOW');
    }
    public static  function SUCCESS_PROCESS_SHOW(){
        return  new ErroresExceptionEnum('Se ha encontrado el objeto','SUCCESS_PROCESS_SHOW');
    }
    public static  function ERROR_PROCESS_LIST(){
        return  new ErroresExceptionEnum('Error al consultar la lista','ERROR_PROCESS_LIST');
    }
    public static  function SUCCESS_PROCESS_LIST(){
        return  new ErroresExceptionEnum('Se ha encontrado la lista','SUCCESS_PROCESS_LIST');
    }
    public static  function ERROR_PROCESS_PAGE(){
        return  new ErroresExceptionEnum('Error al consultar la pagina','ERROR_PROCESS_PAGE');
    }
    public static  function SUCCESS_PROCESS_PAGE(){
        return  new ErroresExceptionEnum('Resultados de la pagina','SUCCESS_PROCESS_PAGE');
    }

    public static  function ERROR_PROCESS_DELETE(){
        return  new ErroresExceptionEnum('Error al intentar eliminar el elmento','ERROR_PROCESS_DELETE');
    }

    public static  function SUCCESS_PROCESS_DELETE(){
        return  new ErroresExceptionEnum('Se ha eliminado el objeto correctamente','SUCCESS_PROCESS_DELETE');
    }

    public static  function OBJECT_NOT_FOUND(){
        return  new ErroresExceptionEnum('No se ha encontrado el objeto','OBJECT_NOT_FOUND');
    }

    public static  function OBJECT_FOUND(){
        return  new ErroresExceptionEnum('Se ha encontrado que el objeto ya ha sido registrado previamente','OBJECT_FOUND');
    }

    public static  function OBJECT_NECESARY(){
        return  new ErroresExceptionEnum('Se requiere de el siguiente objeto','OBJECT_NECESARY');
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }
}
