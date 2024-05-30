{{-- Sidebar --}}

<div class="shadow-right navbar-menu navbar-nav-dark" id="shadowRight">
    <ul class="navbar-nav navbar-nav-dark bg-gradient-primary sidebar sidebar-dark accordion toggled bg-white " id="accordionSidebar">
        {{-- Sidebar Brand --}}
        <a href="{{route('inicio')}}" class="sidebar-brand d-flex align-items-center justify-content-center shadow logo-sidebar-dark">
            <div class="sidebar-brand-text mx-3"><img src="{{asset('assets/img/platformname.png')}}" alt="" alt="logo de halcones" width="100%" height="100%"></div>
        </a>
        
       {{-- Divider --}}
       <div class="user-profile text-center">
            <div class="user-image">
                <img class="shadow" src="{{asset('assets/img/user-img.svg')}}" alt="User Image">
            </div>
            <div class="user-name">
                {{ Auth::user()->nombre }}
            </div>
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-center mt-2" style="gap: 10px;">
                <button type="button" class="close-shadow-menu btn btn-outline-info btn-sm infoModal-open btn-text" data-toggle="modal" data-target="#infoModal">Info</button>
                <button type="button" class="close-shadow-menu btn btn-outline-danger btn-sm logoutModal-open btn-text" data-toggle="modal" data-target="#logoutModal">Log out</button>
            </div>
            </div>
          
            {{--  <hr class="sidebar-divider my-0"> --}}
        {{-- Nav Item Dashboard --}}
                <!-- <li class="nav-item  active">
                    <a class="nav-link" href="/inicio">
                        <i class="fas fa-fw fa-tachometer-alt"></i>
                        <span>Indicadores</span></a>
                </li> -->
        <!-- <li class="nav-item  ">
            <a class="nav-link" href="/corte">
                <i class="fas fa-fw fa-money-check-alt"></i>
                <span>Corte caja</span></a>
        </li> -->
    
        {{-- <li class="nav-item  ">
            <a class="nav-link" href="/abonos/conteo">
                <i class="fas fa-fw fa-money-check-alt"></i>
                <span>abonos</span></a>
        </li> --}}
    
    
        <!-- Funciones -->
        <!-- <li class="nav-item  ">
            <a class="nav-link" href="" data-toggle="collapse" data-target="#funciones" aria-expanded="true" aria-controls="collapsePages">
                <i class="fas fa-fw fa-money-check-alt"></i>
                <span>Funciones</span>
            </a>
            <div id="funciones" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Funciones</h6>
                    <a class="collapse-item" href="{{url('boletos/validar')}}">Validar boleto</a>
                    <div class="collapse-divider"></div>
                </div>
            </div>
    
        </li> -->
    
        <!-- Catalogos -->
        <!-- <li class="nav-item  ">
            <a class="nav-link" href="" data-toggle="collapse" data-target="#catalogos" aria-expanded="true" aria-controls="collapsePages">
                <i class="fas fa-fw fa-money-check-alt"></i>
                <span>Catalogos</span></a>
                <div id="catalogos" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded">
                    <h6 class="collapse-header">Catálogos</h6>
                    <a class="collapse-item" href="{{url('finanzas/convenios_club')}}">Convenios</a>
                    <div class="collapse-divider"></div>
                </div>
            </div>
        </li> -->
    
        {{-- indicadores tictkest vendidos --}}
       {{--  <li class="nav-item  {{ request()->route()->getName() === 'inicio' || request()->route()->getName() === 'indicador.carga' ? 'nav-active nav-active-dark  active' : '' }}">
            <a class="nav-link" href="{{route('inicio')}}" style="padding: 12px;">
                <i class="fas fa-fw fa-money-check-alt"></i>
                <span>Inico</span></a>
        </li> --}}
    
        {{-- {{ dd(auth()->user()->id_rol) }} --}}
    
        <li class="nav-item  {{ request()->route()->getName() === 'inicio' || request()->route()->getName() === 'indicador.carga' || request()->route()->getName() === 'indicador' || request()->route()->getName() === 'indicador.carga.second' ? 'nav-active nav-item-dark nav-active-dark  active' : '' }}">
            <a class="nav-link collapsed text-gray-600 d-flex justify-content-start align-items-center" href="#" data-toggle="collapse" data-target="#indicadores" aria-expanded="true" aria-controls="collapsePages" style="gap: 6px; padding-left:13px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg>
                <span>Indicadores</span>
            </a>
            <div id="indicadores" class="collapse {{ request()->route()->getName() === 'inicio' || request()->route()->getName() === 'indicador.carga' || request()->route()->getName() === 'indicador' || request()->route()->getName() === 'indicador.carga.second' ? 'show' : '' }}" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded seccion-dark">
                    <div class="d-flex align-items-center justify-content-start" style="gap: 6px; padding: 0.5rem 1rem; margin: 0 0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <h6 class="m-0 collapse-header p-0" style="color: #858796;">Opciones</h6>
                    </div>
                    @can('View', auth()->user())
                        <a href="{{route('inicio')}}" class="{{ request()->route()->getName() === 'inicio' || request()->route()->getName() === 'indicador.carga' ? 'active-text nav-inicador-dark' : 'text-gray-500' }} collapse-item" >
                            <span>- Indicador detalles</span>
                        </a>
                    @endcan
                    <a href="{{route('indicador')}}" class="{{ request()->route()->getName() === 'indicador' || request()->route()->getName() === 'indicador.carga.second' ? 'active-text nav-inicador-dark' : 'text-gray-500' }} collapse-item" >
                        <span class="collapse-item-dark">- Indicador resumen</span>
                    </a>
                </div>
            </div>
        </li>
            
        @can('View', auth()->user())

        <li class="nav-item  {{ request()->route()->getName() === 'delete.seat.ticket.web' || request()->route()->getName() === 'cancelacion.boletos.index' || request()->route()->getName() === 'ticket.seatcodes.web' || request()->route()->getName() == 'tipo.pago.web' || request()->route()->getName() == 'cancel.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.index' || request()->route()->getName() === 'cancel.all.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.search' || request()->route()->getName() === 'cancel.ticket.novendido.web' || request()->route()->getName() == 'actualizar.campos.ticket.web' ? 'nav-active nav-item-dark nav-active-dark  active' : '' }}">
            <a class="nav-link collapsed text-gray-600 d-flex justify-content-start align-items-center" href="#" data-toggle="collapse" data-target="#boletos" aria-expanded="true" aria-controls="collapsePages" style="gap: 6px; padding-left:13px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                <span>Gestion de boletos</span>
            </a>
            <div id="boletos" class="collapse {{ request()->route()->getName() === 'delete.seat.ticket.web' || request()->route()->getName() === 'cancelacion.boletos.index' || request()->route()->getName() === 'ticket.seatcodes.web' || request()->route()->getName() == 'tipo.pago.web' || request()->route()->getName() == 'cancel.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.index' || request()->route()->getName() === 'cancel.all.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.search' || request()->route()->getName() === 'cancel.ticket.novendido.web' || request()->route()->getName() == 'actualizar.campos.ticket.web' ? 'show' : '' }}" aria-labelledby="headingPages" data-parent="#accordionSidebar">
                <div class="bg-white py-2 collapse-inner rounded seccion-dark">
                    <div class="d-flex align-items-center justify-content-start" style="gap: 6px; padding: 0.5rem 1rem; margin: 0 0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <h6 class="m-0 collapse-header p-0" style="color: #858796;">Opciones</h6>
                    </div>
                    <a href="{{ route('cancelacion.boletos.index') }}" class="{{ request()->route()->getName() === 'delete.seat.ticket.web' || request()->route()->getName() === 'cancelacion.boletos.index' || request()->route()->getName() === 'ticket.seatcodes.web' || request()->route()->getName() == 'tipo.pago.web' || request()->route()->getName() == 'cancel.ticket.web' || request()->route()->getName() == 'actualizar.campos.ticket.web' ? 'active-text nav-inicador-dark' : 'text-gray-500' }} collapse-item">
                        <span class="collapse-item-dark">- Cancelacion y actua..</span>
                    </a>
                    <a href="{{ route('boletos.no.vendidos.index') }}" class="{{ request()->route()->getName() === 'boletos.no.vendidos.index' || request()->route()->getName() === 'cancel.all.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.search' || request()->route()->getName() === 'cancel.ticket.novendido.web' ? 'active-text nav-inicador-dark' : 'text-gray-500' }} collapse-item">
                        <span class="collapse-item-dark">- No vendidos</span>
                    </a>
                </div>
            </div>
        </li>


        <li class="nav-item  {{ request()->route()->getName() === 'tickets.exportable' || request()->route()->getName() === 'tickets.sold' ? 'nav-active nav-item-dark nav-active-dark  active' : '' }}">
            <a class="nav-link text-gray-600 d-flex justify-content-start align-items-center" href="{{route('tickets.exportable')}}" style="padding: 12px; gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.08V8l-6-6H6a2 2 0 0 0-2 2v16c0 1.1.9 2 2 2h6"/><path d="M14 3v5h5M15.88 20.12l4.24-4.24M15.88 15.88l4.24 4.24"/></svg>
                <span>Log de venta</span>
            </a>
        </li>

        <li class="nav-item  {{ request()->route()->getName() === 'find.seatcode.index' || request()->route()->getName() === 'find.seatcode.search' ? 'nav-active nav-item-dark nav-active-dark  active' : '' }}">
            <a class="nav-link text-gray-600 d-flex justify-content-start align-items-center" href="{{ route('find.seatcode.index') }}" style="padding: 12px; gap:6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path></svg>
                <span>Códigos asiento</span>
            </a>
        </li>
        <li class="nav-item  {{ request()->route()->getName() === 'partidos.index.update.web' || request()->route()->getName() == 'partido.to.update.web' ? 'nav-active nav-item-dark nav-active-dark  active' : '' }}">
            <a class="nav-link text-gray-600 d-flex justify-content-start align-items-center" href="{{ route('partidos.index.update.web') }}" style="padding: 12px; gap:6px;">
              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="#858796" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span>Partidos</span>
            </a>
        </li>



        
        {{-- <li class="nav-item   {{  request()->route()->getName() === 'delete.seat.ticket.web' || request()->route()->getName() === 'cancelacion.boletos.index' || request()->route()->getName() === 'ticket.seatcodes.web' || request()->route()->getName() == 'tipo.pago.web' || request()->route()->getName() == 'cancel.ticket.web' ? 'nav-active nav-item-dark nav-active-dark  active' : '' }}">
            <a href="{{ route('cancelacion.boletos.index') }}" class="text-gray-600 nav-link" style="padding: 12px;">
                <i class="fas fa-ticket-alt text-gray-600"></i>
                <span>Gestión de boletos</span>
            </a>
        </li>

        <li class="nav-item   {{ request()->route()->getName() === 'boletos.no.vendidos.index' || request()->route()->getName() === 'cancel.all.ticket.web' || request()->route()->getName() === 'boletos.no.vendidos.search' || request()->route()->getName() === 'cancel.ticket.novendido.web' ? 'nav-active nav-item-dark nav-active-dark  active' : '' }}">
            <a href="{{ route('boletos.no.vendidos.index') }}" class="text-gray-600 nav-link" style="padding: 12px;">
                <i class="fas fa-ticket-alt text-gray-600"></i>
                <span>Boletos no vendios</span>
            </a>
        </li> --}}

        @endcan
       
        
        </ul>
</div>
    
   