var modname = 'materialrequest';
var modelclass = 'MaterialReq';
var popupmode;

function moveEditColumnToLeft(dataGrid) {
    dataGrid.columnOption("command:edit", { 
        visibleIndex: -1,
        width: 80 
    });
}

aPermissions = {}; // Deklarasikan array untuk menyimpan hasil

async function fetchAndStorePermissions() {
    try {
        const permissions = await checkUserAccess(modelclass, usersid);
        aPermissions = permissions; // Simpan hasil langsung ke objek
    } catch (error) {
        console.error('Error fetching user permissions:', error);
        // Tambahkan objek default ke dalam array jika terjadi kesalahan
        aPermissions = null;
    }
}

// Panggil fungsi untuk fetch data dan masukkan ke dalam array
fetchAndStorePermissions();

var dataGrid = $("#gridContainer").dxDataGrid({    
    dataSource: store(modname),
    allowColumnReordering: true,
    allowColumnResizing: true,
    columnHidingEnabled: true,
    rowAlternationEnabled: false,
    wordWrapEnabled: true,
    autoExpandAll: true,
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
        mode: "popup",
        allowAdding: false,
        allowUpdating: false,
        allowDeleting: true,
    },
    scrolling: {
        mode: "virtual"
    },
    pager: {
        visible: false,
        showInfo: true,
    },
    columns: [
        {
            caption: 'Action',
            width: 140,
            cellTemplate: function(container, options) {

                var isMine = options.data.isMine;
                var isPendingOnMe = options.data.isPendingOnMe;
                var isAssignment = options.data.isAssignment;
                var prStatus = options.data.prStatus;
                var reqid = options.data.id;
                var reqstatus = options.data.requestStatus;
                var mode = (reqstatus == 0 || reqstatus == 2 && (isMine == 1)) ? 'edit' : (reqstatus == 1 && ((isMine == 0 && isPendingOnMe == 1) || (isMine == 1 && isPendingOnMe == 1)) ? 'approval' : 'view') ;
                var arrColor = [
                    "btn-secondary",
                    (mode == 'approval' && reqstatus == 1) ? "btn-danger" : "btn-primary",
                    "btn-warning",
                    ((prStatus == 0 && isAssignment == 1 && reqstatus == 3)) ? "btn-primary" : "btn-success",
                    "btn-danger",
                ];

                var viewIcon = (mode == 'approval' && reqstatus == 1) 
                ? "fa-check" 
                : ((prStatus == 0 && isAssignment == 1 && reqstatus == 3) 
                    ? "fa-pen" 
                    : "fa-search");
    
                $('<button class="btn '+arrColor[reqstatus]+'" id="btnreqid'+reqid+'"><i class="fa '+viewIcon+'"></i></button>').on('dxclick', function(evt) {
                    evt.stopPropagation();
                
                            popup.option({
                                contentTemplate: () => popupContentTemplate(reqid,mode,options),
                            });
                            popup.show();

                }).appendTo(container);
                if((reqstatus == 1 || reqstatus == 2) && ((isMine == 1 && (isPendingOnMe == 0 || isPendingOnMe == null)))) {
                    $('<button class="btn btn-danger" id="btnreqid'+reqid+'" style="margin-left: 3px;">Cancel</button>').on('dxclick', function(evt) {
                        evt.stopPropagation();
                            
                        // var result = confirm('Are you sure you want to cancel this submission ?');

                        // if (result) {
                        //     sendRequest(apiurl + "/submissionrequest/"+reqid+"/"+modelclass, "POST", {
                        //         requestStatus:0,
                        //         action:'submission',
                        //         approvalAction: 0
                        //     }).then(function(response){
                        //         if(response.status != 'error') {
                        //             dataGrid.refresh();
                        //         }
                        //     });
                        // } else {
                        //     alert('Cancelled.');
                        // }

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "Are you sure you want to cancel this submission?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, cancel it'
                          }).then((result) => {
                            if (result.isConfirmed) {
                                showLoadingScreen();
                                sendRequest(apiurl + "/submissionrequest/"+reqid+"/"+modelclass, "POST", {
                                    requestStatus:0,
                                    action:'submission',
                                    approvalAction: 0
                                }).then(function(response){
                                    if(response.status != 'error') {
                                        hideLoadingScreen();
                                        dataGrid.refresh();
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Saved',
                                            text: 'The submission has been cancelled.',
                                        });
                                    } else {
                                        hideLoadingScreen();
                                    }
                                });
                                } else {
                                    hideLoadingScreen();
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Cancelled',
                                        text: 'The submission cancellation has been cancelled.'
                                    });
                                }
                          });
    
                    }).appendTo(container); 
                }
            
            }
        },
        {
            caption: "Code",
            dataField: 'code',
            width: 180,
        },
        { 
            caption: 'BU',
            dataField: "bu",
            width: 80
        },
        { 
            caption: 'Creator Name',
            dataField: "user.fullname",
            width: 180
        },
        {
            caption: 'PR Status',
            dataField: 'prStatus',
            width: 180,
            encodeHtml: false,
            allowFiltering: false,
            allowHeaderFiltering: true,
            customizeText: function (e) {
                var arrText = [
                    "<span class='btn btn-primary btn-xs btn-status'>Waiting</span>",
                    "<span class='btn btn-success btn-xs btn-status'>Done</span>",
                    "<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
                ];
                return arrText[e.value];
            },
        },
        {
            caption: "PR PIC",
            dataField: 'FullName',
            width: 180,
        },
        {
            dataField: 'requestStatus',
            width: 240,
            encodeHtml: false,
            allowFiltering: false,
            allowHeaderFiltering: true,
            customizeText: function (e) {
                var arrText = [
                    "<span class='btn btn-secondary btn-xs btn-status'>Draft</span>",
                    "<span class='btn btn-primary btn-xs btn-status'>Waiting Approval</span>",
                    "<span class='btn btn-warning btn-xs btn-status'>Rework</span>",
                    "<span class='btn btn-success btn-xs btn-status'>Approved</span>",
                    "<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
                ];
                return arrText[e.value];
            },
        },
        {
            dataField: "approveddoc",
            caption:"Approval Doc",
            allowFiltering: false,
            allowSorting: false,
            formItem: { visible: false},
            cellTemplate: function (container, options) {
                if ((options.value!="") && (options.value)){
                    $("<div />").dxButton({
                        icon: 'download',
                        type: "success",
                        text: "Download",
                        onClick: function (e) {
                            window.open(options.value, '_blank');
                        }
                    }).appendTo(container);
                }
            }
        },
    
    ],
    columnChooser: {
    enabled: true,
    },
    export: {
        enabled: true,
        fileName: modname,
        excelFilterEnabled: true,
        allowExportSelectedData: true
    },
    onContentReady: function(e){
        moveEditColumnToLeft(e.component);
        runpopup();
    },
    onCellPrepared: function (e) {
        if (e.rowType == "data") {
            if(e.data.isParent === 1) {
                e.cellElement.css('background','rgba(128, 128, 0,0.1)')
            }
        }
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
    onDataErrorOccurred: function(e) {
        // Menampilkan pesan kesalahan
        console.log("Terjadi kesalahan saat memuat data (0):", e.error.message);

        // Memuat ulang Page
        location.reload();
    }
}).dxDataGrid("instance");

$('#btnadd').on('click',function(){
    showLoadingScreen();
    sendRequest(apiurl + "/"+modname, "POST", {requestStatus:0}).then(function(response){
        const reqid = response.data.id;
        const mode = 'add';
        const options = {"data": {"isMine": 1}};
        popup.option({
            contentTemplate: () => popupContentTemplate(reqid,mode,options),
        });
        popup.show();
        hideLoadingScreen();
    });
})

const accordionItems = [
    {
        ID: 1,
        Title: '<i class="far fa-newspaper"> Form Data</i>',
        visible: true
    },
    {
        ID: 5,
        Title: '',
        visible: false
    },
    {
        ID: 7,
        Title: '<i class="fas fa-users"> Assignment To </i>',
        visible: true
    },
    {
        ID: 6,
        Title: '<i class="fas fa-list-ul"> Details </i>',
        visible: true
    },
    {
        ID: 2,
        Title: '<i class="fas fa-file"> Supporting Document </i>',
        visible: false
    },
    {
        ID: 3,
        Title: '<i class="fas fa-list-ul"> Approver List </i>',
        visible: true
    },
    {
        ID: 4,
        Title: '<i class="fas fa-history"> History </i>',
        visible: true
    },
];

const updateVisibleById = (itemId, visible) => {
    accordionItems.forEach(item => {
      if (item.ID === itemId) {
        item.visible = visible;
      }
    });
  };

const popupContentTemplate = function (reqid,mode,options) {
    
        isMine = options.data.isMine;
        var isPendingOnMe = options.data.isPendingOnMe;
        var isAssignment = options.data.isAssignment;

        popupid = reqid;

        const scrollView = $('<div />');

        if ((isMine == 1 || isPendingOnMe == 1) && (mode == 'add' || mode == 'edit' || mode == 'approval')) {
            if((isPendingOnMe == 1) && (mode == 'approval')) {
                var approvalOptions = 
                    '<div class="row">' +
                        '<div class="col-md-6">' +
                        '<label for="remarks">Approval Action :</label>' +
                        '<div class="form-check">'+
                            '<input class="form-check-input" type="radio" name="approvalaction" id="rappraction1" value="3">'+
                            '<label class="form-check-label" for="rappraction1">'+
                            'Approved'+
                            '</label>'+
                        '</div>'+
                        '<div class="form-check">'+
                            '<input class="form-check-input" type="radio" name="approvalaction" id="rappraction2" value="2">'+
                            '<label class="form-check-label" for="rappraction2">'+
                            'Reworked'+
                            '</label>'+
                        '</div>'+
                        '<div class="form-check mb-3">'+
                            '<input class="form-check-input" type="radio" name="approvalaction" id="rappraction3" value="4">'+
                            '<label class="form-check-label" for="rappraction3">'+
                            'Rejected'+
                            '</label>'+
                        '</div>'+
                        '</div>' +
                        '<div class="col-md-6">' +
                        '<div class="form-group">' +
                            '<label for="remarks">Remarks :</label>' +
                            '<textarea class="form-control" id="remarks" rows="3"></textarea>' +
                        '</div>' +
                        '</div>' +
                    '</div><hr>';
            } else {
                var approvalOptions = '';
            }
            
            scrollView.append('<div class="row">' +
                '<div class="col-lg-12">' +
                '<div class="card">' +
                    '<div class="card-header">' +
                    '<h5 class="card-title">Form Action</h5>' +
                    '</div>' +
                    '<div class="card-body" style="border-bottom-color: darkseagreen !important;border-left-color: darkseagreen;">' +
                    approvalOptions +
                    '<button id="btn-submit" type="button" onClick="btnreqsubmit('+reqid+',\''+mode+'\')" class="btn btn-success waves-effect btn-label waves-light m-1"><i class="bx bx-check-double label-icon"></i> Submit Submission</button>'+
                    '</div>' +
                '</div>' +
                '</div>' +
            '</div>');
        }

        if(options.data.requestStatus == 3 || (admin == 1 || aPermissions.allowView == 1)) {
            updateVisibleById(7, true);
        } else {
            updateVisibleById(7, false);
        }

        var dataSector = [
            { bu: 'IHM', sector: 'NKL' },
            { bu: 'IHM', sector: 'TRN' },
            { bu: 'IHM', sector: 'SPU' },
            { bu: 'IHM', sector: 'SNI' },
            { bu: 'IHM', sector: 'HO' },
            { bu: 'AHL', sector: 'SNI' },
            { bu: 'AHL', sector: 'SBS' },
            { bu: 'AHL', sector: 'SSP' },
            { bu: 'AHL', sector: 'HO' },
        ];

        scrollView.append("<hr>"),

        scrollView.append(

            $("<div>").dxAccordion({
                dataSource: accordionItems,
                animationDuration: 600,
                selectedItems: [accordionItems[0],accordionItems[1],accordionItems[2],accordionItems[3],accordionItems[4],accordionItems[5],accordionItems[6],accordionItems[7]],
                collapsible: true,
                multiple: true,
                itemTitleTemplate: function (data) {
                    return '<small style="margin-bottom:10px !important ;">'+data.Title+'</small>'
                },
                itemTemplate: function (data) {
                    var infoContent2 = $("<div id='infoContent2'>");
                    if(data.ID == 1) {
                        if (mode == 'add' || mode == 'edit'){
                            $("<span style='color:red;font-size:11pt'>").html('Silahkan lengkapi <b><i style="color:black;font-weight:bold" class="far fa-newspaper"> Form Data </i></b> dan tekan tombol <b>Simpan</b> (<i style="color:black;font-weight:bold" class="fas fa-save"></i>) yang ada di pojok kanan atas tabel, sebelum klik tombol <span style="color:black;font-weight:bold"><i class="bx bx-check-double label-icon"></i> Submit Submission</span>').appendTo(infoContent2);
                        }

                        let formData = $("<div id='formdata'>").dxDataGrid({    
                            dataSource: storedetail(modname,reqid),
                            allowColumnReordering: true,
                            allowColumnResizing: true,
                            columnsAutoWidth: true,
                            rowAlternationEnabled: true,
                            wordWrapEnabled: true,
                            showBorders: true,
                            showColumnLines:true,
                            filterRow: { visible: false },
                            filterPanel: { visible: false },
                            headerFilter: { visible: false },
                            searchPanel: {
                                visible: false,
                                width: 240,
                                placeholder: 'Search...',
                            },
                            sorting: {
                                mode: "none" // or "multiple" | "none"
                            },
                            editing: {
                                useIcons:true,
                                mode: "batch",
                                allowAdding: false,
                                allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 || isAssignment == 1 ? true : false),
                                allowDeleting: false,
                            },
                            scrolling: {
                                mode: "virtual"
                            },
                            columns: [
                                {
                                    caption: 'Code',
                                    dataField: 'code',
                                    allowFiltering: false,
                                    allowHeaderFiltering: false,
                                    editorOptions: { 
                                        readOnly: true
                                    }
                                },
                                {
                                    dataField: "bu",
                                    caption: "BU",
                                    lookup: {
                                        dataSource: listOption('/list-company','id','CompanyCode'),  
                                        valueExpr: 'CompanyCode',
                                        displayExpr: 'CompanyCode',
                                    },
                                    editorOptions: { 
                                        readOnly: true
                                    },
                                },
                                {
                                    caption: 'Sector',
                                    dataField: 'sector',
                                    lookup: {
                                        dataSource: function (options) {
                                            return {
                                                store: {
                                                    type: 'array',
                                                    data: dataSector
                                                },
                                                filter: options.data ? ["bu", "=", options.data.bu] : null
                                            };
                                        },
                                        valueExpr: 'sector',
                                        displayExpr: 'sector',
                                    },
                                    validationRules: [{ type: "required" }],
                                    // editorOptions: { 
                                    //     readOnly: (isMine == 0 && isPIC == 0 && isMineCompleted == 0 || (mode == 'add' || mode == 'edit')) ? false : (isMine == 1 && isPIC == 0 && isMineCompleted == 1 || (mode == 'add' || mode == 'edit')) ? false : true,
                                    // },
                                },
                                {
                                    caption: 'PR Status',
                                    dataField: 'prStatus',
                                    lookup: { 
                                        dataSource: [{id:0,name:"Waiting"},{id:1,name:"Done"},{id:2,name:"Rejected"}],  
                                        valueExpr: 'id',
                                        displayExpr: 'name',
                                    },
                                    visible: (options.data.requestStatus == 3) ? true : false
                                },
                                {
                                    dataField: "created_at",
                                    dataType: "date",
                                    format: "dd-MM-yyyy", 
                                    editorOptions: { 
                                        readOnly: true
                                    }
                                },
                            ],
                            export: {
                                enabled: false,
                                fileName: modname,
                                excelFilterEnabled: true,
                                allowExportSelectedData: true
                            },
                            onInitialized: function(e) {
                                dataGrid1 = e.component;
                            },
                            onContentReady: function(e){
                                moveEditColumnToLeft(e.component);
                            },
                            onInitNewRow : function(e) {
                            },
                            onSaved: function(e) {
                            },
                            onEditCanceled: function(e) {
                            },
                            onToolbarPreparing: function(e) {
                                e.toolbarOptions.items.unshift({						
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function() {
                                            dataGrid1.refresh();
                                        }
                                    }
                                });
                            },
                            onEditorPreparing: function (e) {
                
                            },
                            onCellPrepared: function (e) {
                                if (e.column.index == 0 && e.rowType == "data") {
                                    if(e.data.code === null) {
                                        $("#formdata").dxDataGrid('columnOption','code', 'visible', false);
                                    } else {
                                        $("#formdata").dxDataGrid('columnOption','code', 'visible', true);
                                    }
                                }
                                if ( e.rowType == "data" && (e.column.index==2)) {
                                    if (e.value == 0 || e.value === "" || e.value === null || e.value === undefined || /^\s*$/.test(e.value)) {
                                        e.cellElement.css({
                                            "backgroundColor": "#ffe6e6",
                                            "border": "0.5px solid #f56e6e"
                                        })
                                    } else {
                                        e.cellElement.css({
                                            "backgroundColor": "#90EE90",
                                            "border": "0.5px solid #32CD32 "
                                        })
                                    }
                                }
                            },
                            onDataErrorOccurred: function(e) {
                                // Menampilkan pesan kesalahan
                                console.log("Terjadi kesalahan saat memuat data (1):", e.error.message);
                        
                                // Memuat ulang DataGrid
                                dataGrid1.refresh();
                            }
                        }).appendTo(infoContent2)

                        return infoContent2
                    } 
                    else if(data.ID == 6) {
                        return formData = $("<div id='formdetail'>").dxDataGrid({    
                            dataSource: storewithmodule('materialdetail',modelclass,reqid),
                            allowColumnReordering: true,
                            allowColumnResizing: true,
                            columnsAutoWidth: true,
                            rowAlternationEnabled: true,
                            wordWrapEnabled: true,
                            showBorders: true,
                            showColumnLines:true,
                            filterRow: { visible: false },
                            filterPanel: { visible: false },
                            headerFilter: { visible: false },
                            searchPanel: {
                                visible: true,
                                width: 240,
                                placeholder: 'Search...',
                            },
                            sorting: {
                                mode: "none" // or "multiple" | "none"
                            },
                            editing: {
                                useIcons:true,
                                mode: "batch",
                                allowAdding: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                                allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                                allowDeleting: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            },
                            scrolling: {
                                rowRenderingMode: 'virtual',
                            },
                            paging: {
                                pageSize: 5,
                            },
                            pager: {
                                visible: true,
                                allowedPageSizes: [5, 15, 'all'],
                                showPageSizeSelector: true,
                                showInfo: true,
                                showNavigationButtons: true,
                            },
                            columns: [
                                {
                                    caption: 'Description',
                                    dataField: 'catalog_id',
                                    lookup: {
                                        dataSource: listOption('/list-ecatalog','id','description'),  
                                        valueExpr: 'id',
                                        displayExpr: 'description'
                                    },
                                    validationRules: [{ type: "required" }]
                                },
                                {
                                    caption: 'Material Code',
                                    dataField: 'materialCode',
                                    editorOptions: { 
                                        readOnly: true,
                                    }
                                },
                                {
                                    caption: 'Part No',
                                    dataField: 'part_number',
                                    editorOptions: { 
                                        readOnly: true,
                                    }
                                },
                                {
                                    caption: 'PG',
                                    dataField: 'pg',
                                    editorOptions: { 
                                        readOnly: true,
                                    }
                                },
                                {
                                    caption: 'UOM',
                                    dataField: 'uom',
                                    editorOptions: { 
                                        readOnly: true,
                                    }
                                },
                                // {
                                //     caption: 'Quantity - Required',
                                //     dataField: 'required',
                                //     dataType: 'number',
                                //     validationRules: [{ type: "required" }]
                                // },
                                {
                                    caption: 'Quantity - Order',
                                    dataField: 'order',
                                    dataType: 'number',
                                    validationRules: [{ type: "required" }]
                                },
                                {
                                    caption: 'Unit Price',
                                    dataField: 'unit_price',
                                    dataType: 'number',
                                    format: "fixedPoint",
                                    editorOptions: {
                                        format: "fixedPoint",
                                        readOnly: true,
                                    }
                                },
                                {
                                    caption: 'Amount',
                                    dataField: 'amount',
                                    dataType: 'number',
                                    format: "fixedPoint",
                                    editorOptions: {
                                        format: "fixedPoint",
                                        readOnly: true,
                                    }
                                },
                                {
                                    caption: "Remarks",
                                    dataField:'remarks',
                                    dataType: "string",
                                },
                            ],
                            export: {
                                enabled: false,
                                fileName: modname,
                                excelFilterEnabled: true,
                                allowExportSelectedData: true
                            },
                            onInitialized: function(e) {
                                dataGriddetail = e.component;
                            },
                            onContentReady: function(e){
                                moveEditColumnToLeft(e.component);
                            },
                            onToolbarPreparing: function(e) {
                                e.toolbarOptions.items.unshift({						
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function() {
                                            dataGriddetail.refresh();
                                        }
                                    }
                                });
                            },
                            onEditorPrepared: function (e) {
                            },
                            onEditorPreparing: function (e) {
                                if ((e.dataField == "catalog_id") && e.parentType == "dataRow") {
                                    e.editorName = "dxDropDownBox";                
                                    e.editorOptions.dropDownOptions = {                
                                        height: 500,
                                        width: 1000,
                                    };
                                    e.editorOptions.contentTemplate = function (args, container) {
                        
                                        var value = args.component.option("value"),
                                            $dataGrid = $("<div>").dxDataGrid({
                                                width: '100%',
                                                dataSource: args.component.option("dataSource"),
                                                keyExpr: "id",
                                                columns: [
                                                    "materialCode",
                                                    "category",
                                                    {
                                                        dataField: 'description',
                                                        width: 240
                                                    },
                                                    "brand",
                                                    "type",
                                                    "uom",
                                                    "historicalPrice",
                                                    "currency",
                                                    "lastUpdated",
                                                    "pg"
                                                ],
                                                scrolling: {
                                                    mode: "virtual"
                                                },
                                                pager: {
                                                    visible: false,
                                                    showInfo: true,
                                                },
                                                columnsAutoWidth: true,
                                                allowColumnResizing: true,
                                                hoverStateEnabled: true,
                                                paging: { enabled: true, pageSize: 10 },
                                                filterRow: { visible: true },
                                                height: '90%',
                                                showRowLines: true,
                                                showBorders: true,
                                                selection: { mode: "single" },
                                                selectedRowKeys: [value],
                                                focusedRowEnabled: true,
                                                focusedRowKey: value,
                                                searchPanel: {
                                                    visible: true,
                                                    width: 265,
                                                    placeholder: "Search..."
                                                },
                                                onSelectionChanged: function (selectedItems) {
                                                    const keys = selectedItems.selectedRowKeys;
                                                    const hasSelection = keys.length;
                                                    if(hasSelection !== 0) {
                                                        args.component.option('value', hasSelection ? keys[0] : null);
                                                        args.component.close();
                                                    }
                                                }
                                            });
                        
                                        var dataGrid = $dataGrid.dxDataGrid("instance");
                        
                                        args.component.on("valueChanged", function (args) {
                                            var value = args.value;
                        
                                            dataGrid.selectRows(value, false);
                                        });
                                        container.append($dataGrid);
                                        $("<div>").dxButton({
                                            text: "Close",
                        
                                            onClick: function (ev) {
                                                args.component.close();
                                            }
                                        }).css({ float: "right", marginTop: "10px" }).appendTo(container);
                                        return container;
                        
                                    };
                                }
                            },
                            onCellPrepared: function (e) {
                                if ( e.rowType == "data" && (e.column.index==5)) {
                                    if (e.value === "" || e.value === null || e.value === undefined || /^\s*$/.test(e.value)) {
                                        e.cellElement.css({
                                            "backgroundColor": "#ffe6e6",
                                            "border": "0.5px solid #f56e6e"
                                        })
                                    }
                                }
                            },
                            onDataErrorOccurred: function(e) {
                                // Menampilkan pesan kesalahan
                                console.log("Terjadi kesalahan saat memuat data (6):", e.error.message);
                        
                                // Memuat ulang DataGrid
                                dataGriddetail.refresh();
                            }
                        })
                    }
                    else if(data.ID == 2) {
                        var supporting = $("<div id='formattachment'>").dxDataGrid({    
                            dataSource: storewithmodule('attachmentrequest',modelclass,reqid),
                            allowColumnReordering: true,
                            allowColumnResizing: true,
                            columnsAutoWidth: true,
                            rowAlternationEnabled: true,
                            wordWrapEnabled: true,
                            showBorders: true,
                            filterRow: { visible: false },
                            filterPanel: { visible: false },
                            headerFilter: { visible: false },
                            searchPanel: {
                                visible: true,
                                width: 240,
                                placeholder: 'Search...',
                            },
                            editing: {
                                useIcons:true,
                                mode: "popup",
                                allowAdding: ((isMine == 1 && mode == 'view') ? true : (isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                                allowUpdating: ((isMine == 1 && mode == 'view') ? true : (isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                                allowDeleting: ((isMine == 1 && mode == 'view') ? true : (isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            },
                            paging: { enabled: true, pageSize: 10 },
                            columns: [
                                { 
                                    caption: 'Attachment',
                                    dataField: "path",
                                    allowFiltering: false,
                                    allowSorting: false,
                                    cellTemplate: cellTemplate,
                                    editCellTemplate: editCellTemplate,
                                    validationRules: [{ type: "required" }]
                                },
                                {
                                    dataField: "remarks"
                                },
                            ],
                            export: {
                                enabled: false,
                                fileName: modname,
                                excelFilterEnabled: true,
                                allowExportSelectedData: true
                            },
                            onInitialized: function(e) {
                                dataGridAttachment = e.component;
                            },
                            onContentReady: function(e){
                                moveEditColumnToLeft(e.component);
                            },
                            onInitNewRow : function(e) {
                            },
                            onToolbarPreparing: function(e) {
                                e.toolbarOptions.items.unshift({						
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function() {
                                            dataGridAttachment.refresh();
                                        }
                                    }
                                })
                            },
                            onDataErrorOccurred: function(e) {
                                // Menampilkan pesan kesalahan
                                console.log("Terjadi kesalahan saat memuat data (2):", e.error.message);
                        
                                // Memuat ulang DataGrid
                                dataGridAttachment.refresh();
                            }
                        })

                        return supporting;
                    }
                    else if(data.ID == 7) {
                        return $("<div id='formassignmentto'>").dxDataGrid({    
                            dataSource: storewithmodule('assignmentto',modelclass,reqid),
                            allowColumnReordering: true,
                            allowColumnResizing: true,
                            columnsAutoWidth: true,
                            rowAlternationEnabled: true,
                            wordWrapEnabled: true,
                            showBorders: true,
                            filterRow: { visible: false },
                            filterPanel: { visible: false },
                            headerFilter: { visible: false },
                            searchPanel: {
                                visible: true,
                                width: 240,
                                placeholder: 'Search...',
                            },
                            editing: {
                                useIcons:true,
                                mode: "cell",
                                allowAdding: (admin == 1) ? true : aPermissions.allowAdd,
                                allowUpdating: (admin == 1) ? true : aPermissions.allowEdit,
                                allowDeleting: (admin == 1) ? true : aPermissions.allowDelete,
                            },
                            paging: { enabled: true, pageSize: 10 },
                            columns: [
                                {
                                    caption: "PIC Name",
                                    dataField: "employee_id",
                                    lookup: {
                                        dataSource: listOption('/list-employeeall','id','fullname'),  
                                        valueExpr: 'id',
                                        displayExpr: 'fullname',
                                    },
                                    validationRules: [{ type: "required" }]
                                },
                            ],
                            export: {
                                enabled: false,
                                fileName: modname,
                                excelFilterEnabled: true,
                                allowExportSelectedData: true
                            },
                            onInitialized: function(e) {
                                dataGridAssignmentto = e.component;
                            },
                            onContentReady: function(e){
                                moveEditColumnToLeft(e.component);
                            },
                            onInitNewRow : function(e) {
                            },
                            onEditorPreparing: function (e) {
                                if (e.dataField == "employee_id" && e.parentType == "dataRow") {
                                    e.editorName = "dxDropDownBox";                
                                    e.editorOptions.dropDownOptions = {                
                                        height: 500,
                                        width: 600
                                    };
                                    e.editorOptions.contentTemplate = function (args, container) {
                        
                                        var value = args.component.option("value"),
                                            $dataGrid = $("<div>").dxDataGrid({
                                                width: '100%',
                                                dataSource: args.component.option("dataSource"),
                                                keyExpr: "id",
                                                columns: ["sapid","fullname","companycode","departmentname"],
                                                hoverStateEnabled: true,
                                                paging: { enabled: true, pageSize: 10 },
                                                filterRow: { visible: true },
                                                height: '90%',
                                                showRowLines: true,
                                                showBorders: true,
                                                selection: { mode: "single" },
                                                selectedRowKeys: [value],
                                                focusedRowEnabled: true,
                                                focusedRowKey: args.component.option("value"),
                                                searchPanel: {
                                                    visible: true,
                                                    width: 265,
                                                    placeholder: "Search..."
                                                },
                                                onSelectionChanged: function (selectedItems) {
                                                    const keys = selectedItems.selectedRowKeys;
                                                    const hasSelection = keys.length;
                                                    args.component.option('value', hasSelection ? keys[0] : null);
                                                    // console.log(hasSelection)
                                                    if(hasSelection !== 0) {
                                                        args.component.close();
                                                    }
                                                }
                                            });
                        
                                        var dataGrid = $dataGrid.dxDataGrid("instance");
                        
                                        args.component.on("valueChanged", function (args) {
                                            var value = args.value;
                        
                                            dataGrid.selectRows(value, false);
                                        });
                                        container.append($dataGrid);
                                        $("<div>").dxButton({
                                            text: "Close",
                        
                                            onClick: function (ev) {
                                                args.component.close();
                                            }
                                        }).css({ float: "right", marginTop: "10px" }).appendTo(container);
                                        return container;
                        
                                    };
                                }
                            },
                            onToolbarPreparing: function(e) {
                                e.toolbarOptions.items.unshift({						
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function() {
                                            dataGridAssignmentto.refresh();
                                        }
                                    }
                                })
                            },
                            onDataErrorOccurred: function(e) {
                                // Menampilkan pesan kesalahan
                                console.log("Terjadi kesalahan saat memuat data (5):", e.error.message);
                        
                                // Memuat ulang DataGrid
                                dataGridAssignmentto.refresh();
                            }
                        })
                    }
                    else if(data.ID == 3) {
                        return $("<div id='formapproverlist'>").dxDataGrid({    
                            dataSource: storewithmodule('approverlistrequest',modelclass,reqid),
                            allowColumnReordering: true,
                            allowColumnResizing: true,
                            columnsAutoWidth: true,
                            rowAlternationEnabled: true,
                            wordWrapEnabled: true,
                            showBorders: true,
                            filterRow: { visible: false },
                            filterPanel: { visible: false },
                            headerFilter: { visible: false },
                            searchPanel: {
                                visible: true,
                                width: 240,
                                placeholder: 'Search...',
                            },
                            editing: {
                                useIcons:true,
                                mode: "cell",
                                allowAdding: (admin == 1) ? true : false,
                                allowUpdating: (admin == 1) ? true : false,
                                allowDeleting: (admin == 1) ? true : false,
                            },
                            scrolling: {
                                mode: "virtual"
                            },
                            columns: [
                                {
                                    caption: "Fullname",
                                    dataField: "approver_id",
                                    lookup: {
                                        dataSource: listOption('/list-approver/'+modelclass,'id','fullname'),  
                                        valueExpr: 'id',
                                        displayExpr: 'fullname',
                                    },
                                    validationRules: [
                                        { 
                                            type: "required" 
                                        }
                                    ]
                                },
                                {
                                    dataField: "ApprovalType",
                                    editorOptions: { 
                                        readOnly: true
                                    }
                                },
                                {
                                    dataField: "approvalDate",
                                    dataType: "datetime",
                                    format: "dd-MM-yyyy hh:mm:ss",
                                },
                                {
                                    caption: "Approval Status",
                                    dataField: "approvalAction",
                                    encodeHtml: false,
                                    allowFiltering: false,
                                    allowHeaderFiltering: true,
                                    customizeText: function (e) {
                                        var arrText = [
                                            "<span class='btn btn-secondary btn-xs btn-status'>Draft</span>",
                                            "<span class='btn btn-primary btn-xs btn-status'>Waiting Approval</span>",
                                            "<span class='btn btn-warning btn-xs btn-status'>Rework</span>",
                                            "<span class='btn btn-success btn-xs btn-status'>Approved</span>",
                                            "<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
                                        ];
                                        return arrText[e.value];
                                    }
                                },
                            ],
                            export: {
                                enabled: false,
                                fileName: modname,
                                excelFilterEnabled: true,
                                allowExportSelectedData: true
                            },
                            onInitialized: function(e) {
                                dataGridApproverList = e.component;
                            },
                            onContentReady: function(e){
                                moveEditColumnToLeft(e.component);
                            },
                            onInitNewRow : function(e) {
                            },
                            onEditorPreparing: function (e) {
                                if (e.dataField == "approver_id" && e.parentType == "dataRow") {
                                    e.editorName = "dxDropDownBox";                
                                    e.editorOptions.dropDownOptions = {                
                                        height: 500,
                                        width: 600
                                    };
                                    e.editorOptions.contentTemplate = function (args, container) {
                        
                                        var value = args.component.option("value"),
                                            $dataGrid = $("<div>").dxDataGrid({
                                                width: '100%',
                                                dataSource: args.component.option("dataSource"),
                                                keyExpr: "id",
                                                columns: ["fullname","ApprovalType"],
                                                hoverStateEnabled: true,
                                                paging: { enabled: true, pageSize: 10 },
                                                filterRow: { visible: true },
                                                height: '90%',
                                                showRowLines: true,
                                                showBorders: true,
                                                selection: { mode: "single" },
                                                selectedRowKeys: [value],
                                                focusedRowEnabled: true,
                                                focusedRowKey: args.component.option("value"),
                                                searchPanel: {
                                                    visible: true,
                                                    width: 265,
                                                    placeholder: "Search..."
                                                },
                                                onSelectionChanged: function (selectedItems) {
                                                    const keys = selectedItems.selectedRowKeys;
                                                    const hasSelection = keys.length;
                                                    args.component.option('value', hasSelection ? keys[0] : null);
                                                    if(hasSelection !== 0) {
                                                        args.component.close();
                                                    }
                                                }
                                            });
                        
                                        var dataGrid = $dataGrid.dxDataGrid("instance");
                        
                                        args.component.on("valueChanged", function (args) {
                                            var value = args.value;
                        
                                            dataGrid.selectRows(value, false);
                                        });
                                        container.append($dataGrid);
                                        $("<div>").dxButton({
                                            text: "Close",
                        
                                            onClick: function (ev) {
                                                args.component.close();
                                            }
                                        }).css({ float: "right", marginTop: "10px" }).appendTo(container);
                                        return container;
                        
                                    };
                                }
                            },
                            onToolbarPreparing: function(e) {
                                e.toolbarOptions.items.unshift({						
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function() {
                                            dataGridApproverList.refresh();
                                        }
                                    }
                                })
                            },
                            onDataErrorOccurred: function(e) {
                                // Menampilkan pesan kesalahan
                                console.log("Terjadi kesalahan saat memuat data (3):", e.error.message);
                        
                                // Memuat ulang DataGrid
                                dataGridApproverList.refresh();
                            }
                        })

                    }
                    else if(data.ID == 4) {
                        return $("<div id='formhistorylist'>").dxDataGrid({    
                            dataSource: storewithmodule('approverlisthistory',modelclass,reqid),
                            allowColumnReordering: true,
                            allowColumnResizing: true,
                            columnsAutoWidth: true,
                            rowAlternationEnabled: true,
                            wordWrapEnabled: true,
                            showBorders: true,
                            filterRow: { visible: false },
                            filterPanel: { visible: false },
                            headerFilter: { visible: false },
                            searchPanel: {
                                visible: true,
                                width: 240,
                                placeholder: 'Search...',
                            },
                            editing: {
                                useIcons:true,
                                mode: "cell",
                                allowAdding: false,
                                allowUpdating: false,
                                allowDeleting: false,
                            },
                            paging: { enabled: true, pageSize: 10 },
                            columns: [
                                {
                                    dataField: "fullname"
                                },
                                {
                                    caption: "Type",
                                    dataField: "approvalType"
                                },
                                {
                                    caption: "Date",
                                    dataField: "approvalDate",
                                    dataType: "datetime",
                                    format: "dd-MM-yyyy hh:mm:ss",
                                },
                                {
                                    caption: "Action",
                                    dataField: "approvalAction",
                                    encodeHtml: false,
                                    allowFiltering: false,
                                    allowHeaderFiltering: true,
                                    customizeText: function (e) {
                                        var arrText = [
                                            "<span class='btn btn-secondary btn-xs btn-status'>Draft</span>",
                                            "<span class='btn btn-primary btn-xs btn-status'>Submitted</span>",
                                            "<span class='btn btn-warning btn-xs btn-status'>Rework</span>",
                                            "<span class='btn btn-success btn-xs btn-status'>Approved</span>",
                                            "<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
                                            "<span class='btn btn-secondary btn-xs btn-status'>Cancelled</span>",
                                        ];
                                        return arrText[e.value];
                                    }
                                },
                                {
                                    dataField: "remarks"
                                },
                            ],
                            export: {
                                enabled: false,
                                fileName: modname,
                                excelFilterEnabled: true,
                                allowExportSelectedData: true
                            },
                            onInitialized: function(e) {
                                dataGridApproverHistory = e.component;
                            },
                            onContentReady: function(e){
                                moveEditColumnToLeft(e.component);
                            },
                            onInitNewRow : function(e) {
                            },
                            onToolbarPreparing: function(e) {
                                e.toolbarOptions.items.unshift({						
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function() {
                                            dataGridApproverHistory.refresh();
                                        }
                                    }
                                })
                            },
                            onDataErrorOccurred: function(e) {
                                // Menampilkan pesan kesalahan
                                console.log("Terjadi kesalahan saat memuat data (4):", e.error.message);
                        
                                // Memuat ulang DataGrid
                                dataGridApproverHistory.refresh();
                            }
                        })
                    }
                }
            })
        
        );

        scrollView.dxScrollView({
            width: '100%',
            height: '100%',
        })

        return scrollView;
};

function btnreqsubmit(reqid,mode) {

    if (mode == 'add' || mode == 'edit') {
        var dataGridAssignment = $("#formdetail").dxDataGrid("instance");
        var dataSource = dataGridAssignment.getDataSource();
        var rowCount = dataSource.items().length;

        if (rowCount === 0) {
            DevExpress.ui.dialog.alert("The Details does not exist. Please add one.", "Warning");
            return false;
        }
    }

    var btnSubmit = $('#btn-submit');
    btnSubmit.prop('disabled', true);

    var actionForm = (mode == 'approval') ? 'approval' : 'submission';


    if(mode == 'approval') {
        var valapprovalAction = $('input[name="approvalaction"]:checked').val(); // mengambil nilai dari radio button
        var valremarks = $('#remarks').val(); // mengambil nilai dari text area
        if (!valapprovalAction) {
            DevExpress.ui.dialog.alert("Please select approval action.", "Warning");
            btnSubmit.prop('disabled', false);
            return false;
        }
        else if (!valremarks) {
            DevExpress.ui.dialog.alert("Please enter remarks.", "Warning");
            btnSubmit.prop('disabled', false);
            return false;
        }
        
    }

    var valApprovalType = valapprovalAction == 3 ? 'Approved' : valapprovalAction == 2 ? 'Reworked' : valapprovalAction == 4 ? 'Rejected' : '';

    Swal.fire({
        title: 'Are you sure?',
        text: "Are you sure you want to send this submission?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, send it!'
      }).then((result) => {
        if (result.isConfirmed) {
            showLoadingScreen();
            sendRequest(apiurl + "/submissionrequest/"+reqid+"/"+modelclass, "POST", {
                requestStatus:1,
                action: actionForm,
                approvalAction: (valapprovalAction == null) ? 1 : parseInt(valapprovalAction),
                approvalType: valApprovalType,
                remarks: valremarks
          }).then(function(response){
                if(response.status == 'error') {
                    btnSubmit.prop('disabled', false);
                    hideLoadingScreen();
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'The submission has been submited.',
                    });
                    popup.hide();
                    hideLoadingScreen();
                }
          });
        } else {
            btnSubmit.prop('disabled', false);
            Swal.fire({
                icon: 'error',
                title: 'Cancelled',
                text: 'The submission has been cancelled.',
                confirmButtonColor: '#3085d6'
            });
            hideLoadingScreen();
        }
      });

}

function runpopup() {
    popup = $('#popup').dxPopup({
        contentTemplate: popupContentTemplate,
        container: '.content',
        showTitle: true,
        title: 'Submission Detail',
        visible: false,
        dragEnabled: false,
        hideOnOutsideClick: false,
        showCloseButton: true,
        fullScreen : false,
        onShowing: function(e) {
        },
        onShown: function(e) {
        },
        onHidden: function(e) {
            dataGrid.refresh();
        },
        toolbarItems: [
            {
                widget: 'dxButton',
                toolbar: 'bottom',  // Set the button to the bottom toolbar
                location: 'after',
                options: {
                    text: "Fullscreen",
                    onClick: function() {
                        if (popup.option("fullScreen")) {
                            popup.option("fullScreen", false);
                            this.option("text", "Enable Fullscreen");
                        } else {
                            popup.option("fullScreen", true);
                            this.option("text", "Disable Fullscreen");
                        }
                    }
                }
            },
            {
                widget: 'dxButton',
                toolbar: 'bottom',
                location: 'after',
                options: {
                    text: 'Close',
                    onClick() {
                        popup.hide();
                    },
                },
            }
        ]

    }).dxPopup('instance');
}


function cellTemplate(container, options) {
    container.append('<a href="public/upload/'+options.value+'" target="_blank"><img src="public/assets/images/showfile.png" height="50" width="70"></a>');
}

function editCellTemplate(cellElement, cellInfo) {
    let buttonElement = document.createElement("div");
    buttonElement.classList.add("retryButton");
    let retryButton = $(buttonElement).dxButton({
      text: "Retry",
      visible: false,
      onClick: function() {
        // The retry UI/API is not implemented. Use a private API as shown at T611719.
        for (var i = 0; i < fileUploader._files.length; i++) {
          delete fileUploader._files[i].uploadStarted;
        }
        fileUploader.upload();
      }
    }).dxButton("instance");

    $path = "";
    $adafile = "";
    let fileUploaderElement = document.createElement("div");
    let fileUploader = $(fileUploaderElement).dxFileUploader({
      multiple: false,
      accept: ".pptx,.ppt,.docx,.doc,.pdf,.xls,.xlsx,.csv,.png,.jpg,.jpeg,.zip",
      uploadMode: "instantly",
      name: "myFile",
      uploadUrl: apiurl + "/upload-berkas/"+modname,
      onValueChanged: function(e) {
        let reader = new FileReader();
        reader.onload = function(args) {
          imageElement.setAttribute('src', args.target.result);
        }
        reader.readAsDataURL(e.value[0]); // convert to base64 string
      },
      onUploaded: function(e){
          $path = e.request.response;
          $adafile = false;
          cellInfo.setValue(e.request.responseText);
          retryButton.option("visible", false);
      },
      onUploadError: function(e){
          $path = "";
          DevExpress.ui.notify(e.request.response,"error");
      }
    }).dxFileUploader("instance");
        cellElement.append(fileUploaderElement);
        cellElement.append(buttonElement);
  
  }