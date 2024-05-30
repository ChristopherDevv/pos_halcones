@extends('layouts.dashboard')

@section('title','Nuevo almacén')

@section('content')

<div class="card col-6 offset-md-3">
    <div class="card-body">
        <h5 class="card-title">Registro del nuevo almacén</h5>
        <form action="">
                <div class="col-12 ">
                    <input type="text" class="form-control" placeholder="¿Cómo se va a llamar el almacén?" aria-label="nombreAlmacen" required>
                </div>
                <br>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
        </form>
</div>

@endsection
