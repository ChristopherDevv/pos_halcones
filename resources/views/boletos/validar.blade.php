@extends('layouts.dashboard')

@section('title'.'boletaje')

@section('content')
<input type="hidden" name="token" id='_token' value="{{csrf_token() }}">
<div class="card col-lg-9 offset-lg-2">
    <div class="card-body">
        <form enctype="multipart/form-data">
            <fieldset>
                @csrf
                <div class=row>
                    <label for="SelectPartidos">Selecciona un Partido</label>
                    <select class="form-control" name="idPartido" id="idPartido" Disables>
                        @foreach($partidos as $p)
                            <option value="{{$p->id}}">{{$p->titulo}}</option>
                        @endforeach
                    </select>
       
                </div>
                <div class="row">
                    <label for="buscarBoleto">Buscar boleto</label>
                    <input type="text" class="form-control" name="ticket_data" id="ticket_data" required>
                </div>
                <br>
                <div class="row">
                    <div class="col-lg-12">
                        <button type="button" class="btn btn-primary" id='buscar' onclick="obtener()">Buscar</button>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
<br>
<div class="card col-lg-9 offset-lg-2"> 
    <div class="card-body">
        <fieldset>
            <div class="col-12">
                <table class="table dt" id="resultado_busqueda" width="100%">
                    <thead>
                        <tr>
                            <th scope="col">Ticket</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Asiento</th>
                            <th scope="col">Estado</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </fieldset>
    </div>
</div>

<script>

let token = document.getElementById('token');
let idPartido = document.getElementById('idPartido');
let ticketData = document.getElementById('ticket_data');

async function obtener(){
    let obj = {
        _token:_token.value,
        idPartido:idPartido.value,
        code:ticketData.value
    };
    // console.log(obj);


    const response = await fetch('/boletos/buscar',{
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':_token.value,
            'Content-Type':'application/json'
        },
        body:JSON.stringify(obj)
    }).then(response =>{
        if(!response.ok){
            throw new Error("HTTP error"+response.status);
        }
        return response.json();
    }).then(data=>{
        
        if(data.status == 204){
            alert("No se encontró el boleto");
        }else{
            console.log(data);
            alert("Boleto valido");
        }
    }).catch(error =>{
        console.error(error.message);
    });
        

}
</script>


@endsection