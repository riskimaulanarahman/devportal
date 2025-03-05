@extends('layouts.master')
@section('title') @lang('GH & MESS Booking') @endsection
@section('content')
@section('pagetitle') Request Booking @endsection
<style>
    :root {
    font-family: Arial, Helvetica, sans-serif;
}

.tags-input {
    border: 1px solid #333;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    padding: 5px;
    border-radius: 5px;
    min-height: 40px;
    align-items: center;
    width: 100%;
    background-color: #fff;
}

.tags-input .tag {
    /* font-size: 85%; */
    padding: 0.5em 0.75em;
    display: flex;
    align-items: center;
    background-color: #ddd;
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.1s linear;
}

.tags-input .tag:hover {
    background-color: #3af;
    color: white;
}

.tags-input .tag .close {
    margin-left: 8px;
    font-size: 1rem;
    cursor: pointer;
    font-weight: bold;
}

.tags-input .tag .close:hover {
    color: red;
}

.tags-input .dx-textbox {
    border: none;
    outline: none;
    padding: 5px;
    flex-grow: 1;
    min-width: 100px;
    font-size: 1rem;
    background-color: transparent;
}

    .dx-scheduler-appointment {
        overflow: hidden;
    }

    .appointment-subject {
        font-weight: bold;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .appointment-description {
        font-size: 12px;
        color: #000000;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .dx-scheduler .dx-scheduler-date-table-cell {
        position: relative;
    }

    .dx-scheduler .appointment-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .dx-scheduler .appointment-text {
        font-size: 14px;
        text-align: center;
        padding: 5px;
        box-sizing: border-box;
        white-space: normal;
    }

    .dx-scheduler .dx-scheduler-appointment-tooltip {
        max-width: 200px;
    }

    .dx-scheduler .name {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .dx-scheduler .name h2 {
        margin: 0;
        padding: 0;
        font-size: 2vh;
        color: #ffffff;
    }

    .selectors {
        display: flex;
        margin-bottom: 20px;
    }

    .selectors > div {
        margin-right: 10px;
        width: 150px;
    }

    .header {
        margin-bottom: 20px;
        font-size: 20px;
        font-weight: bold;
    }
    .custom-appointment {
    border: 1px solid #d1d1d1;
    border-radius: 5px;
    padding: 10px;
    background-color: #f9f9f9;
    margin: 5px 0;
}

.custom-appointment .subject {
    font-weight: bold;
    font-size: 14px;
    margin-bottom: 5px;
}

.custom-appointment .description {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
}

.custom-appointment .room-name {
    font-size: 52px;
    color: #333;
    margin-bottom: 5px;
}

.custom-appointment .location {
    font-size: 12px;
    color: #888;
}
.custom-textarea {
    border: 1px solid #ccc;
    padding: 8px;
    width: 100%;
    min-height: 50px;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    align-items: center;
    cursor: text;
}

.custom-textarea input {
    border: none;
    outline: none;
    flex-grow: 1;
    min-width: 100px;
}

.chip {
    background-color: #007bff;
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.remove-chip {
    cursor: pointer;
    font-weight: bold;
}
/* Parent container for the combined column */
.combined-column {
    display: flex;
    flex-direction: column;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f9f9f9;
}

/* Styles for the name element */
.name {
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 5px;
}

/* .name h3{
    margin: 0;
    padding: 0;
    font-size: 1vh;
    color: #ffffff;
} */

/* Styles for the roomAccupancy element */
.roomAccupancy {
    text-align: center;
    font-size: 1.5vh;
    margin: 0;
    padding: 0;
    color: #555;
}

</style>
{{-- @php
    dd($booking);
@endphp --}}
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Scheduler Room Booking</h4>
            </div>
            <div class="card-body">
                <script>
                    const booking = @json($booking);                
                    const roomsWithLocations = @json($roomsWithLocations);
                    const uniqueLocations = @json($uniqueLocations);
                    const emplo = @json($emplo);
                    const departments = @json($departments);
                </script>
                <div class="selectors">
                    <div id="location-selector"></div>
                    <div id="room-selector"></div>
                </div>
                <div class="scheduler"></div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script src="{{ asset('assets/js/submission/ghm_booking.js') }}"></script>
<!-- apexcharts -->
{{-- <script src="{{ URL::asset('/assets/libs/apexcharts/apexcharts.min.js') }}"></script> --}}

<!-- dashboard init -->
{{-- <script src="{{ URL::asset('/assets/js/pages/dashboard.init.js') }}"></script> --}}
{{-- <script src="{{ URL::asset('/assets/js/app.min.js') }}"></script> --}}
@endsection
