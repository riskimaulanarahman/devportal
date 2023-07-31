var modname = 'travelrequest';

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
    // columnsAutoWidth: true,
    columnHidingEnabled: true,
    rowAlternationEnabled: true,
    wordWrapEnabled: true,
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
        allowDeleting: false,
    },
    scrolling: {
        mode: "virtual"
    },
    columns: [
        {
            caption: 'Action',
            width: 100,
            cellTemplate: function(container, options) {

                var reqid = options.data.id;
                var reqstatus = options.data.RequestStatus;
                var mode = (reqstatus == 0 || reqstatus == 2) ? 'edit' : 'view';
                var arrColor = [
                    "btn-secondary",
                    "btn-primary",
                    "btn-warning",
                    "btn-success",
                    "btn-danger",
                ];
    
                $('<button class="btn '+arrColor[reqstatus]+'" id="btnreqid'+reqid+'"><i class="fa fa-search"></i></button>').on('dxclick', function(evt) {
                    evt.stopPropagation();
                        
                            popup.option({
                                contentTemplate: () => popupContentTemplate(reqid,mode),
                            });
                            popup.show();

                }).appendTo(container);
                if(reqstatus == 1 || reqstatus == 2) {
                    $('<button class="btn btn-danger" id="btnreqid'+reqid+'">Cancel</button>').on('dxclick', function(evt) {
                        evt.stopPropagation();
                            
                                // popup.option({
                                //     contentTemplate: () => popupContentTemplate(reqid,mode),
                                // });
                                // popup.show();
                                alert('cancel : '+reqid)
    
                    }).appendTo(container); 
                }
            
            }
        },
        {
            caption: "Code",
            dataField: 'code',
        },
        {
			dataField: "CreatedDate",
            sortOrder: "asc",
            dataType: "date",
            format: "dd-MM-yyyy",
        },
        { 
			dataField: "creatorname",
        },
        { 
			dataField: "foremployee",
            caption: "For Employee",
        },
        {
            dataField: 'RequestStatus',
            encodeHtml: false,
            allowFiltering: false,
            allowHeaderFiltering: true,
            customizeText: function (e) {
                var arrText = [
                    "<span class='btn btn-secondary btn-xs'>Draft</span>",
                    "<span class='btn btn-primary btn-xs'>Waiting Approval</span>",
                    "<span class='btn btn-warning btn-xs'>Rework</span>",
                    "<span class='btn btn-success btn-xs'>Approved</span>",
                    "<span class='btn btn-danger btn-xs'>Rejected</span>",
                ];
                return arrText[e.value];
            }
        },
        {
            dataField: 'TravelCategory',
            encodeHtml: false,
            allowFiltering: false,
            allowHeaderFiltering: true,
            customizeText: function (e) {
                var arrText = [
                    "<span class='btn btn-outline-success waves-effect'>Internal BU</span>",
                    "<span class='btn btn-outline-danger waves-effect'>External BU</span>"
                ];
                return arrText[e.value];
            }
        },
        {
            dataField: "ApprovedDoc",
            caption:"Approval Doc",
            width: 180,
            allowFiltering: false,
            allowHeaderFiltering: true,
            allowSorting: true,
            formItem: { visible: false},
            cellTemplate: function (container, options) {
                if ((options.value!="") && (options.value)){
                    $("<div />").html('<a href="'+options.value+'" target="_blank"><button type="button" class="btn btn-success waves-effect btn-label waves-light"><i class="bx bx-download label-icon"></i></button></a>').appendTo(container);
                }
            }
        },
    ],
    export: {
        enabled: true,
        fileName: modname,
        excelFilterEnabled: true,
        allowExportSelectedData: true
    },
    onContentReady: function(e){
        moveEditColumnToLeft(e.component);
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

$('#btnadd').on('click',function(){
    alert('create submission');
    // popup.option({
    //     contentTemplate: () => popupContentTemplate(),
    // });
    // popup.show();
})

const accordionItems = [
    {
        ID: 1,
        Title: '<i class="far fa-newspaper"> Form Data </i>',
    },
    {
        ID: 2,
        Title: '<i class="fas fa-calendar-alt"> Detail Schedule </i>',
    },
    {
        ID: 3,
        Title: '<i class="fas fa-ticket-alt"> Detail Ticket </i>',
    },
    {
        ID: 4,
        Title: '<i class="fas fa-file"> Supporting Document </i>',
    },
    {
        ID: 5,
        Title: '<i class="fas fa-list-ul"> Approver List </i>',
    },
    {
        ID: 6,
        Title: '<i class="fas fa-history"> Approver History </i>',
    },
];

const treeview = [
    {
      "ID": 1,
      "name": "Stores",
      "expanded": true
    },
    {
      "ID": "1_1",
      "categoryId": 1,
      "name": "Super Mart of the West",
      "expanded": true
    },
    {
      "ID": "1_1_1",
      "categoryId": "1_1",
      "name": "Video Players"
    },
    {
      "ID": "1_1_1_1",
      "categoryId": "1_1_1",
      "name": "HD Video Player",
      "price": 220
    },
    {
      "ID": "1_1_1_2",
      "categoryId": "1_1_1",
      "name": "SuperHD Video Player",
      "price": 270
    },
    {
      "ID": "1_1_2",
      "categoryId": "1_1",
      "name": "Televisions",
      "expanded": true
    },
    {
      "ID": "1_1_2_1",
      "categoryId": "1_1_2",
      "name": "SuperLCD 42",
      "price": 1200
    },
    {
      "ID": "1_1_2_2",
      "categoryId": "1_1_2",
      "name": "SuperLED 42",
      "price": 1450
    },
    {
      "ID": "1_1_2_3",
      "categoryId": "1_1_2",
      "name": "SuperLED 50",
      "price": 1600
    },
    {
      "ID": "1_1_2_4",
      "categoryId": "1_1_2",
      "name": "SuperLCD 55",
      "price": 1750
    },
    {
      "ID": "1_1_2_5",
      "categoryId": "1_1_2",
      "name": "SuperLCD 70",
      "price": 4000
    },
    {
      "ID": "1_1_3",
      "categoryId": "1_1",
      "name": "Monitors"
    },
    {
      "ID": "1_1_3_1",
      "categoryId": "1_1_3",
      "name": "19\""
    },
    {
      "ID": "1_1_3_1_1",
      "categoryId": "1_1_3_1",
      "name": "DesktopLCD 19",
      "price": 160
    },
    {
      "ID": "1_1_4",
      "categoryId": "1_1",
      "name": "Projectors"
    },
    {
      "ID": "1_1_4_1",
      "categoryId": "1_1_4",
      "name": "Projector Plus",
      "price": 550
    },
    {
      "ID": "1_1_4_2",
      "categoryId": "1_1_4",
      "name": "Projector PlusHD",
      "price": 750
    }
];

const popupContentTemplate = function (reqid,mode) {

    const scrollView = $('<div />');

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
                if(data.ID == 1) {
                    return formData = $("<div id='formdata'>").dxDataGrid({    
                        dataSource: storedetail('travelrequest',reqid),
                        allowColumnReordering: true,
                        allowColumnResizing: true,
                        columnsAutoWidth: true,
                        rowAlternationEnabled: true,
                        wordWrapEnabled: true,
                        showBorders: true,
                        filterRow: { visible: true },
                        filterPanel: { visible: false },
                        headerFilter: { visible: true },
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
                            allowUpdating: (mode == 'edit') ? true : false,
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
                                caption: 'For Employee',
                                dataField: 'employee_id',
                                allowFiltering: false,
                                allowHeaderFiltering: false,
                                lookup: {
                                    dataSource: listOptionWeb('/list-employeesamedept','id','fullname'),
                                    valueExpr: 'id',
                                    displayExpr: 'fullname',
                                },
                                validationRules: [{ type: "required" }]
                            },             
                            {
                                dataField: 'TravelCategory',
                                name: 'travelcategory',
                                allowFiltering: false,
                                allowHeaderFiltering: false,
                                lookup: {
                                    dataSource: [{id:0,value:'Internal BU'},{id:1,value:'External BU'}],  
                                    valueExpr: 'id',
                                    displayExpr: 'value',
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: 'TravelUse',
                                allowFiltering: false,
                                allowHeaderFiltering: false,
                                lookup: {
                                    dataSource: [{id:0,value:'Land'},{id:1,value:'Air'}],  
                                    valueExpr: 'id',
                                    displayExpr: 'value',
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: 'viaLand',
                                allowFiltering: false,
                                allowHeaderFiltering: false,
                                visible: false,
                                lookup: {
                                    dataSource: [
                                        {id:0,value:'Personal Vehicle'},
                                        {id:1,value:'Pool Car - Drop Off Only'},
                                        {id:2,value:'Pool Car - Until Job Finished'},
                                        {id:3,value:'By Train'},
                                        {id:4,value:'Other'},
                                    ],  
                                    valueExpr: 'id',
                                    displayExpr: 'value',
                                },
                            },
                            {
                                caption: "Job Finish Date",
                                dataField: "jobfinishdate",
                                allowFiltering: false,
                                allowHeaderFiltering: false,
                                dataType: "date",
                                format: "dd-MM-yyyy",
                                displayFormat: "dd-MM-yyyy",
                                visible: false,
                                editorOptions: {
                                    format: "dd-MM-yyyy",
                                    // onValueChanged : function(e) {
                                    //     console.log(e.value);
                                    // }
                                }
                            },
                            {
                                dataField: "OtherLandTransportDesc",
                                allowFiltering: false,
                                allowHeaderFiltering: false,
                                visible: false,
                            },
                            {
                                dataField: 'viaAir',
                                allowFiltering: false,
                                allowHeaderFiltering: false,
                                visible: false,
                                lookup: {
                                    dataSource: [{id:0,value:'Commercial Airline'},{id:1,value:'Company Aircraft'}],  
                                    valueExpr: 'id',
                                    displayExpr: 'value',
                                },
                            }
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
                            // e.data.namacolumn = 'value'
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
                            // e.toolbarOptions.items.unshift({
                            //     location: "before",
                            //     widget: "dxButton",
                            //     options: {
                            //         text: "Save",
                            //         onClick: function() {
                            //             var data = $("#formdata").dxDataGrid("instance").getDataSource().items();
                            //             console.log("Data to be saved: ", data);
                            //         }
                            //     }
                            // });
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
                                                console.log(hasSelection)
                                                args.component.close();
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
                            if (e.column.index == 3 && e.rowType == "data") {
                                if(e.data.TravelUse === 1) {
                                    $("#formdata").dxDataGrid('columnOption','viaAir', 'visible', true);
                                    $("#formdata").dxDataGrid('columnOption','viaLand', 'visible', false);
                                } else {
                                    $("#formdata").dxDataGrid('columnOption','viaAir', 'visible', false);
                                    $("#formdata").dxDataGrid('columnOption','viaLand', 'visible', true);
                                }
                            }
                            if (e.column.index == 4 && e.rowType == "data") {
                                if(e.data.viaLand == 2) {
                                    $("#formdata").dxDataGrid('columnOption','jobfinishdate', 'visible', true);
                                    $("#formdata").dxDataGrid('columnOption','OtherLandTransportDesc', 'visible', false);
                                } else if(e.data.viaLand == 4) {
                                    $("#formdata").dxDataGrid('columnOption','jobfinishdate', 'visible', false);
                                    $("#formdata").dxDataGrid('columnOption','OtherLandTransportDesc', 'visible', true);
                                } else {
                                    $("#formdata").dxDataGrid('columnOption','jobfinishdate', 'visible', false);
                                    $("#formdata").dxDataGrid('columnOption','OtherLandTransportDesc', 'visible', false);
                                }
                            }
                        }
                    })
                } 
                // else if(data.ID == 2) {
                //     return $("<div id='historyhcdepartment'>").dxDataGrid({    
                //         dataSource: storedetail('historyhcdepartment',reqid),
                //         allowColumnReordering: true,
                //         allowColumnResizing: true,
                //         columnsAutoWidth: true,
                //         rowAlternationEnabled: true,
                //         wordWrapEnabled: true,
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
                //             mode: "cell",
                //             allowAdding: false,
                //             allowUpdating: false,
                //             allowDeleting: false,
                //         },
                //         scrolling: {
                //             mode: "virtual"
                //         },
                //         columns: [
                //             { 
                //                 dataField: "column",
                //                 editorOptions: {
                //                     readOnly: true,
                //                 },
                //                 validationRules: [
                //                     { 
                //                         type: "required" 
                //                     }
                //                 ]
                //             },
                //             { 
                //                 dataField: "value",
                //                 caption: "Department",
                //                 lookup: {
                //                     dataSource: listOption('/list-department','id','DepartmentName'),
                //                     displayExpr: "DepartmentName",
                //                     valueExpr: "id",
                //                 },
                //                 validationRules: [{ type: "required" }]
                //             },
                //             {
                //                 dataField: "created_at",
                //                 sortOrder: "asc",
                //                 caption: "Date",
                //                 dataType: "date",
                //                 format: "dd-MM-yyyy",
                //             },
                //         ],
                //         export: {
                //             enabled: true,
                //             fileName: modname,
                //             excelFilterEnabled: true,
                //             allowExportSelectedData: true
                //         },
                //         onInitialized: function(e) {
                //             dataGrid2 = e.component;
                //         },
                //         onContentReady: function(e){
                //             moveEditColumnToLeft(e.component);
                //         },
                //         onInitNewRow : function(e) {
                //             e.data.column = 'Department'
                //         },
                //         onToolbarPreparing: function(e) {
                //             e.toolbarOptions.items.unshift({						
                //                 location: "after",
                //                 widget: "dxButton",
                //                 options: {
                //                     hint: "Refresh Data",
                //                     icon: "refresh",
                //                     onClick: function() {
                //                         dataGrid2.refresh();
                //                     }
                //                 }
                //             })
                //         },
                //     })
                // } else if(data.ID == 3) {
                //     return $("<div id='historyhclocation'>").dxDataGrid({    
                //         dataSource: storedetail('historyhclocation',reqid),
                //         allowColumnReordering: true,
                //         allowColumnResizing: true,
                //         columnsAutoWidth: true,
                //         rowAlternationEnabled: true,
                //         wordWrapEnabled: true,
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
                //             mode: "cell",
                //             allowAdding: false,
                //             allowUpdating: false,
                //             allowDeleting: false,
                //         },
                //         scrolling: {
                //             mode: "virtual"
                //         },
                //         columns: [
                //             { 
                //                 dataField: "column",
                //                 editorOptions: {
                //                     readOnly: true,
                //                 },
                //                 validationRules: [
                //                     { 
                //                         type: "required" 
                //                     }
                //                 ]
                //             },
                //             { 
                //                 dataField: "value",
                //                 caption: "Location",
                //                 lookup: {
                //                     dataSource: listOption('/list-location','id','location'),  
                //                     valueExpr: 'id',
                //                     displayExpr: 'location',
                //                 },
                //                 validationRules: [{ type: "required" }]
                //             },
                //             {
                //                 dataField: "created_at",
                //                 sortOrder: "asc",
                //                 caption: "Date",
                //                 dataType: "date",
                //                 format: "dd-MM-yyyy",
                //             },
                //         ],
                //         export: {
                //             enabled: true,
                //             fileName: modname,
                //             excelFilterEnabled: true,
                //             allowExportSelectedData: true
                //         },
                //         onInitialized: function(e) {
                //             dataGrid3 = e.component;
                //         },
                //         onContentReady: function(e){
                //             moveEditColumnToLeft(e.component);
                //         },
                //         onInitNewRow : function(e) {
                //             e.data.column = 'Location'
                //         },
                //         onToolbarPreparing: function(e) {
                //             e.toolbarOptions.items.unshift({						
                //                 location: "after",
                //                 widget: "dxButton",
                //                 options: {
                //                     hint: "Refresh Data",
                //                     icon: "refresh",
                //                     onClick: function() {
                //                         dataGrid3.refresh();
                //                     }
                //                 }
                //             })
                //         },
                //     })
                // } else if(data.ID == 4) {
                //     return $("<div id='historyhcposition'>").dxDataGrid({    
                //         dataSource: storedetail('historyhcposition',reqid),
                //         allowColumnReordering: true,
                //         allowColumnResizing: true,
                //         columnsAutoWidth: true,
                //         rowAlternationEnabled: true,
                //         wordWrapEnabled: true,
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
                //             mode: "cell",
                //             allowAdding: false,
                //             allowUpdating: false,
                //             allowDeleting: false,
                //         },
                //         scrolling: {
                //             mode: "virtual"
                //         },
                //         columns: [
                //             { 
                //                 dataField: "column",
                //                 editorOptions: {
                //                     readOnly: true,
                //                 },
                //                 validationRules: [
                //                     { 
                //                         type: "required" 
                //                     }
                //                 ]
                //             },
                //             { 
                //                 dataField: "value",
                //                 caption: "Position",
                //                 lookup: {
                //                     dataSource: listOption('/list-designation','id','DesignationName'),  
                //                     valueExpr: 'id',
                //                     displayExpr: 'DesignationName',
                //                 },
                //                 validationRules: [{ type: "required" }]
                //             },
                //             {
                //                 dataField: "created_at",
                //                 sortOrder: "asc",
                //                 caption: "Date",
                //                 dataType: "date",
                //                 format: "dd-MM-yyyy",
                //             },
                //         ],
                //         export: {
                //             enabled: true,
                //             fileName: modname,
                //             excelFilterEnabled: true,
                //             allowExportSelectedData: true
                //         },
                //         onInitialized: function(e) {
                //             dataGrid4 = e.component;
                //         },
                //         onContentReady: function(e){
                //             moveEditColumnToLeft(e.component);
                //         },
                //         onInitNewRow : function(e) {
                //             e.data.column = 'Position'
                //         },
                //         onToolbarPreparing: function(e) {
                //             e.toolbarOptions.items.unshift({						
                //                 location: "after",
                //                 widget: "dxButton",
                //                 options: {
                //                     hint: "Refresh Data",
                //                     icon: "refresh",
                //                     onClick: function() {
                //                         dataGrid4.refresh();
                //                     }
                //                 }
                //             })
                //         },
                //     })
                // } else if(data.ID == 5) {
                //     return $("<div id='historyhcgrade'>").dxDataGrid({    
                //         dataSource: storedetail('historyhcgrade',reqid),
                //         allowColumnReordering: true,
                //         allowColumnResizing: true,
                //         columnsAutoWidth: true,
                //         rowAlternationEnabled: true,
                //         wordWrapEnabled: true,
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
                //             mode: "cell",
                //             allowAdding: false,
                //             allowUpdating: false,
                //             allowDeleting: false,
                //         },
                //         scrolling: {
                //             mode: "virtual"
                //         },
                //         columns: [
                //             { 
                //                 dataField: "column",
                //                 editorOptions: {
                //                     readOnly: true,
                //                 },
                //                 validationRules: [
                //                     { 
                //                         type: "required" 
                //                     }
                //                 ]
                //             },
                //             { 
                //                 dataField: "value",
                //                 caption: "Grade",
                //                 lookup: {
                //                     dataSource: listOption('/list-grade','id','grade'),  
                //                     valueExpr: 'id',
                //                     displayExpr: 'grade',
                //                 },
                //                 validationRules: [{ type: "required" }]
                //             },
                //             {
                //                 dataField: "created_at",
                //                 sortOrder: "asc",
                //                 caption: "Date",
                //                 dataType: "date",
                //                 format: "dd-MM-yyyy",
                //             },
                //         ],
                //         export: {
                //             enabled: true,
                //             fileName: modname,
                //             excelFilterEnabled: true,
                //             allowExportSelectedData: true
                //         },
                //         onInitialized: function(e) {
                //             dataGrid5 = e.component;
                //         },
                //         onContentReady: function(e){
                //             moveEditColumnToLeft(e.component);
                //         },
                //         onInitNewRow : function(e) {
                //             e.data.column = 'Grade'
                //         },
                //         onToolbarPreparing: function(e) {
                //             e.toolbarOptions.items.unshift({						
                //                 location: "after",
                //                 widget: "dxButton",
                //                 options: {
                //                     hint: "Refresh Data",
                //                     icon: "refresh",
                //                     onClick: function() {
                //                         dataGrid5.refresh();
                //                     }
                //                 }
                //             })
                //         },
                //     })
                // } else if(data.ID == 6) {
                //     return $("<div id='historyhclevel'>").dxDataGrid({    
                //         dataSource: storedetail('historyhclevel',reqid),
                //         allowColumnReordering: true,
                //         allowColumnResizing: true,
                //         columnsAutoWidth: true,
                //         rowAlternationEnabled: true,
                //         wordWrapEnabled: true,
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
                //             mode: "cell",
                //             allowAdding: false,
                //             allowUpdating: false,
                //             allowDeleting: false,
                //         },
                //         scrolling: {
                //             mode: "virtual"
                //         },
                //         columns: [
                //             { 
                //                 dataField: "column",
                //                 editorOptions: {
                //                     readOnly: true,
                //                 },
                //                 validationRules: [
                //                     { 
                //                         type: "required" 
                //                     }
                //                 ]
                //             },
                //             { 
                //                 dataField: "value",
                //                 caption: "Level",
                //                 lookup: {
                //                     dataSource: listOption('/list-level','id','level'),  
                //                     valueExpr: 'id',
                //                     displayExpr: 'level',
                //                 },
                //                 validationRules: [{ type: "required" }]
                //             },
                //             {
                //                 dataField: "created_at",
                //                 sortOrder: "asc",
                //                 caption: "Date",
                //                 dataType: "date",
                //                 format: "dd-MM-yyyy",
                //             },
                //         ],
                //         export: {
                //             enabled: true,
                //             fileName: modname,
                //             excelFilterEnabled: true,
                //             allowExportSelectedData: true
                //         },
                //         onInitialized: function(e) {
                //             dataGrid6 = e.component;
                //         },
                //         onContentReady: function(e){
                //             moveEditColumnToLeft(e.component);
                //         },
                //         onInitNewRow : function(e) {
                //             e.data.column = 'Level'
                //         },
                //         onToolbarPreparing: function(e) {
                //             e.toolbarOptions.items.unshift({						
                //                 location: "after",
                //                 widget: "dxButton",
                //                 options: {
                //                     hint: "Refresh Data",
                //                     icon: "refresh",
                //                     onClick: function() {
                //                         dataGrid6.refresh();
                //                     }
                //                 }
                //             })
                //         },
                //     })
                // } else if(data.ID == 7) {
                //     return $("<div id='historyhcappraisal'>").dxDataGrid({    
                //         dataSource: storedetail('historyhcappraisal',reqid),
                //         allowColumnReordering: true,
                //         allowColumnResizing: true,
                //         columnsAutoWidth: true,
                //         rowAlternationEnabled: true,
                //         wordWrapEnabled: true,
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
                //             mode: "cell",
                //             allowAdding: false,
                //             allowUpdating: false,
                //             allowDeleting: false,
                //         },
                //         scrolling: {
                //             mode: "virtual"
                //         },
                //         columns: [
                //             { 
                //                 dataField: "column",
                //                 editorOptions: {
                //                     readOnly: true,
                //                 },
                //                 validationRules: [
                //                     { 
                //                         type: "required" 
                //                     }
                //                 ]
                //             },
                //             { 
                //                 dataField: "value",
                //                 caption: "Point Appraisal",
                //                 // lookup: {
                //                 //     dataSource: listOption('/list-department','id','DepartmentName'),
                //                 //     displayExpr: "DepartmentName",
                //                 //     valueExpr: "id",
                //                 // },
                //                 validationRules: [{ type: "required" }]
                //             },
                //             {
                //                 dataField: "created_at",
                //                 sortOrder: "asc",
                //                 caption: "Date",
                //                 dataType: "date",
                //                 format: "dd-MM-yyyy",
                //             },
                //         ],
                //         export: {
                //             enabled: true,
                //             fileName: modname,
                //             excelFilterEnabled: true,
                //             allowExportSelectedData: true
                //         },
                //         onInitialized: function(e) {
                //             dataGrid7 = e.component;
                //         },
                //         onContentReady: function(e){
                //             moveEditColumnToLeft(e.component);
                //         },
                //         onInitNewRow : function(e) {
                //             e.data.column = 'Appraisal'
                //         },
                //         onToolbarPreparing: function(e) {
                //             e.toolbarOptions.items.unshift({						
                //                 location: "after",
                //                 widget: "dxButton",
                //                 options: {
                //                     hint: "Refresh Data",
                //                     icon: "refresh",
                //                     onClick: function() {
                //                         dataGrid7.refresh();
                //                     }
                //                 }
                //             })
                //         },
                //     })
                // }
            }
        })
    
    );

    // scrollView.append(
    //     $("<div id='reqForm'>").dxForm({
    //         onInitialized: function(e) {
    //             dxFormInstance = e.component;
    //         },
    //         labelMode : 'floating',
    //         readOnly: false,
    //         showColonAfterLabel: true,
    //         showValidationSummary: false,
    //         items: [ {
    //         itemType: 'group',
    //         caption: '',
    //         colCount : 2,
    //         items: 
    //             [
    //                 { 
    //                     dataField: 'CreatedDate',
    //                     label: { text: "Creation Date" },
    //                     editorType: 'dxDateBox',
    //                     editorOptions: { 
    //                         value: datenow,
    //                         displayFormat: "dd-MM-yyyy",
    //                         readOnly: true
    //                     } 
    //                 },
    //                 {
    //                     dataField: 'CreatedBy',
    //                     label: {text: 'Created By'},
    //                     editorType: 'dxSelectBox',
    //                     editorOptions: {
    //                         dataSource: listOption('/list-employee','id','fullname'),
    //                         valueExpr: 'id',
    //                         displayExpr: 'fullname',
    //                         searchEnabled: false,
    //                         readOnly: true
    //                     },
    //                 },
    //                 {
    //                     dataField: "employee_id",
    //                     label: {text: 'Create for Employee'},
    //                     editorType: "dxDropDownBox",
    //                     editorOptions: {
    //                         dataSource: listOption('/list-employeesamedept','id','fullname'),
    //                         displayExpr: "fullname",
    //                         valueExpr: "id",
    //                         placeholder: "Select an employee...",
    //                         contentTemplate: function(e) {

    //                             $("<div>").dxDataGrid({
    //                                 dataSource: e.component.getDataSource(),
    //                                 columns: [
    //                                     { dataField: "fullname", caption: "Full Name" },
    //                                     { dataField: "companycode", caption: "BU" },
    //                                     { dataField: "departmentname", caption: "Department" }
    //                                 ],
    //                                 focusedRowEnabled: true,
	// 								focusedRowKey: e.component.option("value"),
    //                                 height: "100%",
    //                                 width: "100%",
    //                                 selection: {
    //                                     mode: "single"
    //                                 },
    //                                 searchPanel: {
    //                                     visible: true,
    //                                     width: 265,
    //                                     placeholder: "Search..."
    //                                 },
    //                                 onSelectionChanged: function(selectedItems) {
    //                                     const keys = selectedItems.selectedRowKeys;
    //                                     const hasSelection = keys.length;
    //                                     e.component.option('value', hasSelection ? keys[0] : null);
    //                                     e.component.close();
    //                                 }
    //                             }).appendTo(e.component.content());
    //                         }
    //                     },
    //                     validationRules: [{
    //                         type: "required",
    //                         message: "Please select employee"
    //                     }]
    //                 }
    //             ],
    //         }, 
           
    //     ],
            
    //     }),
        
        
    // );

    // scrollView.append('<button type="button" onClick="btnreqdraft()" class="btn btn-secondary waves-effect btn-label waves-light m-1"><i class="bx bx-hourglass label-icon "></i> Save As Draft</button>'),
    scrollView.append('<button type="button" onClick="btnreqsubmit()" class="btn btn-success waves-effect btn-label waves-light m-1"><i class="bx bx-check-double label-icon"></i> Submit</button>'),
    scrollView.append('<button type="button" onClick="btnreqcancel()" class="btn btn-danger waves-effect btn-label waves-light m-1"><i class="bx bx-block label-icon "></i> Cancel</button>'),
    scrollView.append("<hr>"),

    scrollView.dxScrollView({
        width: '100%',
        height: '100%',
    })

    return scrollView;

};

const popup = $('#popup').dxPopup({
    contentTemplate: popupContentTemplate,
    container: '.content',
    showTitle: true,
    title: 'Submission Detail',
    visible: false,
    dragEnabled: false,
    hideOnOutsideClick: false,
    showCloseButton: true,
    fullScreen : true,
    onHidden: function(e) {
        dataGrid.refresh();
    }

}).dxPopup('instance');