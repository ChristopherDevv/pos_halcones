@extends('layouts.dashboard')
@section('title','Becas')
@section('content')
    <div class="card col-lg-9 offset-lg-2">
        <div class="card-body">
            <h5 class="card-title">Registro y descripción de becas</h5>
            <form action="{{url('academias/guardar_beca')}}" method="POST">
                <fieldset>
                    @csrf
                    <div class="row">
                        <div class="col-12">
                            <label for="nombreBeca">Beca<span class="obligatorio">*</span> </label>
                            <input type="text" class="form-control" name="nombreBeca" id="nombreBeca" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label for="comentariosBeca">Descripción o comentarios sobre la beca</label>
                            <textarea class="form-control" name="comentariosBeca" id="comentariosBeca" rows="5"></textarea>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">Publicar</button>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
@endsection