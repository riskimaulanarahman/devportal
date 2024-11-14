var modname = 'mmf28request';
var modelclass = 'Mmf';
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
            caption: 'Description',
			dataField: "MaterialDescr",
            width: 200
        },
        { 
            caption: 'Symptomps',
			dataField: "Symptomps",
            width: 200
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
        // Title: '<i class="far fa-newspaper"> Form Data 2 </i>',
        Title: '',
        visible: true
    },
    {
        ID: 7,
        Title: '<i class="far fa-newspaper"> Chemical Content </i>',
        visible: true
    },
    {
        ID: 6,
        Title: '<i class="fas fa-users"> Assignment To </i>',
        visible: false
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

    isMine = options.data.isMine;
    var isPendingOnMe = options.data.isPendingOnMe;
    isProcHead = options.data.isProcHead;
    isBuyer = options.data.isBuyer;

    var validationRequiredType = [];
    var visibleRequiredType = false;

    var validationHazardous = [];
    var visibleHazardous = false;
    var visibleNContaminated = false;
    var validationNContaminated = [];
    var visibleNHazardous = false;
    var validationNHazardous = [];

    popupid = reqid;

    // console.log(mode)
    // console.log(isMine)
    // console.log(isProcHead)

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
        updateVisibleById(6, true);
    } else {
        updateVisibleById(6, false);
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
                            'Silahkan lengkapi <b><i style="color:black;font-weight:bold" class="far fa-newspaper"> Form Data </i></b> sebelum klik tombol <span style="color:black;font-weight:bold"><i class="bx bx-check-double label-icon"></i> Submit Submission</span><br> Tekan <span style="color:red;font-weight:bold">ESC</span> Untuk Cancel Edit'
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
                                caption: "BU",
                                dataField: "bu",
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
                                caption: 'Work Order No',
                                dataField: 'detail28.WONumber',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Charge Code',
                                dataField: 'detail28.ChargeCode',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Material Dispatch No',
                                dataField: 'detail28.MaterialDispatch',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Required By (Date)',
                                dataField: 'detail28.RequiredDate',
                                dataType: "date",
                                format: "dd-MM-yyyy", 
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Material Code',
                                dataField: 'detail28.MaterialCode',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Material Description',
                                dataField: 'detail28.MaterialDescr',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Symptoms (Problem)',
                                dataField: 'detail28.Symptomps',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Telp No',
                                dataField: 'detail28.TelpNo',
                                dataType: "number",
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
                            if ( e.rowType == "data" && (e.column.index>1 && e.column.index<9)) {
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
                      var requiredData = [
                        { id: 1, name: "Repair" },
                        { id: 2, name: "Servicing" },
                        { id: 3, name: "Calibration" },
                        { id: 4, name: "Others" }
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 || isBuyer == 1 ? true : false),
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            {
                                caption: 'Required',
                                dataField: 'detail28.RequiredType',
                                lookup: { 
                                    dataSource: requiredData,  
                                    valueExpr: 'id',
                                    displayExpr: 'name',
                                },
                                setCellValue: function (rowData, value) {
                                    rowData.detail28 = rowData.detail28 || {};
                                    rowData.detail28.RequiredType = value;

                                    if (value == 4) {
                                        validationRequiredType.length = 0;
                                        validationRequiredType.push({
                                            type: "required", 
                                            message: "This item is required"
                                        });
                                        visibleRequiredType = true;
                                    } else {
                                        validationRequiredType.length = 0;
                                        visibleRequiredType = false;
                                    }
                                    // Update the visibility of the "Others Detail" column and refresh the grid
                                    dataGrid11.columnOption('detail28.RequiredOther', 'visible', visibleRequiredType);
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Specify Others',
                                dataField: 'detail28.RequiredOther',
                                editorType: 'dxTextArea',
                                editorOptions: { 
                                    height: 50,
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                visible: visibleRequiredType,
                                validationRules: validationRequiredType,
                            },
                            {
                                caption: 'Instruction',
                                dataField: 'detail28.Instruction',
                                editorType: 'dxTextArea',
                                editorOptions: { 
                                    height: 50,
                                    readOnly: (mode == 'approval') ? true : false
                                }
                            },
                            {
                                caption: 'Estimation Cost',
                                dataField: 'detail28.EstimateCost',
                                dataType: "number",
                                format: "fixedPoint",
                                visible: (mode == 'approval' && isBuyer == 1) ? true : false,
                                editorOptions: { 
                                    format: "fixedPoint",
                                    readOnly: (mode == 'approval' && isBuyer == 1) ? false : true
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
                            dataGrid11 = e.component;
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
                            dataGrid11.refresh();
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
                            if ( e.rowType == "data" && (e.column.index==0)) {
                                if (e.value === "" || e.value === null || e.value === undefined || /^\s*$/.test(e.value)) {
                                    e.cellElement.css({
                                        "backgroundColor": "#ffe6e6",
                                        "border": "0.5px solid #f56e6e"
                                    })
                                }
                            }
                            if (e.column.index == 0 && e.rowType == "data") {
                                if(e.value === 4) {
                                    dataGrid11.columnOption('detail28.RequiredOther', 'visible', true);
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

                    function updateVisibility(dataGrid) {
                        var rows = dataGrid.getVisibleRows();
                    
                        // Reset visibility rules
                        visibleRequiredType = false;
                    
                        rows.forEach(function(row) {
                            if (row.data.detail28 && row.data.detail28.RequiredType === 4) {
                                visibleRequiredType = true;
                            }
                        });
                    
                        // Update the visibility of the columns and refresh the grid
                        dataGrid.columnOption('detail28.RequiredOther', 'visible', visibleRequiredType);
                    }

                    return formData2;
                }
                if(data.ID == 7) {

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
                                caption: 'Hazardous Chemical',
                                dataField: 'detail28.isHazardousChemical',
                                dataType: 'boolean',
                                setCellValue: function (rowData, value) {
                                    rowData.detail28 = rowData.detail28 || {};
                                    rowData.detail28.isHazardousChemical = value;

                                    if (value) {
                                        validationHazardous.length = 0;
                                        validationNContaminated.length = 0;
                                        validationNHazardous.length = 0;
                                        validationHazardous.push({
                                            type: "required", 
                                            message: "This item is required"
                                        });
                                        visibleHazardous = true;
                                    } else {
                                        validationHazardous.length = 0;
                                        validationNContaminated.length = 0;
                                        validationNHazardous.length = 0;
                                        visibleHazardous = false;
                                    }
                                    // Update the visibility
                                    dataGrid12.columnOption('detail28.HazChemicalName', 'visible', visibleHazardous);
                                    dataGrid12.columnOption('detail28.isDecontaminated', 'visible', visibleHazardous);
                                    dataGrid12.columnOption('detail28.isNonChemical', 'visible', visibleHazardous);
                                    dataGrid12.columnOption('detail28.isNotContaminated', 'visible', visibleHazardous);
                                    dataGrid12.columnOption('detail28.isNonHazardous', 'visible', visibleHazardous);
                                    // dataGrid12.columnOption('detail28.NotContaminatedReason', 'visible', visibleHazardous);
                                    // dataGrid12.columnOption('detail28.NonHazChemicalName', 'visible', visibleHazardous);
                                },
                            },
                            {
                                caption: 'Chemical Name',
                                dataField: 'detail28.HazChemicalName',
                                visible: visibleHazardous,
                                validationRules: validationHazardous,
                            },
                            {
                                caption: 'Decontaminated',
                                dataField: 'detail28.isDecontaminated',
                                dataType: 'boolean',
                                visible: visibleHazardous,
                            },
                            {
                                caption: 'No Chemical Involved',
                                dataField: 'detail28.isNonChemical',
                                dataType: 'boolean',
                                visible: visibleHazardous,
                            },
                            {
                                caption: 'Not Contaminated',
                                dataField: 'detail28.isNotContaminated',
                                dataType: 'boolean',
                                visible: visibleHazardous,
                                setCellValue: function (rowData, value) {
                                    rowData.detail28 = rowData.detail28 || {};
                                    rowData.detail28.isNotContaminated = value;

                                    if (value) {
                                        validationNContaminated.length = 0;
                                        validationNContaminated.push({
                                            type: "required", 
                                            message: "This item is required"
                                        });
                                        visibleNContaminated = true;
                                    } else {
                                        validationNContaminated.length = 0;
                                        visibleNContaminated = false;
                                    }
                                    // Update the visibility
                                    dataGrid12.columnOption('detail28.NotContaminatedReason', 'visible', visibleNContaminated);
                                },
                            },
                            {
                                caption: 'Reason (Not Contaminated)',
                                dataField: 'detail28.NotContaminatedReason',
                                visible: visibleNContaminated,
                                validationRules: validationNContaminated,
                            },
                            {
                                caption: 'Non-hazardous Chemical',
                                dataField: 'detail28.isNonHazardous',
                                dataType: 'boolean',
                                visible: visibleHazardous,
                                setCellValue: function (rowData, value) {
                                    rowData.detail28 = rowData.detail28 || {};
                                    rowData.detail28.isNonHazardous = value;

                                    if (value) {
                                        validationNHazardous.length = 0;
                                        validationNHazardous.push({
                                            type: "required", 
                                            message: "This item is required"
                                        });
                                        visibleNHazardous = true;
                                    } else {
                                        validationNHazardous.length = 0;
                                        visibleNHazardous = false;
                                    }
                                    // Update the visibility
                                    dataGrid12.columnOption('detail28.NonHazChemicalName', 'visible', visibleNHazardous);
                                },
                            },
                            {
                                caption: 'Chemical Name (Non-hazardous Chemical)',
                                dataField: 'detail28.NonHazChemicalName',
                                visible: visibleNHazardous,
                                validationRules: validationNHazardous,
                            },
                      ],
                      export: {
                          enabled: false,
                          fileName: modname,
                          excelFilterEnabled: true,
                          allowExportSelectedData: true
                      },
                      onInitialized: function(e) {
                          dataGrid12 = e.component;
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
                                        dataGrid12.refresh();
                                    }
                              }
                          });
                      },
                      onSaved: function(e) {
                            updateVisibility2(e.component);
                      },
                      onEditCanceled: function(e) {
                            updateVisibility2(e.component);
                            dataGrid12.refresh();
                      },
                      onEditorPreparing: function (e) {
                      },
                      onCellPrepared: function (e) {
                            if (e.column.index == 0 && e.rowType == "data") {
                                if(e.value === 1) {
                                    dataGrid12.columnOption('detail28.HazChemicalName', 'visible', true);
                                    dataGrid12.columnOption('detail28.isDecontaminated', 'visible', true);
                                    dataGrid12.columnOption('detail28.isNonChemical', 'visible', true);
                                    dataGrid12.columnOption('detail28.isNotContaminated', 'visible', true);
                                    dataGrid12.columnOption('detail28.isNonHazardous', 'visible', true);
                                }
                            }
                            if (e.column.index == 4 && e.rowType == "data") {
                                if(e.value === 1) {
                                    dataGrid12.columnOption('detail28.NotContaminatedReason', 'visible', true);
                                }
                            }
                            if (e.column.index == 6 && e.rowType == "data") {
                                if(e.value === 1) {
                                    dataGrid12.columnOption('detail28.NonHazChemicalName', 'visible', true);
                                }
                            }
                      },
                      onDataErrorOccurred: function(e) {
                          // Menampilkan pesan kesalahan
                          console.log("Terjadi kesalahan saat memuat data (1.1.3):", e.error.message);
                  
                          // Memuat ulang DataGrid
                          dataGrid12.refresh();
                      }
                  })

                  function updateVisibility2(dataGrid) {
                    var rows = dataGrid.getVisibleRows();
                
                    // Reset visibility rules
                    visibleHazardous = false;
                    visibleNContaminated = false;
                    visibleNHazardous = false;
                
                    rows.forEach(function(row) {
                        if (row.data.detail28 && row.data.detail28.isHazardousChemical === 1) {
                            visibleHazardous = true;
                        }
                        if (row.data.detail28 && row.data.detail28.isNotContaminated === 1) {
                            visibleNContaminated = true;
                        }
                        if (row.data.detail28 && row.data.detail28.isNonHazardous === 1) {
                            visibleNHazardous = true;
                        }
                    });
                    
                
                    // Update the visibility of the columns and refresh the grid
                    dataGrid.columnOption('detail28.HazChemicalName', 'visible', visibleHazardous);
                    dataGrid.columnOption('detail28.isDecontaminated', 'visible', visibleHazardous);
                    dataGrid.columnOption('detail28.isNonChemical', 'visible', visibleHazardous);
                    dataGrid.columnOption('detail28.isNotContaminated', 'visible', visibleHazardous);
                    dataGrid.columnOption('detail28.isNonHazardous', 'visible', visibleHazardous);
                    dataGrid.columnOption('detail28.NotContaminatedReason', 'visible', visibleNContaminated);
                    dataGrid.columnOption('detail28.NonHazChemicalName', 'visible', visibleNHazardous);
                }

                  return formData2;
                }
                else if(data.ID == 6) {
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

    if(mode == 'approval' && isProcHead == 1) {
        var fieldsToCheckGrid = [
            { field: 'Buyer', name: 'Buyer' },
        ]
    } else {
        var fieldsToCheckGrid = [
            { field: 'WONumber', name: 'Work Order No' },
            { field: 'ChargeCode', name: 'Charge Code' },
            { field: 'MaterialDispatch', name: 'Material Dispatch No' },
            { field: 'RequiredDate', name: 'Required By (Date)' },
            { field: 'MaterialCode', name: 'Material Code' },
            { field: 'MaterialDescr', name: 'Material Description' },
            { field: 'Symptomps', name: 'Symptoms (Problem)' },
            { field: 'RequiredType', name: 'Required' },
            
        ];
    }

    // console.log(mode);
    // console.log(isProcHead);
    // console.log(fieldsToCheckGrid);

    sendRequest(apiurl + "/submissioncheckfields/"+reqid+"/Mmf28", "POST", {
        fieldsToCheckGrid
    }).then(function(response){
        if(response.status !== 'error') {
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
    })
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
    var value = options.value ? options.value.trim() : '';
    var origin = window.location.origin;
    if (value && value.includes('upload')) {
        var baseUrl = origin + '/oasys/';
    } else {
        var baseUrl = origin + '/devportal/public/upload/';
    }
    var fullUrl = baseUrl + value;
    container.append('<a href="'+fullUrl+'" target="_blank"><img src="public/assets/images/showfile.png" height="50" width="70"></a>');
    // container.append('<a href="public/upload/'+options.value+'" target="_blank"><img src="public/assets/images/showfile.png" height="50" width="70"></a>');
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