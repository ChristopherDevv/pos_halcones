@props(['message'])

<div id="toast-warning" class="alert alert-warning alert-dismissible fade show d-flex align-items-center w-100" role="alert">
    <div class="d-inline-flex align-items-center justify-content-center flex-shrink-0 text-warning bg-light rounded" style="width: 2rem; height: 2rem;">
        <svg class="w-75 h-75" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" style="fill: rgba(229, 217, 136, 1);transform: ;msFilter:;"><path d="M12.884 2.532c-.346-.654-1.422-.654-1.768 0l-9 17A.999.999 0 0 0 3 21h18a.998.998 0 0 0 .883-1.467L12.884 2.532zM13 18h-2v-2h2v2zm-2-4V9h2l.001 5H11z"></path></svg>
        <span class="sr-only">Warning icon</span>
    </div>
    <div class="ml-3"><strong>Warning!</strong> {{ $message }}</div>
    <button type="button" class="close ml-auto alert-text-dark" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<script>
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    toastr["warning"]("{{$message}}");
</script>