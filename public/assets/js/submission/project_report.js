var modname = 'projectreport';
var modelclass = 'Project';
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
            width: 210
        },
        {
            caption: "Submit Date",
            dataField: 'Submitted',
            dataType: 'date',
            format: 'yyyy-MM-dd', 
            width: 100,
        },        
        {
            caption: "Approve Date",
            dataField: 'Approver',
            dataType: 'date',
            format: 'yyyy-MM-dd', 
            width: 100,
        },        
        {
            caption: "Last Progress Update",
            dataField: 'progres',
            dataType: 'date',
            format: 'yyyy-MM-dd', 
            width: 100,
        },        
        {
            caption: "Project Complete",
            dataField: 'ps',
            dataType: 'date',
            format: 'yyyy-MM-dd', 
            width: 100,
        },        
        {
            caption: "Approver Name",
            dataField: "approver_name",
            width: 120,
        },
        { 
            caption: 'Developer Name',
            width: 200,
			dataField: "developer_name",
            
        },           
        {
            dataField: 'nameSystem',
            width: 350,
            sortOrder: "asc",
        },
        {
            caption: "Description",
            dataField: 'description',
            width: 180
        },
        {
            caption: "Old System Desc",
            dataField: 'descOldSystem',
            width: 180
        },
        {
            caption: " New System Desc",
            dataField: 'descNewSystem',
            width: 180
        },
        {
            caption: "Purpose",
            dataField: 'purpose',
            width: 180
        },
        { 
			dataField: "requester_name",
            caption: 'Creator Name',
            width: 120
        },
        {
            caption: "Priority",
            dataField: 'priority',
            width: 100
        },
        {
            caption: "Expected Date",
            dataField: 'expecteddate',
            dataType: 'date',
            format: 'yyyy-MM-dd', 
            width: 100
        },
        {
            caption: "Progress",
            dataField: 'progress',
            customizeText: function(cellInfo) {
                return `${cellInfo.value}%`;
            },
            width: 100
        },
        {
            dataField: 'requestStatus',
            width: 180,
            encodeHtml: false,
            allowFiltering: false,
            allowHeaderFiltering: true,
            customizeText: function (e) {
                var arrText = [
                    "Draft",
                    "Waiting Approval",
                    "Rework",
                    "Approved",
                    "Rejected",
                ];
                return arrText[e.value];
            },
            // width: 110
        },
        {
            dataField: 'projectStatus',
            width: 180,
            encodeHtml: false,
            sortOrder: "desc",
            customizeText: function (e) {
                if(e.value == 'Completed') {
                    return "Completed"
                } else if(e.value == 'Progress') {
                    return "Progress"
                } else {
                    return "Waiting"
                }
            },
            // width: 110
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
        ID: 1,
        Title: '<i class="far fa-newspaper"> Form Data </i>',
        visible: true
    },
    {
        ID: 5,
        Title: '<i class="fas fa-users"> Assignment To </i>',
        visible: false
    },
    {
        ID: 6,
        Title: '<i class="fas fa-list-ul"> Stakeholders </i>',
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

    var isMine = options.data.isMine;
    var isPendingOnMe = options.data.isPendingOnMe;

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

    if(options.data.requestStatus == 3) {
        updateVisibleById(5, true);
    } else {
        updateVisibleById(5, false);
    }

    const syncTreeViewSelection = function (treeViewInstance, value) {
        if (!value) {
          treeViewInstance.unselectAll();
        } else {
          treeViewInstance.selectItem(value);
        }
    };

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
                var container = $("<div>");
                if (mode == 'add' || mode == 'edit'){
                    $("<span style='color:red;font-size:11pt'>").html('Silahkan lengkapi <b><i style="color:black;font-weight:bold" class="far fa-newspaper"> Form Data </i></b> dan tekan tombol <b>Simpan</b> (<i style="color:black;font-weight:bold" class="fas fa-save"></i>) yang ada di pojok kanan atas tabel serta lampirkan <i style="color:black;font-weight:bold" class="fas fa-file"> Supporting Document </i> sebelum klik tombol <span style="color:black;font-weight:bold"><i class="bx bx-check-double label-icon"></i> Submit Submission</span>').appendTo(container);
                }
                if(data.ID == 1) {
                    var formData = $("<div id='formdata'>").dxDataGrid({    
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
                                caption: 'Parent System',
                                dataField: 'parentID',
                                lookup: {
                                    dataSource: listOption('/list-parentproject','id','nameSystem'),  
                                    valueExpr: 'id',
                                    displayExpr: 'nameSystem',
                                },
                                width: 200,
                            },
                            {
                                dataField: 'nameSystem',
                                dataType: 'string',
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: 'description',
                                dataType: 'string',
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Old System Desc',
                                dataField: 'descOldSystem',
                                dataType: 'string',
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'New System Desc',
                                dataField: 'descNewSystem',
                                dataType: 'string',
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: 'purpose',
                                dataType: 'string',
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Expected Date',
                                dataField: 'expecteddate',
                                dataType: 'date',
                                format: "yyyy-MM-dd",
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: 'priority',
                                dataType: 'string',
                                visible: false,
                                lookup: {
                                    dataSource: ['Low','Middle','High'],  
                                },
                            },
                            {
                                dataField: 'progress',
                                caption: 'Progress %',
                                dataType: 'string',
                                visible: false,
                                lookup: {
                                    dataSource: [0,10,20,30,40,50,60,70,80,90,100],  
                                },
                            },
                            {
                                dataField: 'projectStatus',
                                dataType: 'string',
                                visible: false,
                                lookup: {
                                    dataSource: ['Waiting','Progress','Completed'],  
                                },
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
                            if (e.dataField == "parentID" && e.parentType == "dataRow") {
                                e.editorName = "dxDropDownBox";                
                                e.editorOptions.dropDownOptions = {        
                                    height: 500,
                                    width: 600
                                };
                                e.editorOptions.contentTemplate = function (args, container) {

                                        const $dataGrid = $("<div>").dxTreeView({
                                            dataSource: args.component.option("dataSource"),
                                            dataStructure: 'plain',
                                            keyExpr: "id",
                                            parentIdExpr: 'parentID',
                                            selectionMode: 'single',
                                            displayExpr: 'nameSystem',
                                            selectByClick: true,
                                            selectNodesRecursive: false,
                                            onItemSelectionChanged(selectedItems) {
                                                const selectedKeys = selectedItems.component.getSelectedNodeKeys();
                                                const hasSelection = selectedKeys.length;

                                                args.component.option('value', hasSelection ? selectedKeys[0] : null);
                                                if(hasSelection !== 0) {
                                                    args.component.close();
                                                }
                                            },
                                        });
                    
                                    var dataGrid = $dataGrid.dxTreeView("instance");
                    
                                    args.component.on("valueChanged", function (e) {
                                        
                                        var value = e.value;
                                        dataGrid.selectItem(value);
                                        e.component.close();
                                    });
                                    
                                    return $dataGrid;
                    
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
                            if (e.column.index == 0 && e.rowType == "data") {
                                if(e.data.requestStatus == 3) {
                                    $("#formdata").dxDataGrid('columnOption','priority', 'visible', true);
                                    $("#formdata").dxDataGrid('columnOption','progress', 'visible', true);
                                    $("#formdata").dxDataGrid('columnOption','projectStatus', 'visible', true);
                                }
                            }
                            if ( e.rowType == "data" && (e.column.index>1 && e.column.index<8)) {
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
                    }).appendTo(container)
                    return container
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
                                caption: 'Proposal File',
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

                    var downloadButton = $("<button>")
                        .text("Download Proposal Guide Template")
                        .addClass("btn btn-danger btn-xs")
                        .appendTo(supporting);

                    downloadButton.click(function() {
                        var fileUrl = "public/doc/Proposal Pengajuan System.pptx";
                        var link = document.createElement("a");
                        link.href = fileUrl;
                        link.download = "Proposal Pengajuan System.pptx";
                        link.click();
                    });

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
                else if(data.ID == 5) {
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
                            allowAdding: (admin == 1 || developer ) ? true : false,
                            allowUpdating: (admin == 1 || developer ) ? true : false,
                            allowDeleting: (admin == 1 || developer ) ? true : false,
                        },
                        paging: { enabled: true, pageSize: 10 },
                        columns: [
                            {
                                caption: "Developer Name",
                                dataField: "developer_id",
                                lookup: {
                                    dataSource: listOption('/list-developer','id','developerName'),
                                    displayExpr: "developerName",
                                    valueExpr: "id",
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
                else if(data.ID == 6) {
                    return $("<div id='formstackholders'>").dxDataGrid({    
                        dataSource: storewithmodule('stackholders',modelclass,reqid),
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
                            mode: "batch",
                            allowAdding: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 || developer ? true : false),
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 || developer ? true : false),
                            allowDeleting: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 || developer ? true : false),
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            {
                                caption: "Fullname",
                                dataField: "employee_id",
                                lookup: {
                                    dataSource: listOptionWeb('/list-getemployee/','id','fullname'),  
                                    valueExpr: 'id',
                                    displayExpr: 'fullname',
                                },
                                validationRules: [
                                    { 
                                        type: "required" 
                                    }
                                ]
                            },
                        ],
                        export: {
                            enabled: false,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGridStackholders = e.component;
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
                                            columns: ["companycode","fullname","departmentname"],
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
                                        dataGridStackholders.refresh();
                                    }
                                }
                            })
                        },
                        // onDataErrorOccurred: function(e) {
                        //     // Menampilkan pesan kesalahan
                        //     console.log("Terjadi kesalahan saat memuat data (6):", e.error.message);
                    
                        //     // Memuat ulang DataGrid
                        //     dataGridStackholders.refresh();
                        // }
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
      accept: ".pptx,.ppt,.docx,.pdf,.xlsx,.csv,.png,.jpg,.jpeg,.zip",
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
       
        let path = e.request.response;

        const unsafeCharacters = /[#"%<>\\^`{|}]/g;
        let unsafeFound = path.match(unsafeCharacters);

        if (unsafeFound) {
            let unsafeCharactersString = unsafeFound.join(', ');
            DevExpress.ui.dialog.alert(
                `The file name contains these unsafe characters: ${unsafeCharactersString}. Please rename the file to continue.`,
                "error"
            );
        
            path = "";
            retryButton.option("visible", true);
        } else {
            cellInfo.setValue(e.request.responseText);
            retryButton.option("visible", false);
        }

      },
      onUploadError: function(e){
          $path = "";
          DevExpress.ui.notify(e.request.response,"error");
      }
    }).dxFileUploader("instance");
  
    // let imageElement = document.createElement("img");
    //     imageElement.classList.add("uploadedImage");
    //     imageElement.setAttribute('src', "upload/" +cellInfo.value);
    //     imageElement.setAttribute('height', "50");
        
    //     cellElement.append(imageElement);
        cellElement.append(fileUploaderElement);
        cellElement.append(buttonElement);
  
  }