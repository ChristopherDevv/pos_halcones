{{-- Sidebar --}}

<div id="newSidebar" class="shadow-right navbar-menu navbar-nav-dark hidden-mobile">
    <a href="{{route('inicio')}}" class="d-flex align-items-center justify-content-center shadow logo-sidebar-dark">
        <div class="w-100 d-flex align-items-center justify-content-center" style="padding: 7px 0px;">
            <img src="{{asset('assets/img/platformname.png')}}" alt="logo de halcones" style="object-fit: contain; width: 70%; height: 70%;;">
        </div>
    </a>
    <div class="user-profile text-center">
        <div class="user-image">
            <img class="shadow" src="{{asset('assets/img/user-img.svg')}}" alt="User Image">
        </div>
        <div class="user-name">
            {{ Auth::user()->nombre }}
        </div>
        <div class="d-flex align-items-center justify-content-center mt-2" style="gap: 10px;">
            <button type="button" class="close-shadow-menu btn btn-outline-info btn-sm infoModal-open rounded-pill px-3" data-toggle="modal" data-target="#infoModal">Info</button>
            <button type="button" class="close-shadow-menu btn btn-outline-danger btn-sm logoutModal-open rounded-pill px-3" data-toggle="modal" data-target="#logoutModal">Log out</button>
        </div>
    </div>
    <ul class="navbar-nav navbar-nav-dark sidebar-dark accordion toggled " id="accordionSidebar">
        
        <li class="nav-item px-3 py-2  {{ request()->route()->getName() === 'inicio' || request()->route()->getName() === 'indicador.carga' || request()->route()->getName() === 'indicador' || request()->route()->getName() === 'indicador.carga.second' ? 'nav-active nav-item-dark nav-active-dark active font-weight-bold' : '' }}">
            <div class="d-flex align-items-center justify-content-between" data-toggle="collapse" data-target="#indicadores" aria-expanded="true" aria-controls="collapsePages" style="cursor: pointer;">
                <a class="nav-link collapsed text-gray-600 d-flex justify-content-start align-items-center" href="#" style="gap: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg>
                    <span>Indicadores</span>
                </a>
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" style="fill: #babcc6;transform: ;msFilter:;"><path d="M6.227 11h11.547c.862 0 1.32-1.02.747-1.665L12.748 2.84a.998.998 0 0 0-1.494 0L5.479 9.335C4.906 9.98 5.364 11 6.227 11zm5.026 10.159a.998.998 0 0 0 1.494 0l5.773-6.495c.574-.644.116-1.664-.747-1.664H6.227c-.862 0-1.32 1.02-.747 1.665l5.773 6.494z"></path></svg>
            </div>
            <div id="indicadores" class="collapse my-2 {{ request()->route()->getName() === 'inicio' || request()->route()->getName() === 'indicador.carga' || request()->route()->getName() === 'indicador' || request()->route()->getName() === 'indicador.carga.second' ? 'show' : '' }}" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded seccion-dark">
                    <div class="d-flex align-items-center justify-content-start options-collapse">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <p class="m-0 collapse-header p-0" style="color: #858796;">Opciones</p>
                    </div>
                    @can('View', auth()->user())
                        <a href="{{route('inicio')}}" class="options-collapse link-collapse d-block {{ request()->route()->getName() === 'inicio' || request()->route()->getName() === 'indicador.carga' ? 'active-text nav-inicador-dark' : 'text-gray-500' }} collapse-item" >
                            <span class="font-weight-normal">- Indicador detalles</span>
                        </a>
                    @endcan
                    <a href="{{route('indicador')}}" class=" options-collapse link-collapse d-block {{ request()->route()->getName() === 'indicador' || request()->route()->getName() === 'indicador.carga.second' ? 'active-text nav-inicador-dark' : 'text-gray-500' }} collapse-item" >
                        <span class="collapse-item-dark font-weight-normal">- Indicador resumen</span>
                    </a>
                </div>
            </div>
        </li>

        @can('View', auth()->user())

        <li class="nav-item  px-3 py-2 {{ request()->route()->getName() === 'delete.seat.ticket.web' || request()->route()->getName() === 'cancelacion.boletos.index' || request()->route()->getName() === 'ticket.seatcodes.web' || request()->route()->getName() == 'tipo.pago.web' || request()->route()->getName() == 'cancel.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.index' || request()->route()->getName() === 'cancel.all.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.search' || request()->route()->getName() === 'cancel.ticket.novendido.web' || request()->route()->getName() == 'actualizar.campos.ticket.web' ? 'nav-active nav-item-dark nav-active-dark  active font-weight-bold' : '' }}">
            <div class="d-flex align-items-center justify-content-between" data-toggle="collapse" data-target="#boletos" aria-expanded="true" aria-controls="collapsePages" style="cursor: pointer;">
                <a class="nav-link collapsed text-gray-600 d-flex justify-content-start align-items-center" href="#" style="gap: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                    <span>Gestion de boletos</span>
                </a>
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" style="fill: #babcc6;transform: ;msFilter:;"><path d="M6.227 11h11.547c.862 0 1.32-1.02.747-1.665L12.748 2.84a.998.998 0 0 0-1.494 0L5.479 9.335C4.906 9.98 5.364 11 6.227 11zm5.026 10.159a.998.998 0 0 0 1.494 0l5.773-6.495c.574-.644.116-1.664-.747-1.664H6.227c-.862 0-1.32 1.02-.747 1.665l5.773 6.494z"></path></svg>
                {{-- <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg> --}}
            </div>
            <div id="boletos" class="collapse my-2 {{ request()->route()->getName() === 'delete.seat.ticket.web' || request()->route()->getName() === 'cancelacion.boletos.index' || request()->route()->getName() === 'ticket.seatcodes.web' || request()->route()->getName() == 'tipo.pago.web' || request()->route()->getName() == 'cancel.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.index' || request()->route()->getName() === 'cancel.all.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.search' || request()->route()->getName() === 'cancel.ticket.novendido.web' || request()->route()->getName() == 'actualizar.campos.ticket.web' ? 'show' : '' }}" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded seccion-dark">
                    <div class="d-flex align-items-center justify-content-start options-collapse">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <p class="m-0 collapse-header p-0" style="color: #858796;">Opciones</p>
                    </div>
                    <a href="{{ route('cancelacion.boletos.index') }}" class="options-collapse link-collapse d-block {{ request()->route()->getName() === 'delete.seat.ticket.web' || request()->route()->getName() === 'cancelacion.boletos.index' || request()->route()->getName() === 'ticket.seatcodes.web' || request()->route()->getName() == 'tipo.pago.web' || request()->route()->getName() == 'cancel.ticket.web' || request()->route()->getName() == 'actualizar.campos.ticket.web' ? 'active-text nav-inicador-dark' : 'text-gray-500' }} collapse-item">
                        <span class="font-weight-normal">- Cancelacion y actua..</span>
                    </a>
                    <a href="{{ route('boletos.no.vendidos.index') }}" class="options-collapse link-collapse d-block {{ request()->route()->getName() === 'boletos.no.vendidos.index' || request()->route()->getName() === 'cancel.all.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.search' || request()->route()->getName() === 'cancel.ticket.novendido.web' ? 'active-text nav-inicador-dark' : 'text-gray-500' }} collapse-item">
                        <span class="font-weight-normal">- No vendidos</span>
                    </a>
                </div>
            </div>
        </li>
        <li class="nav-item  {{ request()->route()->getName() === 'tickets.exportable' || request()->route()->getName() === 'tickets.sold' ? 'nav-active nav-item-dark nav-active-dark  active' : '' }}">
            <a class="nav-link text-gray-600 px-3 py-3 d-flex justify-content-start align-items-center" href="{{route('tickets.exportable')}}" style="gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.08V8l-6-6H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h6"/><path d="M14 3v5h5M15.88 20.12l4.24-4.24M15.88 15.88l4.24 4.24"/></svg>
                <span>Log de venta</span>
            </a>
        </li>
        <li class="nav-item  {{ request()->route()->getName() === 'find.seatcode.index' || request()->route()->getName() === 'find.seatcode.search' ? 'nav-active nav-item-dark nav-active-dark  active' : '' }}">
            <a class="nav-link text-gray-600 px-3 py-3 d-flex justify-content-start align-items-center" href="{{ route('find.seatcode.index') }}" style="gap:6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
                <span>Códigos asiento</span>
            </a>
        </li>
        <li class="nav-item  {{ request()->route()->getName() === 'partidos.index.update.web' || request()->route()->getName() == 'partido.to.update.web' || request()->route()->getName() == 'update.partido.web' ? 'nav-active nav-item-dark nav-active-dark  active' : '' }}">
            <a class="nav-link text-gray-600 px-3 py-3 d-flex justify-content-start align-items-center" href="{{ route('partidos.index.update.web') }}" style="gap:6px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Partidos</span>
            </a>
        </li>

        @endcan

    </ul>
</div>
    
   