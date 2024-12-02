var modnameindex = 'mmf30report';
var modname = 'mmf30request';
var modelclass = 'Mmf';
var popupmode;

function moveEditColumnToLeft(dataGrid) {
    dataGrid.columnOption("command:edit", { 
        visibleIndex: -1,
        width: 80 
    });
}

var dates = getStartAndEndDateOfMonth();

prType = [
    { id: 1, name: "Normal PR" },
    { id: 2, name: "Urgent PR" },
    { id: 3, name: "Minor Purchase" },
    { id: 4, name: "Request For Sourcing (RFS) Only" }
];
reqType = [
    { id: 1, name:"Stok Item"},
    { id: 2, name:"Services"},
    { id: 3, name:"Fixed Asset (Requires CAPEX approval)"},
    { id: 4, name:"Raw Material"},
    { id: 5, name:"Others"}
];


var dataGrid = $("#gridContainer").dxDataGrid({    
    dataSource: storereport(modnameindex,dates.startOfMonth,dates.endOfMonth),
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
                var reqid = options.data.id;
                var reqstatus = options.data.requestStatus;
                var mode = (reqstatus == 0 || reqstatus == 2 && (isMine == 1)) ? 'edit' : (reqstatus == 1 && ((isMine == 0 && isPendingOnMe == 1) || (isMine == 1 && isPendingOnMe == 1)) ? 'approval' : 'view') ;
                var arrColor = [
                    "btn-secondary",
                    (mode == 'approval' && reqstatus == 1) ? "btn-danger" : "btn-primary",
                    "btn-warning",
                    "btn-success",
                    "btn-danger",
                ];

                var viewIcon = (mode == 'approval' && reqstatus == 1) ? "fa-check" : "fa-search";
    
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
                            
                        var result = confirm('Are you sure you want to cancel this submission ?');

                        if (result) {
                            sendRequest(apiurl + "/submissionrequest/"+reqid+"/"+modelclass, "POST", {
                                requestStatus:0,
                                action:'submission',
                                approvalAction: 0
                            }).then(function(response){
                                if(response.status != 'error') {
                                    dataGrid.refresh();
                                }
                            });
                        } else {
                            alert('Cancelled.');
                        }
    
                    }).appendTo(container); 
                }
            
            }
        },
        { 
            caption: 'Created At',
			dataField: "created_at",
            dataType: "date",
            width: 180
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
            caption: 'PR Type',
			dataField: "PRType",
            width: 200,
            lookup: { 
                dataSource: prType,  
                valueExpr: 'id',
                displayExpr: 'name',
            },
        },
        { 
            caption: 'Requisition Material',
			dataField: "RequisitionType",
            width: 200,
            lookup: { 
                dataSource: reqType,  
                valueExpr: 'id',
                displayExpr: 'name',
            },
        },
        { 
			dataField: "Reason",
            width: 180
        },
        { 
            caption: 'Creator Name',
			dataField: "user.fullname",
            width: 180
        },
        { 
            caption: 'Employee Name',
			dataField: "employee_name",
            width: 180
        },
        
        {
            dataField: 'requestStatus',
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
                var value = options.value;
                var origin = window.location.origin;
                if (value && value.includes('doc')) {
                    var baseUrl = origin + '/oasys/';
                } else {
                    var baseUrl = origin + '/devportal/';
                }
                var fullUrl = baseUrl + value;
                if ((value!="") && (value)){
                    $("<div />").dxButton({
                        icon: 'download',
                        type: "success",
                        text: "Download",
                        onClick: function (e) {
                            window.open(fullUrl, '_blank');

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

        let endDateBox;

        e.toolbarOptions.items.unshift(
            {
                location: "after",
                widget: "dxDateBox",
                options: {
                    hint: "Start Date",
                    displayFormat: "dd/MM/yyyy",
                    value: dates.startOfMonth,
                    onValueChanged: function(e) {
                        startDate = e.value;
                        endDate = null; // Reset end date when start date changes
                        endDateBox.option("value", endDate); // Clear end date box
                        filterData();
                    }
                }
            },
            {
                location: "after",
                widget: "dxDateBox",
                options: {
                    hint: "End Date",
                    displayFormat: "dd/MM/yyyy",
                    value: dates.endOfMonth,
                    onValueChanged: function(e) {
                        endDate = e.value;
                        filterData();
                    },
                    onInitialized: function(e) {
                        endDateBox = e.component; // Save reference to end date box
                    }
                }
            },
            {						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        dataGrid.refresh();
                    }
                }
            }
        )
    },
    onDataErrorOccurred: function(e) {
        // Menampilkan pesan kesalahan
        console.log("Terjadi kesalahan saat memuat data (0):", e.error.message);

        // Memuat ulang Page
        location.reload();
    }
}).dxDataGrid("instance");

var startDate;
var endDate;

function filterData() {
    var filter = [];

    var formattedStartDate = formatDate(startDate);
    var formattedEndDate = formatDate(endDate);

    if (startDate && endDate) {

        if (new Date(startDate) <= new Date(endDate)) {
            filter.push(["created_at", ">=", formattedStartDate]);
            filter.push(["created_at", "<=", formattedEndDate]);
        } else {
            DevExpress.ui.dialog.alert("startDate cannot be later than endDate.", "error")
            return;
        }
    }

    if(filter.length == 2) {
        console.log('run')
        dataGrid.option("dataSource", storereport(modnameindex, formattedStartDate, formattedEndDate));
        dataGrid.refresh();
    }

}


const accordionItems = [
    {
        ID: 1,
        Title: '<i class="far fa-newspaper"> Form Data</i>',
        visible: true
    },
    {
        ID: 5,
        Title: '',
        visible: true
    },
    {
        ID: 7,
        Title: '<i class="fas fa-users"> Assignment To </i>',
        visible: false
    },
    {
        ID: 6,
        Title: '<i class="fas fa-list-ul"> Details </i>',
        visible: true
    },
    {
        ID: 2,
        Title: '<i class="fas fa-file"> Supporting Document </i>',
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

const popupContentTemplate = function (reqid,mode,options) {
    // console.log('id detail 30 : '+options.data.detail30.id);
    isMine = options.data.isMine;
    var isPendingOnMe = options.data.isPendingOnMe;
    isProcHead = options.data.isProcHead;
    isBuyer = options.data.isBuyer;
    // var detail30id = options.data.detail30.id;

    var validationRules = [];
    var visibleRulesReqType = false;
    var visibleRulesPRType = false;

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

    if(admin == 1 || (isPendingOnMe && isProcHead)) {
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
                var infoContent2 = $("<div id='infoContent2'>");
                if(data.ID == 1) {
                    if (mode == 'add' || mode == 'edit'){
                        $("<span style='color:red; font-size:11pt; text-shadow: -1px -1px 0 #fff, 1px -1px 0 #fff, -1px 1px 0 #fff, 1px 1px 0 #fff;'>").html(
                            'Silahkan lengkapi <b><i style="color:black; font-weight:bold;" class="far fa-newspaper"> Form Data </i></b> sebelum klik tombol <span style="color:black; font-weight:bold;"><i class="bx bx-check-double label-icon"></i> Submit Submission</span><br> Tekan <span style="color:red; font-weight:bold;">ESC</span> Untuk Cancel Edit'
                        ).appendTo(infoContent2);
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
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
                                // validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'PR Type',
                                dataField: 'detail30.PRType',
                                lookup: { 
                                    dataSource: prType,  
                                    valueExpr: 'id',
                                    displayExpr: 'name',
                                },
                                setCellValue: function (rowData, value) {
                                    rowData.detail30 = rowData.detail30 || {};
                                    rowData.detail30.PRType = value;

                                    console.log('Selected PRType:', value);

                                    // Update visibility based on the selected value
                                    let visibleRulesPRType = (value === 3);

                                    // Update the visibility of the "Others Detail" columns
                                    dataGrid1.columnOption('detail30.SupplierName', 'visible', visibleRulesPRType);
                                    dataGrid1.columnOption('detail30.SupplierAddress', 'visible', visibleRulesPRType);
                                    dataGrid1.columnOption('detail30.SupplierEmailFax', 'visible', visibleRulesPRType);
                                    dataGrid1.columnOption('detail30.ContractNo', 'visible', visibleRulesPRType);

                                    // Clear and apply validation rules if needed
                                    validationRules.length = 0; // Clear existing rules
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Supplier Name',
                                dataField: 'detail30.SupplierName',
                                visible: visibleRulesPRType,
                            },
                            {
                                caption: 'Supplier Address',
                                dataField: 'detail30.SupplierAddress',
                                visible: visibleRulesPRType,
                            },
                            {
                                caption: 'Email/Fax',
                                dataField: 'detail30.SupplierEmailFax',
                                visible: visibleRulesPRType,
                            },
                            {
                                caption: 'Contract No',
                                dataField: 'detail30.ContractNo',
                                visible: visibleRulesPRType,
                            },
                            {
                                caption: 'Requisition Material',
                                dataField: 'detail30.RequisitionType',
                                lookup: { 
                                    dataSource: reqType,  
                                    valueExpr: 'id',
                                    displayExpr: 'name',
                                },
                                setCellValue: function (rowData, value) {
                                    rowData.detail30 = rowData.detail30 || {};
                                    rowData.detail30.RequisitionType = value;

                                    if (value == 5) {
                                        validationRules.length = 0;
                                        validationRules.push({
                                            type: "required", 
                                            message: "This item is required"
                                        });
                                        visibleRulesReqType = true;
                                    } else {
                                        validationRules.length = 0;
                                        visibleRulesReqType = false;
                                    }
                                    // Update the visibility of the "Others Detail" column and refresh the grid
                                    dataGrid1.columnOption('detail30.RequisitionOther', 'visible', visibleRulesReqType);
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Others Detail',
                                dataField: 'detail30.RequisitionOther',
                                visible: visibleRulesReqType,
                                validationRules: validationRules,
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
                            updateVisibility(e.component);
                        },
                        onEditCanceled: function(e) {
                            updateVisibility(e.component);
                            dataGrid1.refresh();
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
                            if ( e.rowType == "data" && (e.column.index==2 || e.column.index==7)) {
                                if (e.value === "" || e.value === null || e.value === undefined || /^\s*$/.test(e.value)) {
                                    e.cellElement.css({
                                        "backgroundColor": "#ffe6e6",
                                        "border": "0.5px solid #f56e6e"
                                    })
                                }
                            }
                            if (e.column.index == 2 && e.rowType == "data") {
                                if(e.value === 3) {
                                    dataGrid1.columnOption('detail30.SupplierName', 'visible', true);
                                    dataGrid1.columnOption('detail30.SupplierAddress', 'visible', true);
                                    dataGrid1.columnOption('detail30.SupplierEmailFax', 'visible', true);
                                    dataGrid1.columnOption('detail30.ContractNo', 'visible', true);
                                }
                            }
                            if (e.column.index == 7 && e.rowType == "data") {
                                if(e.value === 5) {
                                    dataGrid1.columnOption('detail30.RequisitionOther', 'visible', true);
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

                    function updateVisibility(dataGrid) {
                        var rows = dataGrid.getVisibleRows();
                    
                        // Reset visibility rules
                        visibleRulesReqType = false;
                        visibleRulesPRType = false;
                    
                        rows.forEach(function(row) {
                            if (row.data.detail30 && row.data.detail30.RequisitionType === 5) {
                                visibleRulesReqType = true;
                            }
                            if (row.data.detail30 && row.data.detail30.PRType === 3) {
                                visibleRulesPRType = true;
                            }
                        });
                    
                        // Update the visibility of the columns and refresh the grid
                        dataGrid.columnOption('detail30.RequisitionOther', 'visible', visibleRulesReqType);
                        dataGrid.columnOption('detail30.SupplierName', 'visible', visibleRulesPRType);
                        dataGrid.columnOption('detail30.SupplierAddress', 'visible', visibleRulesPRType);
                        dataGrid.columnOption('detail30.SupplierEmailFax', 'visible', visibleRulesPRType);
                        dataGrid.columnOption('detail30.ContractNo', 'visible', visibleRulesPRType);
                    }

                    return infoContent2
                } 
                if(data.ID == 5) {
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            {
                                caption: 'Cost Code',
                                dataField: 'detail30.CostCode',
                            },
                            {
                                caption: 'Reason for requisition/purchase',
                                dataField: 'detail30.Reason',
                                editorType: 'dxTextArea',
                                editorOptions: { 
                                    height: 50,
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Remarks',
                                dataField: 'detail30.RemarksU',
                                editorType: 'dxTextArea',
                                editorOptions: { 
                                    height: 50,
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Deliver To',
                                dataField: 'detail30.DeliverTo',
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
                            if ( e.rowType == "data" && (e.column.index>=1 && e.column.index<3)) {
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
                            console.log("Terjadi kesalahan saat memuat data (1.1.2):", e.error.message);
                    
                            // Memuat ulang DataGrid
                            dataGrid11.refresh();
                        }
                    })

                    return formData2;
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
                            allowAdding: (admin == 1 || isProcHead == 1) ? true : false,
                            allowUpdating: (admin == 1 || isProcHead == 1) ? true : false,
                            allowDeleting: (admin == 1 || isProcHead == 1) ? true : false,
                        },
                        paging: { enabled: true, pageSize: 10 },
                        columns: [
                            {
                                caption: "Buyer Name",
                                dataField: "employee_id",
                                lookup: {
                                    dataSource: listOption('/list-buyer','id','fullname'),  
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
                        onRowInserting: function(e) {
                            // var dataGrid = e.component;
                            // var rowCount = dataGrid.getDataSource().items().length;
                
                            // if (rowCount >= 1) {
                            //     e.cancel = true; // Batalkan penambahan baris baru
                            //     DevExpress.ui.dialog.alert("Hanya satu baris yang diperbolehkan.", "Peringatan");
                            // }
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
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
                                            columns: ["fullname"],
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
                            console.log("Terjadi kesalahan saat memuat data (7):", e.error.message);
                    
                            // Memuat ulang DataGrid
                            dataGridAssignmentto.refresh();
                        }
                    })
                }
                // else if(data.ID == 6) {
                //     return formData = $("<div id='formdetail'>").dxDataGrid({    
                //         dataSource: storewithmodule('mmf30detail',modelclass,detail30id),
                //         allowColumnReordering: true,
                //         allowColumnResizing: true,
                //         columnsAutoWidth: true,
                //         rowAlternationEnabled: true,
                //         wordWrapEnabled: true,
                //         showBorders: true,
                //         showColumnLines:true,
                //         filterRow: { visible: false },
                //         filterPanel: { visible: false },
                //         headerFilter: { visible: false },
                //         searchPanel: {
                //             visible: true,
                //             width: 240,
                //             placeholder: 'Search...',
                //         },
                //         sorting: {
                //             mode: "none" // or "multiple" | "none"
                //         },
                //         editing: {
                //             useIcons:true,
                //             mode: "cell",
                //             allowAdding: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                //             allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 || (mode == 'approval' && isBuyer) ? true : false),
                //             allowDeleting: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                //         },
                //         scrolling: {
                //             rowRenderingMode: 'virtual',
                //         },
                //         paging: {
                //             pageSize: 15,
                //         },
                //         pager: {
                //             visible: true,
                //             allowedPageSizes: [5, 15, 'all'],
                //             showPageSizeSelector: true,
                //             showInfo: true,
                //             showNavigationButtons: true,
                //         },
                //         columns: [
                //             {
                //                 dataField: 'MaterialCode',
                //                 dataType: "string",
                //                 editorOptions: { 
                //                     // readOnly: ((mode == 'approval') ? true : false),
                //                     readOnly: (mode == 'approval' && isBuyer) || (admin == 1) ? false : (isMine == 1) ? false : true,
                //                 }
                //             },
                //             {
                //                 dataField: 'MaterialDescr',
                //                 dataType: "string",
                //                 editorOptions: { 
                //                     // readOnly: ((mode == 'approval') ? true : false),
                //                     readOnly: (mode == 'approval' && isBuyer) || (admin == 1) ? false : (isMine == 1) ? false : true,
                //                 }
                //             },
                //             {
                //                 dataField: 'PartNumber',
                //                 dataType: 'string',
                //                 editorOptions: { 
                //                     // readOnly: ((mode == 'approval') ? true : false),
                //                     readOnly: (mode == 'approval' && isBuyer) || (admin == 1) ? false : (isMine == 1) ? false : true,
                //                 }
                //             },
                //             {
                //                 caption: 'Brand/Manufacturer',
                //                 dataField: 'BrandManufacturer',
                //                 editorOptions: { 
                //                     // readOnly: ((mode == 'approval') ? true : false),
                //                     readOnly: (mode == 'approval' && isBuyer) || (admin == 1) ? false : (isMine == 1) ? false : true,

                //                 }
                //             },
                //             {
                //                 dataField: 'Qty',
                //                 dataType: "number",
                //                 editorOptions: { 
                //                     // readOnly: ((mode == 'approval') ? true : false),
                //                     readOnly: (mode == 'approval' && isBuyer) || (admin == 1) ? false : (isMine == 1) ? false : true,

                //                 }
                //             },
                //             {
                //                 caption: 'Unit',
                //                 dataField: 'Unit',
                //                 lookup: {
                //                     dataSource: listOption('/list-unit','id','nama'),  
                //                     valueExpr: 'nama',
                //                     displayExpr: 'nama'
                //                 },
                //                 editorOptions: { 
                //                     // readOnly: ((mode == 'approval') ? true : false),
                //                     readOnly: (mode == 'approval' && isBuyer) || (admin == 1) ? false : (isMine == 1) ? false : true,
                //                 }
                //             },
                //             {
                //                 caption: 'Currency',
                //                 dataField: 'Currency',
                //                 lookup: {
                //                     dataSource: listOption('/list-currency','id','nama'),  
                //                     valueExpr: 'nama',
                //                     displayExpr: 'nama'
                //                 },
                //                 editorOptions: { 
                //                     readOnly: (mode == 'approval' && isBuyer) || (admin == 1) ? false : true,
                //                 }
                //             },
                //             {
                //                 caption: "Unit Price",
                //                 dataField:'UnitPrice',
                //                 dataType: "number",
                //                 format: "fixedPoint",
                //                 editorOptions: {
                //                     format: "fixedPoint",
                //                     readOnly: (mode == 'approval' && isBuyer) || (admin == 1) ? false : true,
                //                 }
                //             },
                //             {
                //                 caption: "Extended Price",
                //                 dataField:'ExtendedPrice',
                //                 dataType: "number",
                //                 format: "fixedPoint",
                //                 editorOptions: {
                //                     format: "fixedPoint",
                //                     // readOnly: (mode == 'approval' && isBuyer) || (admin == 1) ? false : true,
                //                     readOnly: true,
                //                 }
                //             },
                //             {
                //                 caption: "Remarks",
                //                 dataField:'Remarks',
                //                 dataType: "string",
                //                 editorOptions: { 
                //                     readOnly: (mode == 'approval' && isBuyer) || (admin == 1) ? false : (isMine == 1) ? false : true,
                //                 }
                //             },
                //         ],
                //         export: {
                //             enabled: false,
                //             fileName: modname,
                //             excelFilterEnabled: true,
                //             allowExportSelectedData: true
                //         },
                //         onInitialized: function(e) {
                //             dataGriddetail = e.component;
                //         },
                //         onContentReady: function(e){
                //             moveEditColumnToLeft(e.component);
                //                 // e.component.columnOption('Action', 'visible', true);
                //         },
                //         onToolbarPreparing: function(e) {
                //             e.toolbarOptions.items.unshift({						
                //                 location: "after",
                //                 widget: "dxButton",
                //                 options: {
                //                     hint: "Refresh Data",
                //                     icon: "refresh",
                //                     onClick: function() {
                //                         dataGriddetail.refresh();
                //                     }
                //                 }
                //             });
                //         },
                //         onEditorPrepared: function (e) {
                //         },
                //         onEditorPreparing: function (e) {
                //             if ((e.dataField == "Unit") && e.parentType == "dataRow") {
                //                 e.editorName = "dxDropDownBox";                
                //                 e.editorOptions.dropDownOptions = {                
                //                     height: 500,
                //                     width: 600,
                //                     valueExpr: 'nama',
                //                     displayExpr: 'nama'
                //                 };
                //                 e.editorOptions.contentTemplate = function (args, container) {
                    
                //                     var value = args.component.option("value"),
                //                         $dataGrid = $("<div>").dxDataGrid({
                //                             width: '100%',
                //                             dataSource: args.component.option("dataSource"),
                //                             keyExpr: "nama",
                //                             columns: ["nama","detail"],
                //                             scrolling: {
                //                                 mode: "virtual"
                //                             },
                //                             pager: {
                //                                 visible: false,
                //                                 showInfo: true,
                //                             },
                //                             hoverStateEnabled: true,
                //                             paging: { enabled: true, pageSize: 10 },
                //                             filterRow: { visible: true },
                //                             height: '90%',
                //                             showRowLines: true,
                //                             showBorders: true,
                //                             selection: { mode: "single" },
                //                             selectedRowKeys: [value],
                //                             focusedRowEnabled: true,
                //                             focusedRowKey: value,
                //                             searchPanel: {
                //                                 visible: true,
                //                                 width: 265,
                //                                 placeholder: "Search..."
                //                             },
                //                             onSelectionChanged: function (selectedItems) {
                //                                 const keys = selectedItems.selectedRowKeys;
                //                                 const datas = selectedItems.selectedRowsData;
                //                                 // console.log(datas)
                //                                 const hasSelection = datas.length;
                //                                 // console.log(hasSelection)

                //                                 // args.component.option('value', hasSelection > 0 ? datas[0]['nama'] : null);
                //                                 if(hasSelection !== 0) {
                //                                     args.component.option('value', datas[0]['nama']);
                //                                     args.component.close();
                //                                 }
                //                             }
                //                         });
                //                     // console.log(value)
                    
                //                     var dataGrid = $dataGrid.dxDataGrid("instance");
                    
                //                     args.component.on("valueChanged", function (args) {
                //                         var value = args.value;
                    
                //                         dataGrid.selectRows(value, false);
                //                     });
                //                     container.append($dataGrid);
                //                     $("<div>").dxButton({
                //                         text: "Close",
                    
                //                         onClick: function (ev) {
                //                             args.component.close();
                //                         }
                //                     }).css({ float: "right", marginTop: "10px" }).appendTo(container);
                //                     return container;
                    
                //                 };
                //             }
                //         },
                //         onCellPrepared: function (e) {
                //         },
                //         onDataErrorOccurred: function(e) {
                //             // Menampilkan pesan kesalahan
                //             console.log("Terjadi kesalahan saat memuat data (6):", e.error.message);
                    
                //             // Memuat ulang DataGrid
                //             dataGriddetail.refresh();
                //         }
                //     })
                // }
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
                                encodeHtml: false,
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
    var value = options.value ? options.value.trim() : '';
    var origin = window.location.origin;
    if (value && value.includes('upload')) {
        var baseUrl = origin + '/oasys/';
    } else {
        var baseUrl = origin + '/devportal/public/upload/';
    }
    var fullUrl = baseUrl + value;
    container.append('<a href="'+fullUrl+'" target="_blank"><img src="public/assets/images/showfile.png" height="50" width="70"></a>');
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