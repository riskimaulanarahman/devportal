var modname = 'jdireport';
var modelclass = 'Jdi';
var popupmode;

function moveEditColumnToLeft(dataGrid) {
    dataGrid.columnOption("command:edit", { 
        visibleIndex: -1,
        width: 80 
    });
}


var dataGrid = $("#gridContainer").dxDataGrid({    
    dataSource: store(modname),
    allowColumnReordering: true,
    allowColumnResizing: true,
    columnHidingEnabled: false,
    rowAlternationEnabled: false,
    wordWrapEnabled: false,
    showBorders: true,
    filterRow: { visible: true },
    filterPanel: { visible: true },
    headerFilter: { visible: true },
    selection: { mode: "multiple" },
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
        allowDeleting: false,
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
            caption: "Code",
            dataField: 'code',
            width: 180,
        },
        {
            dataField: 'noRegistration',
            width: 180,
        },
        {
            dataField: 'registerDate',
            dataType: 'date',
            format: "yyyy-MM-dd",
            width: 150,
        },
        {
            dataField: 'year',
            width: 120,
        },
        {
            dataField: 'month',
            width: 80,
        },
        {
            dataField: 'department',
            width: 180,
        },
        {
            dataField: 'title',
            width: 200,
        },
        {
            dataField: 'bu',
            width: 120,
        },
        {
            dataField: 'sector',
            width: 120,
        },
        {
            dataField: 'sapid',
            width: 120,
        },
        {
            caption: 'Originator',
            dataField: 'pencetuside_id',
            lookup: {
                dataSource: listOption('/list-employeeall','id','fullname'),  
                valueExpr: 'id',
                displayExpr: 'fullname',
            },
            width: 200,
        },
        {
            dataField: 'level',
            width: 120,
        },
        {
            dataField: 'anggota1',
            width: 120,
        },
        {
            caption: 'Level Anggota 1',
            dataField: 'anggota1level',
            width: 120,
        },
        {
            dataField: 'anggota2',
            width: 120,
        },
        {
            caption: 'Level Anggota 2',
            dataField: 'anggota2level',
            width: 120,
        },
        {
            dataField: 'objective',
            width: 120,
        },
        {
            caption: 'Category',
            dataField: 'ranking',
            width: 120,
        },
        {
            caption: 'Status JDI',
            dataField: 'status_jdi',
            width: 120,
        },
        {
            caption: 'Saving(IDR)',
            dataField: 'totalSaving',
            width: 180,
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
            },
            width: 150,
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
        // location.reload();
    }
}).dxDataGrid("instance");

$('#btnadd').on('click',function(){
    sendRequest(apiurl + "/"+modname, "POST", {requestStatus:0}).then(function(response){
        const reqid = response.data.id;
        const mode = 'add';
        const options = {"data": {"isMine": 1}};
        popup.option({
            contentTemplate: () => popupContentTemplate(reqid,mode,options),
        });
        popup.show();
    });
})

const accordionItems = [
    {
        ID: 7,
        Title: '<i class="far fa-newspaper"> To be filled by BCID Verifikator </i>',
        visible: true
    },
    {
        ID: 1,
        Title: '<i class="far fa-newspaper"> Form Data 1 </i>',
        visible: true
    },
    {
        ID: 5,
        Title: '<i class="far fa-newspaper"> Form Data 2 </i>',
        visible: true
    },
    {
        ID: 6,
        Title: '<i class="far fa-newspaper"> To be filled by Estate/ Dept CI Facilitator &  BCID Facilitator </i>',
        visible: false
    },
    {
        ID: 2,
        Title: '<i class="fas fa-file"> Supporting Document (Before & After / etc) </i>',
        visible: true
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

const popupContentTemplate = function (reqid,mode,options) {

    isMine = options.data.isMine;
    var isPendingOnMe = options.data.isPendingOnMe;
    isBCIDv = options.data.isBCIDv;

    var validationRules = [];
    var validationRules2 = [];

    popupid = reqid;

    console.log(mode)

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

    if(options.data.requestStatus == 3 || (isPendingOnMe && isBCIDv)) {
        updateVisibleById(7, true);
    } else {
        updateVisibleById(7, false);
    }

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
                var infoContent1 = $("<div id='infoContent1'>");
                if(data.ID == 7) {
                    if (mode == "approval" && (isPendingOnMe && isBCIDv)){
                        $("<span style='color:red;font-size:11pt'>").html('Silahkan lengkapi <b><i style="color:black;font-weight:bold" class="far fa-newspaper"> Form Data BCID </i></b> dan tekan tombol <b>Simpan</b> (<i style="color:black;font-weight:bold" class="fas fa-save"></i>) yang ada di pojok kanan atas tabel. lakukan aksi approval dan klik tombol <span style="color:black;font-weight:bold"><i class="bx bx-check-double label-icon"></i> Submit Submission</span>').appendTo(infoContent1);
                    }
                    let formData4 = $("<div id='formdata4'>").dxDataGrid({    
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
                        selection: { mode: "multiple" },
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
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 || developer || isBCIDv == 1 ? true : false),
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            {
                                dataField: 'noRegistration',
                                width: 200,
                                editorOptions: { 
                                    readOnly: true
                                },
                            },
                            {
                                dataField: "submitDate",
                                dataType: "date",
                                format: "dd-MM-yyyy", 
                                editorOptions: { 
                                    readOnly: true
                                }
                            },
                            {
                                dataField: 'objective',
                                lookup: {
                                    dataSource: [
                                        'Cost',
                                        'Environment',
                                        'Moral',
                                        'Productivity',
                                        'Quality',
                                        'Safety',
                                    ],
                                    searchEnabled: false
                                },
                            },
                            {
                                caption: 'Category',
                                dataField: 'ranking',
                                lookup: {
                                    dataSource: ['Bronze','Silver','Gold'],
                                    searchEnabled: false
                                },
                                setCellValue: function (rowData, value) {
                                    rowData.ranking = value;
                                    if (value === "Bronze") {
                                        rowData.savingInfo = null;
                                    }
                                },
                            },
                            {
                                caption: 'Status JDI',
                                dataField: 'status_jdi',
                                lookup: {
                                    dataSource: [
                                        'Register',
                                        'Awarded',
                                    ],
                                    searchEnabled: false
                                },
                            },
                            {
                                caption: 'Applicable Roll-Out Other Area/ Estate',
                                dataField: 'isRollout',
                                dataType: 'boolean'
                            },
                            {
                                dataField: 'savingInfo',
                                lookup: {
                                    dataSource: [
                                        'Saving < 1 mio IDR',
                                        'Saving > 1 mio IDR - 5 mio IDR',
                                        'Saving > 5 mio IDR - 10 mio IDR',
                                        'Saving > 10 mio IDR - 20 mio IDR',
                                        'Saving > 20 mio IDR',
                                    ],
                                    searchEnabled: false
                                },
                            },
                        ],
                        export: {
                            enabled: false,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGrid13 = e.component;
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
                                        dataGrid13.refresh();
                                    }
                                }
                            });
                        },
                        onEditorPreparing: function (e) {
                        },
                        onCellPrepared: function (e) {
                            if ( e.rowType == "data" && (e.column.index>1 && e.column.index<6)) {
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
                            console.log("Terjadi kesalahan saat memuat data (1.3):", e.error.message);
                    
                            // Memuat ulang DataGrid
                            dataGrid13.refresh();
                        }
                    }).appendTo(infoContent1)
                    return infoContent1

                } 
                var infoContent2 = $("<div id='infoContent2'>");
                if(data.ID == 1) {
                    if (mode == 'add' || mode == 'edit'){
                        $("<span style='color:red;font-size:11pt'>").html('Silahkan lengkapi <b><i style="color:black;font-weight:bold" class="far fa-newspaper"> Form Data </i></b> dan tekan tombol <b>Simpan</b> (<i style="color:black;font-weight:bold" class="fas fa-save"></i>) yang ada di pojok kanan atas tabel serta lampirkan <i style="color:black;font-weight:bold" class="fas fa-file"> Supporting Document </i> sebelum klik tombol <span style="color:black;font-weight:bold"><i class="bx bx-check-double label-icon"></i> Submit Submission</span>').appendTo(infoContent2);
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
                        selection: { mode: "multiple" },
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
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 || developer ? true : false),
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
                                dataField: 'title',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Pencetus Ide',
                                dataField: 'pencetuside_id',
                                lookup: {
                                    dataSource: listOption('/list-employeeall','id','fullname'),  
                                    valueExpr: 'id',
                                    displayExpr: function(item) {
                                        return item ? item.fullname + " (" + item.sapid + " | " + item.levels + ")" : "";
                                    }
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'BU',
                                dataField: 'bu',
                                lookup: {
                                    dataSource: [{bu:'IHM'}],
                                    valueExpr: 'bu',
                                    displayExpr: 'bu',
                                },
                                setCellValue: function (rowData, value) {
                                    rowData.bu = value;
                                    if (value === "IHM") {
                                        rowData.sector = "HO";
                                    } else if (value === "AHL") {
                                        rowData.sector = "HO";
                                    }
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
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
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Department',
                                dataField: 'department_id',
                                lookup: {
                                    dataSource: listOption('/list-department','id','DepartmentName'),  
                                    valueExpr: 'id',
                                    displayExpr: 'DepartmentName',
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Department Head',
                                dataField: 'depthead_id',
                                lookup: {
                                    dataSource: listOption('/list-employee','id','fullname'),  
                                    valueExpr: 'id',
                                    displayExpr: function(item) {
                                        return item ? item.fullname + " (" + item.sapid + ")" : "";
                                    }
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Anggota 1',
                                dataField: 'anggota1_id',
                                lookup: {
                                    dataSource: listOption('/list-employeeall','id','fullname'),  
                                    valueExpr: 'id',
                                    displayExpr: function(item) {
                                        return item ? item.fullname + " (" + item.sapid + ")" : "";
                                    }
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                }
                            },
                            {
                                caption: 'Anggota 2',
                                dataField: 'anggota2_id',
                                lookup: {
                                    dataSource: listOption('/list-employeeall','id','fullname'),  
                                    valueExpr: 'id',
                                    displayExpr: function(item) {
                                        return item ? item.fullname + " (" + item.sapid + ")" : "";
                                    }
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
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
                            if ((e.dataField == "pencetuside_id" || e.dataField == "depthead_id" || e.dataField == "anggota1_id" || e.dataField == "anggota2_id") && e.parentType == "dataRow") {
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
                                            columns: ["sapid","companycode","fullname","departmentname","levels"],
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
                                                console.log(keys)
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
                        onCellPrepared: function (e) {
                            if (e.column.index == 0 && e.rowType == "data") {
                                if(e.data.code === null) {
                                    $("#formdata").dxDataGrid('columnOption','code', 'visible', false);
                                } else {
                                    $("#formdata").dxDataGrid('columnOption','code', 'visible', true);
                                }
                            }
                            if ( e.rowType == "data" && (e.column.index>0 && e.column.index<7)) {
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
                            console.log("Terjadi kesalahan saat memuat data (1):", e.error.message);
                    
                            // Memuat ulang DataGrid
                            dataGrid1.refresh();
                        }
                    }).appendTo(infoContent2)
                    return infoContent2
                } 
                if(data.ID == 5) {
                      var tagData = [
                        { id: 1, name: "Defect" },
                        { id: 2, name: "Inventory" },
                        { id: 3, name: "Motion" },
                        { id: 4, name: "Over Processing" },
                        { id: 5, name: "Over Production" },
                        { id: 6, name: "Transportation" },
                        { id: 7, name: "Waiting" }
                    ];

                    let formData2 = $("<div id='formdata2'>").dxDataGrid({    
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
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 || developer || isBCIDv ? true : false),
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            {
                                caption: 'Hambatan/Tantangan/Ketidaksesuaian',
                                dataField: 'htk',
                                editorType: 'dxTextArea',
                                editorOptions: { 
                                    height: 50,
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Perbaikan Yang Dibuat',
                                dataField: 'perbaikan',
                                editorType: 'dxTextArea',
                                editorOptions: { 
                                    height: 50,
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Adakah ide ini mengurangi pemborosan ?',
                                dataField: "isNotWasteful",
                                dataType: "boolean",
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                setCellValue: function (rowData, value) {
                                    rowData.isNotWasteful = value;
                                    // console.log(value)
                                    if (value == true) {
                                        validationRules2.length = 0;
                                        validationRules2.push({
                                            type: "required", 
                                            message: "This item is required"
                                        });
                                    } else {
                                        validationRules2.length = 0;
                                    }
                                },
                            },
                            {
                                caption: '7 Waste',
                                dataField: 'sevenWaste',
                                editCellTemplate: function(container, options) {
                                    $("<div>").dxTagBox({
                                        dataSource: tagData,  // Array of possible tags
                                        value: options.value,
                                        showSelectionControls: true,
                                        // applyValueMode: 'useButtons',
                                        valueExpr: "name",
                                        displayExpr: "name",
                                        onValueChanged: function(e) {
                                            options.setValue(e.value);
                                        }
                                    }).appendTo(container);
                                },
                                validationRules: validationRules2,
                            },
                            {
                                caption: 'Alasan mengurangi pemborosan',
                                dataField: 'reasonNotWasteful',
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: validationRules2,
                            },
                            {
                                caption: 'is Saving ?',
                                dataField: "isSaving",
                                dataType: "boolean",
                                setCellValue: function (rowData, value) {
                                    rowData.isSaving = value;
                                    if (value == true) {
                                        validationRules.length = 0;
                                        validationRules.push({
                                            type: "required", 
                                            message: "This item is required"
                                        });
                                    } else {
                                        validationRules.length = 0;
                                    }
                                },
                                headerCellTemplate: function(container, options) {
                                    $(container).css({
                                        'background-color': '#4CAF50', // Green background
                                        'color': '#FFFFFF'  // White text
                                    }).append($('<span>').text(options.column.caption));
                                },
                                editorOptions: { 
                                    readOnly: ((isMine == 1) && mode == 'edit' || mode == 'add' ) || (mode == 'approval' && isBCIDv) ? false : true,
                                }
                            },
                            {
                                caption: 'Saving Calculation Formula: (Valid/ Original Document)',
                                dataField: 'savingFormula',
                                editorType: 'dxTextArea',
                                editorOptions: { 
                                    height: 50,
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: validationRules,
                            },
                            {
                                caption: 'Total Saving Realized per Year: (Actual saving for 3 months x 4)',
                                dataField: 'totalSaving',
                                dataType: 'string',
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: validationRules,
                            },
                            
                        ],
                        export: {
                            enabled: false,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGrid11 = e.component;
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
                                        dataGrid11.refresh();
                                    }
                                }
                            });
                        },
                        onEditorPreparing: function (e) {
                        },
                        onCellPrepared: function (e) {
                            if ( e.rowType == "data" && (e.column.index>=0 && e.column.index<2)) {
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
                            console.log("Terjadi kesalahan saat memuat data (1.1):", e.error.message);
                    
                            // Memuat ulang DataGrid
                            dataGrid11.refresh();
                        }
                    })

                    return formData2;
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
                                dataField: "remarks",
                                lookup: {
                                    dataSource: ['Before','After','Supporting Document'],
                                    searchEnabled: false
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

                    // var downloadButton = $("<button>")
                    //     .text("Download Proposal Guide Template")
                    //     .addClass("btn btn-danger btn-xs")
                    //     .appendTo(supporting);

                    // downloadButton.click(function() {
                    //     var fileUrl = "public/doc/Proposal Pengajuan System.pptx";
                    //     var link = document.createElement("a");
                    //     link.href = fileUrl;
                    //     link.download = "Proposal Pengajuan System.pptx";
                    //     link.click();
                    // });

                    return supporting;
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

    var btnSubmit = $('#btn-submit');
    btnSubmit.prop('disabled', true);
    var actionForm = (mode == 'approval') ? 'approval' : 'submission';

    if(mode == 'approval') {
        var valapprovalAction = $('input[name="approvalaction"]:checked').val(); // mengambil nilai dari radio button
        var valremarks = $('#remarks').val(); // mengambil nilai dari text area
        if (!valapprovalAction) {
            alert('Please select approval action.')
            btnSubmit.prop('disabled', false);
            return false;
        }
        else if (!valremarks) {
            alert('Please enter remarks.')
            btnSubmit.prop('disabled', false);
            return false;
        }
        
    }

    var valApprovalType = valapprovalAction == 3 ? 'Approved' : valapprovalAction == 2 ? 'Reworked' : valapprovalAction == 4 ? 'Rejected' : '';

    var result = confirm('Are you sure you want to send this submission ?');
    if (result) {
        sendRequest(apiurl + "/submissionrequest/"+reqid+"/"+modelclass, "POST", {
            requestStatus:1,
            action: actionForm,
            approvalAction: (valapprovalAction == null) ? 1 : parseInt(valapprovalAction),
            approvalType: valApprovalType,
            remarks: valremarks
        }).then(function(response){
            if(response.status == 'error') {
                btnSubmit.prop('disabled', false);
            } else {
                popup.hide();
            }
        });
    } else {
        btnSubmit.prop('disabled', false);
        alert('Cancelled.');
    }

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
            toolbar: 'bottom',
            location: 'after',
            options: {
            text: 'Close',
            onClick() {
                popup.hide();
            },
            },
        }]

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