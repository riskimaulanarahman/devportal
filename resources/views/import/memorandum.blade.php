@extends('layouts.master')
@section('title') Import Memorandum @endsection
@section('content')
@section('pagetitle') Import Memorandum @endsection

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <p class="card-title-desc">Unggah file CSV untuk mengimpor data Memorandum ke dalam sistem.</p>
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
                
                <div class="mb-3">
                    <a href="public/dir/new_memorandum_template.csv" 
                       class="btn btn-info" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       role="button">
                      Download Template
                    </a>
                  </div>

                <form id="form-import" action="{{ route('import.memorandumcsv') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Pilih File CSV</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    <div class="mb-3">
                        <span class="form-text text-muted">File harus berformat CSV dan sesuai dengan template yang disediakan.</span>
                    </div>
                    <div class="mb-3" style="font-weight: bold">
                        <span class="form-text text-muted">Catatan :</span>
                        <ul>
                            <li>Format Date : yyyy-mm-dd</li>
                            <li>Format Harga : Tidak boleh ada karakter special seperti titik,koma atau lainnya</li>
                        </ul>
                    </div>
                    <button type="submit" id="btn-submit" class="btn btn-primary">Import</button>
                    {{-- <a href="{{ route('export.memorandum.csv') }}" class="btn btn-success">
                        Export CSV
                    </a> --}}
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <p class="card-title-desc">Data Memorandum</p>
            </div>
            <div class="card-body" style="padding: 10px; !important">
                <div id="gridContainer" style="height: 600px;"></div>
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

    $('#form-import').on('submit', function () {
        showLoadingScreen();
        $('#btn-submit').prop('disabled', true);
    });
</script>

<script>
    let modname = 'memorandum-import';
    function store(module) {
        var store = new DevExpress.data.CustomStore({
            key: "",
            load: function() {
                return sendRequest(apiurl + "/"+module);
            },
            insert: function(values) {
                return sendRequest(apiurl + "/"+module, "POST", values);
            },
            update: function(key, values) {
                return sendRequest(apiurl + "/"+module+"/"+key, "PUT", values);
            },
            remove: function(key) {
                return sendRequest(apiurl + "/"+module+"/"+key, "DELETE");
            },
        });

        return store;
    }
    var dataGrid = $("#gridContainer").dxDataGrid({    
        dataSource: store(modname),
        allowColumnReordering: true,
        allowColumnResizing: true,
        columnsAutoWidth: true,
        columnMinWidth: 100,
        rowAlternationEnabled: true,
        wordWrapEnabled: false,
        showBorders: true,
        filterRow: { visible: true },
        filterPanel: { visible: true },
        headerFilter: { visible: true },
        searchPanel: {
            visible: true,
            width: 240,
            placeholder: 'Search...',
        },
        editing: {
            useIcons:true,
            mode: "batch",
            allowAdding: false,
            allowUpdating: false,
            allowDeleting: false,
        },
        scrolling: {
            mode: "virtual",
            scrollByContent: true,     // <--- Scroll horizontal jika grid lebih lebar
            showScrollbar: "always"    // <--- Scrollbar selalu tampil
        },
        pager: {
            visible: true,
            showInfo: true,
        },
        columns: [
            { dataField: "fullName", caption: "Fullname" },
            { dataField: "bu", caption: "Company" },
            { dataField: "estate", caption: "Estate" },
            { dataField: "joinDate", dataType: "date", format: "dd-MMM-yyyy", caption: "Join Date" },
            { dataField: "birthOfDate", dataType: "date", format: "dd-MMM-yyyy", caption: "Birth of Date" },
        ],
        export: {
            enabled: true,
            fileName: modname,
            excelFilterEnabled: true,
            allowExportSelectedData: true
        },
        onContentReady: function(e){
            // moveEditColumnToLeft(e.component);
        },
        onEditorPreparing: function (e) {
        },
        onToolbarPreparing: function(e) {
            dataGrid = e.component;

            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        dataGrid.refresh();
                    }
                }
            })
        },
    }).dxDataGrid("instance");
</script>
@endsection