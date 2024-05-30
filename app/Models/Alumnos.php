<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumnos extends Model
{
    use HasFactory;
    protected $table = 'alumnos';
    protected $primaryKey = 'idAlumno';
    public $timestamps = true;

    protected $fillable = [
        'idAlumno',
        'curp',
        'nombre',
        'papellido',
        'mapellido',
        'fechaNacimiento',
        'genero',
        'nombreTutor',
        'telefonoTutor',
        'calle',
        'nExt',
        'nInt',
        'colonia',
        'cp',
        'urlActaNacimiento',
        'certificadoMedico',
        'constanciaEst',
        'ineTutor',
        'created_at',
        'updated_at'
    ];
}
