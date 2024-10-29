@yield('css')
<!-- Bootstrap Css -->
<link href="{{ URL::asset('assets/css/bootstrap.min.css')}}" id="bootstrap-style" rel="stylesheet" type="text/css" />
<!-- Icons Css -->
<link href="{{ URL::asset('assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />
<!-- App Css-->
<link href="{{ URL::asset('assets/css/app.min.css')}}" id="app-style" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('assets/css/dx.common.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/dx.light.css') }}">

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
    #capslock-indicator-on, #capslock-indicator-off {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        background-color: rgba(0, 0, 0, 0.7);
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        color: white;
        transition: opacity 0.5s ease-in-out;
        z-index: 100000;
    }
    #capslock-indicator-on.show, #capslock-indicator-off.show {
        display: block;
        opacity: 0.5;
    }
    #capslock-indicator-on i, #capslock-indicator-off i {
        font-size: 50px;
    }
</style>