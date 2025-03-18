@extends('layouts.master')
@section('title') Import MCOP @endsection
@section('content')
@section('pagetitle') Import MCOP @endsection

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <p class="card-title-desc">Unggah file CSV untuk mengimpor data MCOP ke dalam sistem.</p>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('import.mcopcsv') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Pilih File CSV</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                        <small class="form-text text-muted">File harus berformat CSV dan sesuai dengan template yang disediakan.</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Import</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    // Tambahkan script khusus jika diperlukan
    document.getElementById('csv_file').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file && file.type !== 'text/csv') {
            alert('File harus berformat CSV.');
            event.target.value = ''; // Reset input file
        }
    });
</script>
@endsection