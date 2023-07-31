@yield('css')
<!-- Bootstrap Css -->
<link href="{{ URL::asset('assets/css/bootstrap.min.css')}}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('assets/css/app.min.css')}}" id="app-style" rel="stylesheet" type="text/css" />

<link rel="stylesheet" href="{{ asset('assets/css/dx.common.css') }}">
@if(Auth::user())
    @if(Auth::user()->theme->layout_mode !== 'dark')
        <link rel="stylesheet" href="{{ asset('assets/css/dx.material.blue.light.compact.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('assets/css/dx.material.blue.dark.compact.css') }}">
        <style>
            /* pindahkan ke file css sendiri jika sudah selesai */
            :root {
                --bgdark:#22354e !important;
            }
            .dx-datagrid {
                background-color: var(--bgdark);
            }
            .dx-toolbar {
                background-color: var(--bgdark);
            }
            .dx-popup-content {
                background-color: #283f5c;
            }
            .dx-accordion-item-title {
                background-color: var(--bgdark);
            }
            .dx-accordion-item-body {
                background-color: #283f5c;
            }
            .dx-datagrid-header-panel {
                background-color: var(--bgdark)
            }
            .dx-datagrid-borders > .dx-datagrid-filter-panel, .dx-datagrid-borders > .dx-datagrid-headers {
                border-top: 1px solid #283f5c !important;
            }
            .dx-datagrid-borders > .dx-datagrid-headers, .dx-datagrid-borders > .dx-datagrid-rowsview, .dx-datagrid-borders > .dx-datagrid-total-footer {
                border-left: 1px solid var(--bgdark);
                border-right: 1px solid var(--bgdark);
            }
            .dx-datagrid-borders > .dx-datagrid-filter-panel {
                border-top-width: 0;
                border-left: 1px solid #283f5c !important;
                border-right: 1px solid #283f5c !important;
                border-bottom: 1px solid #283f5c !important;
            }
            .dx-datagrid .dx-row-lines > td {
                border-bottom: 1px solid #283f5c !important;
            }
            .dx-datagrid-filter-row {
                background-color: var(--bgdark);
            }
            .dx-datagrid-headers {
                border-bottom: 1px solid #283f5c !important;
            }
            .dx-datagrid .dx-row-alt > td {
                background-color: #283f5c !important;
            }
        </style>
    @endif
@endif
<style>
    .dx-checkbox-container {
        height: unset !important;
    }
    .dx-datagrid .dx-data-row > td.bullet {
        padding-top: 0;
        padding-bottom: 0;
    }
    .dx-datagrid {
        padding: 10px !important;
    }
    .dx-accordion-item-title {
        padding: 6px 11px !important;
    }
</style>