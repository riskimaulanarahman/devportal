{{-- Mengambil segmen terakhir URL --}}
@php
    // Mapping segmen terakhir ke komponen Blade
    $components = [
        'employeedata' => 'employeedata',
        'mmf_30_request' => 'mmf30',
        'wphc_request' => 'wphc',
        // Tambahkan mapping lainnya di sini
    ];

    // Mendapatkan segmen terakhir dari URL
    $segment = Request::segment(count(Request::segments()));

    // Memeriksa apakah segmen ada array mapping
    $componentExists = array_key_exists($segment, $components);
@endphp

{{-- Render komponen berdasarkan segmen terakhir --}}
@if($componentExists)
    @component('components.' . $components[$segment])
    @endcomponent
@endif