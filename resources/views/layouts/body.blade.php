
{{-- @if (Auth::user()->theme_id == 0) --}}
    <body class="content" id="bodycontent" 
        @foreach ($themes as $theme)
            data-layout-mode="{{ $theme->layout_mode }}"
            data-topbar="{{ $theme->layout_mode }}"
            data-layout-size="{{ $theme->layout_width  }}"
            data-layout-scrollable="{{ $theme->layout_position }}"
            data-sidebar-size="{{ $theme->sidebar_size }}"
            data-sidebar="{{ ($theme->layout_mode == 'dark') ? $theme->layout_mode : $theme->sidebar_color }}"
        @endforeach
    >
{{-- @else
    <body class="content" data-layout-mode="dark" data-topbar="dark" data-sidebar="dark">
@endif --}}

{{-- data-layout-mode="dark" --}}
{{-- data-topbar="dark" --}}
{{-- data-sidebar="dark" --}}
{{-- data-layout-size="fluid" --}}
{{-- data-layout-scrollable="false" --}}