@extends('layouts.dashboard')

@section('title'.'abonos')

@section('content')
<div class='card col-lg-9 offset-lg-2'>
    <div class="card-body">
        <fieldset>
            <div class='row'>
                <strong>Abonos regulares: &nbsp</strong>{{ $abonos[0]->conteo_count }}
            </div>
            <div class='row'>
                <strong>Abonos vip: &nbsp</strong>{{$abonosvip[0]->conteo_count}}
            </div>
            <div class='row'>
               <strong>Total abonos: &nbsp</strong> {{$abonos[0]->conteo_count + $abonosvip[0]->conteo_count}}
            </div>
        </fieldset>
    </div>
<div>
@endsection



