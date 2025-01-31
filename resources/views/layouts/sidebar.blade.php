<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="{{ url('/') }}" class="logo logo-dark">
            <span class="logo-sm">
                {{-- <img src="{{ URL::asset('assets/images/logo-dark-sm.png') }}" alt="" height="22"> --}}
                <b style="font-size: 22px;">{{ env('APP_SHORTNAME') }}</b>
            </span>
            <span class="logo-lg">
                {{-- <img src="{{ URL::asset('assets/images/logo-dark.png') }}" alt="" height="22"> --}}
                <b style="font-size: 30px;">{{ env('APP_NAME') }}</b>
            </span>
        </a>

        <a href="{{ url('/') }}" class="logo logo-light">
            <span class="logo-lg">
                {{-- <img src="{{ URL::asset('assets/images/logo-light.png') }}" alt="" height="22"> --}}
                <b style="font-size: 30px; color:white !important;">{{ env('APP_NAME') }}</b>
            </span>
            <span class="logo-sm">
                {{-- <img src="{{ URL::asset('assets/images/logo-light-sm.png') }}" alt="" height="22"> --}}
                <b style="font-size: 22px; color:white !important;">{{ env('APP_SHORTNAME') }}</b>
            </span>
        </a>
    </div>

    <button type="button" class="btn btn-sm px-3 header-item vertical-menu-btn topnav-hamburger">
        <span class="hamburger-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </button>

    <div data-simplebar class="sidebar-menu-scroll">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <ul class="metismenu list-unstyled" id="side-menu">
                @foreach ($sequence as $sequenceitem)
                <li class="menu-title" data-key="t-applications">{{ $sequenceitem['title'] }}</li>
                @foreach ($sidemenu as $sidemenu_item)                
                        @if ($sidemenu_item['sequence_id'] == $sequenceitem['id'] && $sidemenu_item['is_active'] && $sidemenu_item['parent_id'] == null)
                            <li>
                                <a href="{{ !$sidemenu_item['is_parent'] ? $sidemenu_item['route'] : '#' }}" class="{{ $sidemenu_item['is_parent'] ? 'has-arrow' : '' }}">
                                    @foreach ($icons as $icon)
                                        @if($sidemenu_item['icon_id'] == $icon['id'])
                                            <i class="icon nav-icon" data-eva="{{ $icon['name'] }}"></i>
                                        @endif
                                    @endforeach
                                    <span class="menu-item" data-key="t-ecommerce">{{ $sidemenu_item['title'] }}</span>
                                </a>
                                @if ($sidemenu_item['is_parent'])
                                    <ul class="sub-menu" aria-expanded="false">
                                        @foreach ($sidemenu as $submenu_item)
                                            @if ($sidemenu_item['id'] == $submenu_item['parent_id'] && $submenu_item['is_active'])
                                                <li>
                                                    <a href="{{ $submenu_item['route'] }}" class="{{ $sidemenu_item['is_parent'] && $submenu_item['is_secondary_menu'] ? 'has-arrow' : '' }}">
                                                        @foreach ($icons as $icon)
                                                            @if ($submenu_item['icon_id'] == $icon['id'])
                                                                <i class="icon nav-icon" data-eva="{{ $icon['name'] }}"></i>
                                                            @endif
                                                        @endforeach
                                                        <span class="menu-item" data-key="t-dashboards">
                                                            @php
                                                                if ($submenu_item['must_full_title'] == 1) {
                                                                    echo $submenu_item['title'];
                                                                } else {
                                                                    $split = explode(" ", $submenu_item['title']);
                                                                    echo $split[count($split)-1];
                                                                }
                                                            @endphp
                                                        </span>
                                                    </a>
                                                    @if ($submenu_item['is_secondary_menu'] == 1)
                                                        <ul class="sub-menu" aria-expanded="false">
                                                            @foreach ($sidemenu as $secondary_item)
                                                                @if ($submenu_item['id'] == $secondary_item['parent_id'] && $secondary_item['is_active'])
                                                                    <li>
                                                                        <a href="{{ $secondary_item['route'] }}" data-key="t-level-2.1">
                                                                            @foreach ($icons as $icon)
                                                                                @if ($secondary_item['icon_id'] == $icon['id'])
                                                                                    <i class="icon nav-icon" data-eva="{{ $icon['name'] }}"></i>
                                                                                @endif
                                                                            @endforeach
                                                                            @php
                                                                                if ($secondary_item['must_full_title'] == 1) {
                                                                                    echo $secondary_item['title'];
                                                                                } else {
                                                                                    $split = explode(" ", $secondary_item['title']);
                                                                                    echo $split[count($split)-1];
                                                                                }
                                                                            @endphp
                                                                        </a>
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endif
                    @endforeach
                @endforeach
            </ul>
            <!-- Left Menu Start -->
            {{-- <ul class="metismenu list-unstyled" id="side-menu">
                @foreach ($sequence as $sequenceitem)
                    <li class="menu-title" data-key="t-applications">{{ $sequenceitem['title'] }}</li>
                    @foreach ($sidemenu as $sidemenu_item)
                        @if (($sidemenu_item['sequence_id'] == $sequenceitem['id'] && $sidemenu_item['is_active'] && $sidemenu_item['parent_id'] == null))
                            <li>
                                <a href="{{ !$sidemenu_item['is_parent'] ? $sidemenu_item['route'] : '#' }}" class="{{ $sidemenu_item['is_parent'] ? 'has-arrow' : '' }}">
                                    @foreach ($icons as $icon)
                                        @if($sidemenu_item['icon_id'] == $icon['id'])
                                            <i class="icon nav-icon" data-eva="{{ $icon['name'] }}"></i>
                                        @endif
                                    @endforeach
                                    <span class="menu-item" data-key="t-ecommerce">{{ $sidemenu_item['title'] }}</span>
                                </a>
                                @if ($sidemenu_item['is_parent'])
                                    <ul class="sub-menu" aria-expanded="false">
                                        @foreach ( $sidemenu as $submenu_item )
                                            @if(($sidemenu_item['id'] == $submenu_item['parent_id'] && $sidemenu_item['is_active']))
                                                <li>
                                                    <a href="{{ $submenu_item['route'] }}">
                                                        @foreach ($icons as $icon)
                                                            @if($sidemenu_item['icon_id'] == $icon['id'])
                                                                <i class="icon nav-icon" data-eva="{{ $icon['name'] }}"></i>
                                                            @endif
                                                        @endforeach
                                                        <span class="menu-item" data-key="t-dashboards">
                                                            @php
                                                                if($submenu_item['must_full_title'] == 1) {
                                                                    echo $submenu_item['title'];
                                                                } else {
                                                                    $split = explode(" ", $submenu_item['title']);
                                                                    echo $split[count($split)-1];
                                                                }
                                                            @endphp
                                                        </span>
                                                    </a>
                                                    @if ($submenu_item['is_secondary_menu'] == 1)
                                                        <ul>
                                                            <li>
                                                                <a href="{{ $submenu_item['route'] }}" data-key="t-level-2.1">{{ $submenu_item['title'] }}</a>
                                                            </li>
                                                        </ul>
                                                    @endif
                                                </li>  
                                            @endif
                                        @endforeach
                                    </ul>
                                @endif
                                
                            </li>
                        @endif
                    @endforeach
                @endforeach
            </ul> --}}
            {{-- <ul class="metismenu list-unstyled" id="side-menu">
                @foreach ($sequence as $sequence_item)
                    @if ($sequence_item['is_active'] == 1)
                        <li class="menu-title" data-key="t-menu">{{ $sequence_item['title'] }}</li>
                            @foreach ($sidemenu as $sidemenu_item)
                                @if ($sequence_item['id'] === $sidemenu_item['sequence_id'] && $sidemenu_item['is_active'] == 1 && $sidemenu_item['parent_id'] == null)
                                    <li>
                                        <a href="{{ $sidemenu_item['route'] }}"
                                            class="{{ $sidemenu_item['is_parent'] == 1? 'has-arrow' : '' }}">
                                            <i class="icon nav-icon" data-eva="shopping-bag-outline"></i>
                                            <span class="menu-item" data-key="t-ecommerce">{{ $sidemenu_item['title'] }}</span>
                                        </a>
                                        <ul class="sub-menu" aria-expanded="false">
                                            @foreach ($sidemenu as $submenu_item)
                                                @if($sidemenu_item['id'] === $submenu_item['parent_id'] && $sidemenu_item['is_active'] == 1)
                                                    <li>
                                                        <a href="{{ $submenu_item['route'] }}">
                                                            <i class="icon nav-icon" data-eva="grid-outline"></i>
                                                            <span class="menu-item" data-key="t-dashboards">
                                                                @if ($submenu_item['must_full_title'] == 0)
                                                                    @php
                                                                        $split = explode(" ", $submenu_item['title']);
                                                                        echo $split[count($split)-1]
                                                                    @endphp
                                                                @else
                                                                    {{ $submenu_item['title'] }}
                                                                @endif
                                                            </span>
                                                            <span class="badge rounded-pill bg-primary">3</span>
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </li>
                                @endif
                            @endforeach
                    @endif
                @endforeach
            </ul> --}}
        </div>
        <!-- Sidebar -->

        <div class="p-3 px-4 sidebar-footer">
            <p class="mb-1 main-title">
                <script>
                    document.write(new Date().getFullYear())
                </script> &copy; {{ env('APP_NAME') }}
            </p>
            <p class="mb-0">Design & Develop by {{ env('APP_AUTHOR') }}</p>
        </div>
    </div>
</div>
<!-- Left Sidebar End -->