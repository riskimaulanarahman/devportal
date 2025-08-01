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
                
                <div class="mb-3">
                    <a href="public/dir/new_mcop_template.csv" 
                       class="btn btn-info" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       role="button">
                      Download Template
                    </a>
                  </div>

                <form id="form-import" action="{{ route('import.mcopcsv') }}" method="POST" enctype="multipart/form-data">
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
                    <a href="{{ route('export.complex.csv') }}" class="btn btn-success">
                        Export CSV
                    </a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <p class="card-title-desc">Data MCOP</p>
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
    let modname = 'mcop';
    function store(module) {
        var store = new DevExpress.data.CustomStore({
            key: "id",
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
            { dataField: "e_SAP_ID", caption: "SAP ID" },
            { dataField: "e_Name", caption: "Name" },
            { dataField: "e_Position", caption: "Position" },
            { dataField: "e_Dept", caption: "Dept" },
            { dataField: "e_Estate", caption: "Estate" },
            { dataField: "e_Eligible", caption: "Eligible" },
            { dataField: "v_Vehicle_Type", caption: "Vehicle Type" },
            { dataField: "v_Number_Plate_Old", caption: "Number Plate Old" },
            { dataField: "v_Number_Plate_New", caption: "Number Plate New" },
            { dataField: "v_PO_Order", caption: "PO Order" },
            { dataField: "v_Unit_From", caption: "Unit From" },
            { dataField: "v_Date_of_Receipt", caption: "Date Of Receipt", dataType: "date", format: "dd-MM-yyyy" },
            { dataField: "v_SAP_Asset_Number", caption: "SAP Asset Number" },
            { dataField: "v_Machine_No", caption: "Machine No" },
            { dataField: "v_Frame_Number", caption: "Frame Number" },
            { dataField: "v_Vehicle_Accessories", caption: "Vehicle Accessories" },
            { dataField: "v_Unit_Build_Year", caption: "Unit Build Year" },
            { dataField: "v_Unit_Condition", caption: "Unit Condition" },
            { dataField: "c_Contract_No", caption: "Contract No" },
            { dataField: "c_Unit_Handover_Date", caption: "Unit Handover Date", dataType: "date", format: "dd-MM-yyyy" },
            { dataField: "c_Unit_Handover_No", caption: "Unit Handover No" },
            { dataField: "c_Unit_Price", caption: "Unit Price", dataType: "number" },
            { dataField: "c_Period_Months", caption: "Period Months", dataType: "number" },
            { dataField: "c_Former_Owner", caption: "Former Owner" },
            { dataField: "c_Book_Value", caption: "Book Value", dataType: "number" },
            { dataField: "c_Previous_Contract_Ended_Date", caption: "Prev. Contract Ended", dataType: "date", format: "dd-MM-yyyy" },
            { dataField: "c_New_Contract_Value", caption: "New Contract Value", dataType: "number" },
            { dataField: "c_Monthly_Fuel_Subsidy", caption: "Monthly Fuel Subsidy", dataType: "number" },
            { dataField: "c_Start_Contract", caption: "Start Contract", dataType: "date", format: "dd-MM-yyyy" },
            { dataField: "c_End_Contract", caption: "End Contract", dataType: "date", format: "dd-MM-yyyy" },
            { dataField: "c_Remarks", caption: "Remarks" },
            { dataField: "c_SPH", caption: "SPH" },
            { dataField: "r_Recondition_Start_Date", caption: "Recondition Start", dataType: "date", format: "dd-MM-yyyy" },
            { dataField: "r_Recondition_End_Date", caption: "Recondition End", dataType: "date", format: "dd-MM-yyyy" },
            { dataField: "r_Recondition_Cost", caption: "Recondition Cost", dataType: "number" },
            { dataField: "r_Recondition_Contract_Value", caption: "Recondition Contract Value", dataType: "number" },
            { dataField: "s_STNK_No", caption: "STNK No" },
            { dataField: "s_STNK_Expiry_Date", caption: "STNK Expiry", dataType: "date", format: "dd-MM-yyyy" },
            { dataField: "s_STNK_Tax", caption: "STNK Tax", dataType: "number" },
            { dataField: "s_STNK_Position", caption: "STNK Position" },
            { dataField: "b_BPKB_No", caption: "BPKB No" },
            { dataField: "b_BPKB_Position", caption: "BPKB Position" }
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