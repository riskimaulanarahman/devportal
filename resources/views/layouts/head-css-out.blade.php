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
</style>