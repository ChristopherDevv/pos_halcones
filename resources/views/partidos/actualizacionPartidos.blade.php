@extends('layouts.dashboard')

@section('title','Actualización de partidos')

@section('content')

<div class="shakeX-animation">
    <div class="p-3 p-md-5 mt-2 mt-md-0 bg-white rounded cursor-shadow seccion-dark animate__animated animate__fadeInLeft">
        <form action="{{route('partido.to.update.web')}}" method="GET">
            @csrf
            <div class="row">
                <div class="col">
                    <label for="eventId" class="d-flex justify-content-start align-items-center" style="gap: 2px;">
                        <span class="d-inline-block" style="color: rgb(236, 62, 62)">* </span>
                        <span class="d-inline-block">Partidos</span>
                    </label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <label class="input-group-text label-rounded-form input-dark" for="eventId">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            </label>
                        </div>
                        <select class="section-hover form-control input-dark custom-select input-rounded-form" id="eventId" name="eventId" aria-label="Selecciona un partido" required>
                            <option value="" selected disabled>Seleccione un partido</option>
                            @if(!empty($partidos))
                                @foreach($partidos as $partido)
                                    <option value="{{$partido->id}}">{{ strtoupper($partido->id . ' - ' . $partido->titulo) }}</option>
                                @endforeach
                            @endif
                        </select>
                        @error('eventId')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div class="input-group-append">
                       <x-primary-button type="submit" text="Buscar"/>
                    </div>
                </div>
            </div>
        </form>
     </div>
</div>

 <div class="animate__animated animate__fadeInLeft">
    @isset($messageSuccess)
    <div class="mt-3">
       <x-alert-success-primary message="{{$messageSuccess}}"/>
   </div>
    @endisset
   
    @isset($messageSuccessSeconday)
    <div class="mt-3">
       <x-alert-success-secondary message="{{$messageSuccessSeconday}}"/>
   </div>
    @endisset
   
   @isset($errorMessage)
   <div class="mt-3">
       <x-alert-danger-primary message="{{$errorMessage}}"/>
   </div>
   @endisset
 </div>


@isset($partidoToUpdate)
   {{--  {{dd($partidoToUpdate)}} --}}
   <section class="mt-4 d-flex flex-column flex-md-row align-items-start justify-content-between animate__animated animate__fadeInLeft" style="gap: 20px;">
        <div class="bg-white w-100 rounded cursor-shadow seccion-dark p-update-partido">
            <form action="{{route('update.partido.web')}}" method="POST" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                @foreach ($partidoToUpdate as $actualPartido)
                    <input type="hidden" value="{{$actualPartido->id}}" name="idPartido">
                @endforeach
                <div class="w-100 d-block mb-3">
                    <label for="statusPartido">Status partido</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <label class="input-group-text label-rounded-form input-dark" for="statusPartido">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line><line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line><line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line><line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line><line x1="17" y1="16" x2="23" y2="16"></line></svg>
                            </label>
                        </div>
                        <select class="form-control custom-select input-dark input-rounded-form" name="statusPartido" id="statusPartido">
                            <option value="" selected disabled>Selecione un status</option>
                            @foreach ($statuses as $status)
                                <option value="{{$status}}">{{$status}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="w-100 d-block mb-4">
                    <label for="imagePartido">Imagen partido</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <label class="input-group-text label-rounded-form input-dark" for="imagePartido">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M20.4 14.5L16 10 4 20"/></svg>                            
                            </label>
                        </div>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input input-dark" name="imagePartido" id="imagePartido" aria-describedby="inputGroupFileAddon01">
                            <label class="custom-file-label input-rounded-form input-dark" for="imagePartido">Suba una imagen</label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="input-group-append d-inline-block" onclick="return confirm('¿Estas seguro de actualizar el partido?')">
                            <x-primary-button type="submit" text="Actualizar"/>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    
        <div class="bg-white w-100 rounded cursor-shadow seccion-dark p-update-partido">
            @foreach ($partidoToUpdate as $partidoActual)
                <h4 class="h4" style="font-weight: 600;">{{$partidoActual->titulo}}</h2>
                <div class="d-flex ilign-items-center justify-content-start mb-3" style="gap: 15px;">
                    <p>Fecha: <span class="font-weight-bolder">{{$partidoActual->fecha }}</span></p>
                    <p>Status: <span class="font-weight-bolder">{{$partidoActual->status}}</span> </p>
                </div>
                <div class="d-block w-100 text-center d-flex align-items-center justify-content-center">
                    @if(count($partidoActual->images) > 0)
                        <img class="w-image shadow-img mb-3" src="{{ asset($partidoActual->images[0]->uri_path) }}" alt="Imagen del partido">
                    @else
                        <div class="py-4 px-5 w-50 text-center" style="margin: 0 auto; border: dashed; background: #fff3f3; border-radius: 5px; color: rgb(255, 119, 119);">
                            <p class="m-0">No hay imagen disponible</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

@endisset

@endsection
