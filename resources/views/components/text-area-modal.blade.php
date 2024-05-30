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
                        <label for="exampleFormControlTextarea1">{{$nameTextArea}}</label>
                        <textarea class="form-control input-dark" placeholder="Ejemplo: con jersey" name="modelValue" id="exampleFormControlTextarea1" required rows="3" style="resize: none;"></textarea>
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