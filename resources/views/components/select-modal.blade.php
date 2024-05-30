<div class="modal fade" id="{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content modal-content-dark">
            <div class="modal-header modal-content-dark-secondary">
                <h5 class="modal-title font-weight-bolder" id="{{ $id }}Label">{{ $title }}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{ $body }}</p>
                <form id="accept-form" action="{{ $acceptRoute }}" method="POST">
                    @csrf
                    <input type="hidden" name="modelId" value="{{ $ticketId }}">
                    <input type="hidden" name="fieldName" value="{{ $fieldName }}">
                    <input type="hidden" name="eventSelected" value="{{ $eventSelected }}">
                    <div class="form-group">
                        <label for="typeReservation">{{$nameSelect}}</label>
                        <select class="input-dark form-control rounded-input" id="typeReservation" name="modelValue" aria-label="Selecciona un partido" required>
                            <option value="" selected disabled>Selecciona el type reservation</option>
                            <option value="taquilla">taquilla</option>
                            <option value="evento">evento</option>
                        </select>
                    </div>
                    <div class="modal-footer modal-content-dark-secondary">
                        <button class="btn btn-secondary rounded-pill px-3" type="button" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-3" type="button">
                            Aceptar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>