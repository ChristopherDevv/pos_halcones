@props(['id', 'title', 'body', 'acceptRoute', 'user', 'modelId', 'modelValue', 'eventSelected'])

<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id }}Label" aria-hidden="true" data-event-selected="{{ $eventSelected }}">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-dark">
            <div class="modal-header modal-content-dark-secondary">
                <h5 class="modal-title font-weight-bolder" id="{{ $id }}Label">{{ $title }}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                @if ($user)
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <img class="shadow img-profile rounded-circle" src="{{asset('assets/img/user-img.svg')}}" alt="User Image" style="width: 20%;">
                    <p class="font-weight-bolder mb-2">{{ $body }}</p>
                </div>
                    <p>Nombre: {{ $user->nombre }}</p>
                    <p>Correo: {{ $user->correo }}</p>
                    <p>Apellido P.: {{ $user->apellidoP }}</p>
                    <p>Apellido M.: {{ $user->apellidoM }}</p>
                    <p>Rol: {{ $user->id_rol }}</p>
                @else
                    <p>{{ $body }}</p>
                @endif
            </div>
            <div class="modal-footer modal-content-dark-secondary">
                <button class="btn btn-secondary rounded-pill px-3" type="button" data-dismiss="modal">Cancelar</button>
                @if ($acceptRoute)
                <a class="btn btn-primary rounded-pill px-3" href="{{ $acceptRoute }}"
                   onclick="event.preventDefault();
                   document.getElementById('accept-form').submit();">
                    Aceptar
                </a>
                <form id="accept-form" action="{{ $acceptRoute }}" method="GET" style="display: none;">
                    @csrf
                </form>
                @else
                    @if ($title !== '¿Estas seguro de cambiar el tipo de pago?')
                         <button id="acceptButton" type="button" class="btn btn-primary rounded-pill px-3" data-dismiss="modal">Aceptar</button>                        
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>