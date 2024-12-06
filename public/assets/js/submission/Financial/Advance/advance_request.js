var modname = 'advancerequest';
var modelclass = 'Financial';
var modelsubclass = 'Advance';
var popupmode;

function moveEditColumnToLeft(dataGrid) {
    dataGrid.columnOption("command:edit", { 
        visibleIndex: -1,
        width: 80 
    });
}

AdvanceForm = [
    { id: 1, name: "HR Related" },
    { id: 2, name: "Ops Related" }
];
OpsCategory = [
    { id: 1, name:"General"},
    { id: 4, name:"Direct Purchased"},
    { id: 5, name:"SSL"}
];


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
            caption: 'Form',
			dataField: "detail_advance.AdvanceForm",
            width: 180,
            lookup: { 
                dataSource: AdvanceForm,  
                valueExpr: 'id',
                displayExpr: 'name',
            },
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

// $('#HistoryButton').on('click',function(){
//     var dataGridhistory = $("#historyMMF30").dxDataGrid({    
//         dataSource: store('mmf30historyApp'),
//         allowColumnReordering: true,
//         allowColumnResizing: true,
//         columnHidingEnabled: true,
//         rowAlternationEnabled: false,
//         wordWrapEnabled: true,
//         autoExpandAll: true,
//         showBorders: true,
//         filterRow: { visible: true },
//         filterPanel: { visible: true },
//         headerFilter: { visible: true },
//         searchPanel: {
//             visible: true,
//             width: 240,
//             placeholder: 'Search...',
//         },
//         editing: {
//             useIcons:true,
//             mode: "popup",
//             allowAdding: false,
//             allowUpdating: false,
//             allowDeleting: true,
//         },
//         scrolling: {
//             mode: "virtual"
//         },
//         pager: {
//             visible: false,
//             showInfo: true,
//         },
//         columns: [
//             {
//                 caption: 'Action',
//                 width: 140,
//                 cellTemplate: function(container, options) {

//                     var isMine = options.data.isMine;
//                     var isPendingOnMe = options.data.isPendingOnMe;
//                     var reqid = options.data.id;
//                     var reqstatus = options.data.requestStatus;
//                     var mode = (reqstatus == 0 || reqstatus == 2 && (isMine == 1)) ? 'edit' : (reqstatus == 1 && ((isMine == 0 && isPendingOnMe == 1) || (isMine == 1 && isPendingOnMe == 1)) ? 'approval' : 'view') ;
//                     var arrColor = [
//                         "btn-secondary",
//                         (mode == 'approval' && reqstatus == 1) ? "btn-danger" : "btn-primary",
//                         "btn-warning",
//                         "btn-success",
//                         "btn-danger",
//                     ];

//                     var viewIcon = (mode == 'approval' && reqstatus == 1) ? "fa-check" : "fa-search";
        
//                     $('<button class="btn '+arrColor[reqstatus]+'" id="btnreqid'+reqid+'"><i class="fa '+viewIcon+'"></i></button>').on('dxclick', function(evt) {
//                         evt.stopPropagation();
                    
//                                 popup.option({
//                                     contentTemplate: () => popupContentTemplate(reqid,mode,options),
//                                 });
//                                 popup.show();

//                     }).appendTo(container);
//                     if((reqstatus == 1 || reqstatus == 2) && ((isMine == 1 && (isPendingOnMe == 0 || isPendingOnMe == null)))) {
//                         $('<button class="btn btn-danger" id="btnreqid'+reqid+'" style="margin-left: 3px;">Cancel</button>').on('dxclick', function(evt) {
//                             evt.stopPropagation();
                                
//                             var result = confirm('Are you sure you want to cancel this submission ?');

//                             if (result) {
//                                 sendRequest(apiurl + "/submissionrequest/"+reqid+"/"+modelclass, "POST", {
//                                     requestStatus:0,
//                                     action:'submission',
//                                     approvalAction: 0
//                                 }).then(function(response){
//                                     if(response.status != 'error') {
//                                         dataGrid.refresh();
//                                     }
//                                 });
//                             } else {
//                                 alert('Cancelled.');
//                             }
        
//                         }).appendTo(container); 
//                     }
                
//                 }
//             },
//             {
//                 caption: "Code",
//                 dataField: 'code',
//                 width: 180,
//             },
//             { 
//                 caption: 'BU',
//                 dataField: "bu",
//                 width: 80
//             },
//             { 
//                 caption: 'PR Type',
//                 dataField: "PRType",
//                 width: 200,
//                 lookup: { 
//                     dataSource: prType,  
//                     valueExpr: 'id',
//                     displayExpr: 'name',
//                 },
//             },
//             { 
//                 caption: 'Requisition Material',
//                 dataField: "RequisitionType",
//                 width: 200,
//                 lookup: { 
//                     dataSource: reqType,  
//                     valueExpr: 'id',
//                     displayExpr: 'name',
//                 },
//             },
//             { 
//                 dataField: "Reason",
//                 width: 180
//             },
//             { 
//                 caption: 'Creator Name',
//                 dataField: "user.fullname",
//                 width: 180
//             },
//             { 
//                 caption: 'Employee Name',
//                 dataField: "employee_name",
//                 width: 180
//             },
//             {
//                 dataField: 'requestStatus',
//                 encodeHtml: false,
//                 allowFiltering: false,
//                 allowHeaderFiltering: true,
//                 customizeText: function (e) {
//                     var arrText = [
//                         "<span class='btn btn-secondary btn-xs btn-status'>Draft</span>",
//                         "<span class='btn btn-primary btn-xs btn-status'>Waiting Approval</span>",
//                         "<span class='btn btn-warning btn-xs btn-status'>Rework</span>",
//                         "<span class='btn btn-success btn-xs btn-status'>Approved</span>",
//                         "<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
//                     ];
//                     return arrText[e.value];
//                 },
//             },
//             {
//                 dataField: "approveddoc",
//                 caption:"Approval Doc",
//                 allowFiltering: false,
//                 allowSorting: false,
//                 formItem: { visible: false},
//                 cellTemplate: function (container, options) {
//                     var value = options.value;
//                     var origin = window.location.origin;
//                     if (value && value.includes('doc')) {
//                         var baseUrl = origin + '/oasys/';
//                     } else {
//                         var baseUrl = origin + '/devportal/';
//                     }
//                     var fullUrl = baseUrl + value;
//                     if ((value!="") && (value)){
//                         $("<div />").dxButton({
//                             icon: 'download',
//                             type: "success",
//                             text: "Download",
//                             onClick: function (e) {
//                                 window.open(fullUrl, '_blank');

//                             }
//                         }).appendTo(container);
//                     }
//                 }
//             },
        
//         ],
//         columnChooser: {
//         enabled: true,
//         },
//         export: {
//             enabled: true,
//             fileName: modname,
//             excelFilterEnabled: true,
//             allowExportSelectedData: true
//         },
//         onContentReady: function(e){
//             moveEditColumnToLeft(e.component);
//             runpopup();
//         },
//         onCellPrepared: function (e) {
//             if (e.rowType == "data") {
//                 if(e.data.isParent === 1) {
//                     e.cellElement.css('background','rgba(128, 128, 0,0.1)')
//                 }
//             }
//         },
//         onToolbarPreparing: function(e) {
//             dataGridhistory = e.component;

//             e.toolbarOptions.items.unshift({						
//                 location: "after",
//                 widget: "dxButton",
//                 options: {
//                     hint: "Refresh Data",
//                     icon: "refresh",
//                     onClick: function() {
//                         dataGridhistory.refresh();
//                     }
//                 }
//             })
//         },
//         onDataErrorOccurred: function(e) {
//             // Menampilkan pesan kesalahan
//             console.log("Terjadi kesalahan saat memuat data (0):", e.error.message);

//             // Memuat ulang Page
//             dataGridhistory.refresh();
//         }
//     }).dxDataGrid("instance");
// })

$('#btnadd').on('click',function(){
    showLoadingScreen();
    sendRequest(apiurl + "/"+modname, "POST", {requestStatus:0}).then(function(response){
        const reqid = response.data.id;
        const mode = 'add';
        const options = {
            "data": 
            {
                "isMine": 1,
                "detail_advance": 
                {
                    "id":response.data.detail_advance.id
                }
            }
        };
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
    // console.log('id detail : '+options.data.detail_advance.id);
    isMine = options.data.isMine;
    var isPendingOnMe = options.data.isPendingOnMe;
    isProcHead = options.data.isProcHead;
    isBuyer = options.data.isBuyer;
    var detailid = options.data.detail_advance.id;

    var validationRules = [];
    var visibleRulesAdvForm = false;

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

                    $("<div id='formdata'>").dxDataGrid({    
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
                            },
                            {
                                caption: 'Form',
                                dataField: "detail_advance.AdvanceForm",
                                width: 180,
                                lookup: { 
                                    dataSource: AdvanceForm,  
                                    valueExpr: 'id',
                                    displayExpr: 'name',
                                },
                                setCellValue: function (rowData, value) {
                                    rowData.detail_advance = rowData.detail_advance || {};
                                    rowData.detail_advance.AdvanceForm = value;

                                    if (value == 2) {
                                        validationRules.length = 0;
                                        validationRules.push({
                                            type: "required", 
                                            message: "This item is required"
                                        });
                                        visibleRulesAdvForm = true;
                                    } else {
                                        validationRules.length = 0;
                                        visibleRulesAdvForm = false;
                                    }
                                    // Update the visibility of the "Others Detail" column and refresh the grid
                                    dataGrid1.columnOption('detail_advance.OpsCategory', 'visible', visibleRulesAdvForm);
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Category OPS',
                                dataField: 'detail_advance.OpsCategory',
                                lookup: { 
                                    dataSource: OpsCategory,  
                                    valueExpr: 'id',
                                    displayExpr: 'name',
                                },
                                visible: visibleRulesAdvForm,
                                validationRules: validationRules,
                            },
                            {
                                caption: 'Beneficiary',
                                dataField: 'detail_advance.Beneficiary',
                            },
                            {
                                caption: 'AccountName',
                                dataField: 'detail_advance.AccountName',
                            },
                            {
                                caption: 'Bank',
                                dataField: 'detail_advance.Bank',
                            },
                            {
                                caption: 'Bank Account No',
                                dataField: 'detail_advance.AccountNumber',
                            },
                            {
                                caption: 'Expected Date',
                                dataField: "detail_advance.ExpectedDate",
                                dataType: "date",
                                format: "dd-MM-yyyy", 
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Due Date',
                                dataField: "detail_advance.DueDate",
                                dataType: "date",
                                format: "dd-MM-yyyy", 
                                validationRules: [{ type: "required" }]
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
                                        dataGrid11.refresh();
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
                            if ( e.rowType == "data" && (e.column.index==2 || e.column.index>=8 && e.column.index<=9)) {
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
                                caption: 'Remarks',
                                dataField: 'detail_advance.Remarks',
                                editorType: 'dxTextArea',
                                editorOptions: { 
                                    height: 50,
                                    readOnly: (mode == 'approval') ? true : false
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
                            dataGrid11 = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onInitNewRow : function(e) {
                        },
                        onToolbarPreparing: function(e) {
                            // e.toolbarOptions.items.unshift({						
                            //     location: "after",
                            //     widget: "dxButton",
                            //     options: {
                            //         hint: "Refresh Data",
                            //         icon: "refresh",
                            //         onClick: function() {
                            //             dataGrid11.refresh();
                            //         }
                            //     }
                            // });
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
                else if(data.ID == 6) {
                    return formData = $("<div id='formdetail'>").dxDataGrid({    
                        dataSource: storewithmodule('advancedetail',modelsubclass,detailid),
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
                            mode: "cell",
                            allowAdding: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowDeleting: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                        },
                        scrolling: {
                            rowRenderingMode: 'virtual',
                        },
                        paging: {
                            pageSize: 15,
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
                                dataField: 'Description',
                                dataType: "string",
                            },
                            {
                                dataField: 'AccountCode',
                                dataType: "string",
                            },
                            {
                                caption: "Amount",
                                dataField:'Amount',
                                dataType: "number",
                                format: "fixedPoint",
                                editorOptions: {
                                    format: "fixedPoint",
                                }
                            },
                            {
                                caption: "Remarks",
                                dataField:'Remarks',
                                dataType: "string",
                            },
                        ],
                        summary: {
                            recalculateWhileEditing: true,
                            totalItems: [{
                                column: "Amount",
                                summaryType: "sum",
                                valueFormat: "fixedPoint",
                                displayFormat: "Total: {0}",
                            }]
                        },
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
                        },
                        onCellPrepared: function (e) {
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
                        dataSource: storewithmodule('attachmentrequest',modelsubclass,reqid),
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
                        dataSource: storewithmodule('approverlistrequest',modelsubclass,reqid),
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
                                    dataSource: listOption('/list-approver/'+modelsubclass,'id','fullname'),  
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
                        dataSource: storewithmodule('approverlisthistory',modelsubclass,reqid),
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
    var valapprovalAction = $('input[name="approvalaction"]:checked').val(); // mengambil nilai dari radio button
    var valremarks = $('#remarks').val(); // mengambil nilai dari text area
    
    // if(mode == 'approval' && isProcHead == 1 && valapprovalAction == 3) {
    //     var fieldsToCheckGrid = [
    //         { field: 'Buyer', name: 'Buyer' },
    //     ]
    // } else {
    //     if(mode !== 'approval') {
            var fieldsToCheckGrid = [
                { field: 'AdvanceForm', name: 'Form Type' },
                { field: 'ExpectedDate', name: 'Expected Date' },
                { field: 'DueDate', name: 'Due Date' },
            ];
    //     }
    // }

    sendRequest(apiurl + "/submissioncheckfields/"+reqid+"/"+modelsubclass, "POST", {
        fieldsToCheckGrid
    }).then(function(response){
        if(response.status !== 'error') {

            btnSubmit.prop('disabled', true);

            var actionForm = (mode == 'approval') ? 'approval' : 'submission';

            if(mode == 'approval') {
                
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

            confirmAndSendSubmission(reqid, modelclass, actionForm, valapprovalAction, valApprovalType, valremarks);
       
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