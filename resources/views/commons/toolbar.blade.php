<div style="display: flex; align-items: center; justify-content: end; width: auto;">
<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-toolbal topbar mb-4 static-top shadow">

    <!-- Topbar Navbar -->
    <div class="d-flex p-0 m-0 no-bullet nav-global" style="list-style: none; gap: 10px;">
        <!-- Sidebar Toggle (Topbar) -->
        <div id="buttonSidebar" class="align-items-center justify-content-center hamburger-pc hamburger-global">
            <div class="rounded-circle info-profile ring-global p-2 d-flex align-items-center justify-content-center" style="cursor: pointer; padding: 10px !important;">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="gainsboro" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.8 3.8l16.4 16.4M20.2 3.8L3.8 20.2M15 3h6v6M9 3H3v6M15 21h6v-6M9 21H3v-6"/></svg>
            </div>
        </div>
        <div class="align-items-center hamburger-mobile" style="gap: 10px;">
            <li class="nav-item dropdown no-arrow">
                <div class="d-flex align-items-center nav-link dropdown-toggle info-profile" id="userDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="shadow img-profile rounded-circle ml-2 ml-md-0" src="{{asset('assets/img/user-img.svg')}}" alt="User Image">
                    <div class="d-flex flex-column ml-2">
                        <span class="d-none d-lg-inline text-white small" style="font-weight: 600;">{{Auth::user()->nombre}}</span>
                        <span class="d-none d-lg-inline text-white" style="font-size: 10px;">Bienvenido</span>
                    </div>
                </div>
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                    aria-labelledby="userDropdown">
                    <button type="button" class="dropdown-item" data-toggle="modal" data-target="#infoModalSecond" >
                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                        Profile
                    </button>
                    <!-- <a class="dropdown-item" href="#">
                        <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                        Settings
                    </a> -->
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                        Activity Log
                    </a>
                    <div class="dropdown-divider"></div>
                    <button class="dropdown-item"  type="button"  data-toggle="modal" data-target="#logoutModalSecond">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Cerrar sesión
                    </button>
                </div>
            </li>
            <li class="d-flex align-items-center justify-content-center">
                <div id="dark-mode-toggle" class="dark-mode-toggle rounded-circle info-profile ring-global p-2 d-flex align-items-center justify-content-center" style="cursor: pointer;">
                </div>
            </li>
        </div>
        <div class="align-items-center hamburger-pc" style="gap: 10px;">
            <li class="d-flex align-items-center justify-content-center">
                <div id="dark-mode-toggle" class="dark-mode-toggle rounded-circle info-profile ring-global p-2 d-flex align-items-center justify-content-center" style="cursor: pointer;">
                </div>
            </li>
            <li class="nav-item dropdown no-arrow">
                <div class="d-flex align-items-center nav-link dropdown-toggle info-profile" id="userDropdown" role="button"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="shadow img-profile rounded-circle ml-2 ml-md-0" src="{{asset('assets/img/user-img.svg')}}" alt="User Image">
                    <div class="d-flex flex-column ml-2">
                        <span class="d-none d-lg-inline text-white small" style="font-weight: 600;">{{Auth::user()->nombre}}</span>
                        <span class="d-none d-lg-inline text-white" style="font-size: 10px;">Bienvenido</span>
                    </div>
                </div>
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                    aria-labelledby="userDropdown">
                    <button type="button" class="dropdown-item" data-toggle="modal" data-target="#infoModalSecond" >
                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                        Profile
                    </button>
                    <!-- <a class="dropdown-item" href="#">
                        <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                        Settings
                    </a> -->
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                        Activity Log
                    </a>
                    <div class="dropdown-divider"></div>
                    <button class="dropdown-item"  type="button"  data-toggle="modal" data-target="#logoutModalSecond">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Cerrar sesión
                    </button>
                </div>
            </li>
        </div>
            <!-- Sidebar Toggle (Topbar) -->
        <div id="buttonSidebar" class="align-items-center justify-content-center hamburger-mobile hamburger-global">
            <div id="buttonSidebarMobile" class="rounded-circle info-profile ring-global p-2 d-flex align-items-center justify-content-center" style="cursor: pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" style="fill: white;transform: ;msFilter:;"><path d="M4 6h16v2H4zm4 5h12v2H8zm5 5h7v2h-7z"></path></svg>
            </div>
        </div>
        <!-- Nav Item - Search Dropdown (Visible Only XS) -->
        {{-- <li class="nav-item dropdown no-arrow d-sm-none">
            <a class="nav-link dropdown-toggle" href="#" id="searchDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-search fa-fw"></i> 
            </a>
            <div class="topbar-divider d-none d-sm-block"></div>
        </li> --}}
             {{-- Dropdown - Messages
            <div class="dropdown-menu dropdown-menu-right p-3 shadow animated--grow-in"
                aria-labelledby="searchDropdown">
                <form class="form-inline mr-auto w-100 navbar-search">
                    <div class="input-group">
                        <input type="text" class="form-control bg-light border-0 small"
                            placeholder="Search for..." aria-label="Search"
                            aria-describedby="basic-addon2">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </li> --}}
    
    <!--
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
    
                <span class="badge badge-danger badge-counter">3+</span>
            </a>
    
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                    Alerts Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3">
                        <div class="icon-circle bg-primary">
                            <i class="fas fa-file-alt text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 12, 2019</div>
                        <span class="font-weight-bold">A new monthly report is ready to download!</span>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3">
                        <div class="icon-circle bg-success">
                            <i class="fas fa-donate text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 7, 2019</div>
                        $290.29 has been deposited into your account!
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3">
                        <div class="icon-circle bg-warning">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">December 2, 2019</div>
                        Spending Alert: We've noticed unusually high spending for your account.
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
            </div>
        </li>
    
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <span class="badge badge-danger badge-counter">7</span>
            </a>
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                    Message Center
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="img/undraw_profile_1.svg"
                            alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div class="font-weight-bold">
                        <div class="text-truncate">Hi there! I am wondering if you can help me with a
                            problem I've been having.</div>
                        <div class="small text-gray-500">Emily Fowler · 58m</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="img/undraw_profile_2.svg"
                            alt="...">
                        <div class="status-indicator"></div>
                    </div>
                    <div>
                        <div class="text-truncate">I have the photos that you ordered last month, how
                            would you like them sent to you?</div>
                        <div class="small text-gray-500">Jae Chun · 1d</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="img/undraw_profile_3.svg"
                            alt="...">
                        <div class="status-indicator bg-warning"></div>
                    </div>
                    <div>
                        <div class="text-truncate">Last month's report looks great, I am very happy with
                            the progress so far, keep up the good work!</div>
                        <div class="small text-gray-500">Morgan Alvarez · 2d</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="dropdown-list-image mr-3">
                        <img class="rounded-circle" src="https://source.unsplash.com/Mv9hjnEUHR4/60x60"
                            alt="...">
                        <div class="status-indicator bg-success"></div>
                    </div>
                    <div>
                        <div class="text-truncate">Am I a good boy? The reason I ask is because someone
                            told me that people say this to all dogs, even if they aren't good...</div>
                        <div class="small text-gray-500">Chicken the Dog · 2w</div>
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
            </div>
        </li>
        -->
    </div>

    </nav>
    
    <script>
        function salir() {
            document.location = 'user/exit';
        }
    </script>
    
    
    <!-- End of Topbar -->
    
</div>