var modname = 'employeedata';

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
    columnsAutoWidth: true,
    columnHidingEnabled: false,
    rowAlternationEnabled: true,
    wordWrapEnabled: true,
    showBorders: true,
    columnWidth: 150,
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
        allowUpdating: (admin == 1 || developer) ? true : false,
        allowDeleting: false,
    },
    scrolling: {
        mode: "virtual"
    },
    sorting: {
        mode: 'multiple',
    },
    pager: {
        visible: true,
        // allowedPageSizes: [5, 10, 'all'],
        // showPageSizeSelector: true,
        showInfo: true,
        // showNavigationButtons: true,
      },
    columns: [
        // {
        //     caption: 'Action',
        //     width: 160,
        //     formItem: {visible:false},
        //     cellTemplate: function(container, options) {

        //         var reqid = options.data.id;
    
        //         $('<button class="btn btn-primary" id="btnreqid'+reqid+'"><i class="fa fa-history"></i></button>').on('dxclick', function(evt) {
        //             evt.stopPropagation();
        //                     popup.option({
        //                         contentTemplate: () => popupContentTemplate(reqid),
        //                     });
        //                     popup.show();
        //                     // alert('show data history : '+reqid)

        //         }).appendTo(container);
        //         // $('<button class="btn btn-danger" id="btnreqid'+reqid+'" style="margin-left: 5px;"><i class="fa fa-people-arrows"></i></button>').on('dxclick', function(evt) {
        //         //     evt.stopPropagation();
        //         //             alert('Show Popup Mutasi/Promosi : '+reqid)

        //         // }).appendTo(container);
            
        //     }
        // },
        // {
        //     dataField: "oldsap",
        //     dataType: "string",
        // },
        { 
            dataField: "company_id",
            caption: "BU",
            sortOrder: "asc",
            lookup: {
                dataSource: listOption('/list-company','id','companycode'),  
                valueExpr: 'id',
                displayExpr: 'companycode',
            },
            width: 100,
            validationRules: [{ type: "required" }]
        },
        {
            dataField: "SAPID",
            dataType: "string",
            validationRules: [{ type: "required" }]

        },
        {
            dataField: "FullName",
            sortOrder: "asc",
            dataType: "string",
            validationRules: [{ type: "required" }]
        },
        
        {
            dataField: "department_id",
            caption: "Department",
            width: 450,
            lookup: {
                dataSource: listOption('/list-department','id','DepartmentName'),
                displayExpr: "DepartmentName",
                valueExpr: "id",
            },
            validationRules: [{ type: "required" }]
        },
        { 
            dataField: "designation_id",
            caption: "Position",
            width: 450,
            lookup: {
                dataSource: listOption('/list-designation','id','DesignationName'),  
                valueExpr: 'id',
                displayExpr: 'DesignationName',
            },
            // validationRules: [{ type: "required" }]
        },
        { 
            dataField: "location_id",
            caption: "Location",
            lookup: {
                dataSource: listOption('/list-location','id','location'),  
                valueExpr: 'id',
                displayExpr: 'location',
            },
            validationRules: [{ type: "required" }]
        },
        {
            dataField: 'level_id',
            caption: "Level",
            lookup: {
                dataSource: listOption('/list-level','id','level'),  
                valueExpr: 'id',
                displayExpr: 'level',
            },
            validationRules: [{ type: "required" }]
        },
        {
            dataField: "JoinDate",
            caption: "Join Date",
            dataType: "date",
            format: "dd-MM-yyyy",
            // validationRules: [{ type: "required" }]
        },
        {
            dataField: 'LoginName',
            visible: (admin == 1 || developer ) ? true : false,
        },
        {
            dataField: 'CostCenter',
            // validationRules: [{ type: "required" }]
        },
        {
            dataField: 'isInternationalStaff',
            caption: "IS ?",
            lookup: {
                dataSource: [{id:1,value:'Yes'},{id:0,value:'No'}],
                valueExpr: 'id',
                displayExpr: 'value',
                searchEnabled: false
            },
            validationRules: [{ type: "required" }]
        },
        // {
        //     dataField: 'HIPO_MIPO',
        //     caption: "HIPO/MIPO",
        //     lookup: {
        //         dataSource: ['','HIPO','MIPO'],
        //         searchEnabled: false
        //     },
        // },
        
        // {
        //     dataField: 'status',
        //     lookup: {
        //         dataSource: ['Permanent','Contract','Terminated'],
        //         searchEnabled: false
        //     },
        //     validationRules: [{ type: "required" }]
        // },
        // {
        //     dataField: 'reasonStatus',
        //     caption: "Reason of Status",
        // },
        // {
        //     dataField: 'E_AT_MT',
        //     caption: "E/AT/MT",
        //     lookup: {
        //         dataSource: ['E','AT','MT'],
        //         searchEnabled: false
        //     },
        //     validationRules: [{ type: "required" }]
        // },
        // {
        //     dataField: "dob",
        //     caption: "DoB",
        //     dataType: "date",
        //     format: "dd-MM-yyyy",
        //     validationRules: [{ type: "required" }]
        // },
        // {
        //     dataField: 'age',
        //     editorType: 'dxNumberBox',
        //     editorOptions: {
        //         disabled : true
        //     },
        // },
        // {
        //     dataField: 'rangeAge',
        //     editorOptions: {
        //         disabled : true
        //     },
        // },
        // {
        //     dataField: "doh",
        //     caption: "DoH",
        //     dataType: "date",
        //     format: "dd-MM-yyyy",
        //     validationRules: [{ type: "required" }]
        // },
        // {
        //     dataField: 'los',
        //     caption: "LOS",
        //     editorType: 'dxNumberBox',
        //     editorOptions: {
        //         disabled : true
        //     },
        // },
        // {
        //     dataField: 'losSVC',
        //     caption: "LOS Category",
        //     editorOptions: {
        //         disabled : true
        //     },
        // },
        
       
        
        
        // {
        //     dataField: 'grade_id',
        //     caption: "Grade",
        //     lookup: {
        //         dataSource: listOption('/list-grade','id','grade'),  
        //         valueExpr: 'id',
        //         displayExpr: 'grade',
        //     },
        //     validationRules: [{ type: "required" }]
        // },
        
        // {
        //     dataField: "lastPromoDate",
        //     caption: "Last Promo Date",
        //     dataType: "date",
        //     format: "dd-MM-yyyy",
        // },
        // {
        //     dataField: 'TD_NTD',
        //     caption: "TD/NTD",
        //     lookup: {
        //         dataSource: ['TD','NTD'],
        //         searchEnabled: false
        //     },
        //     validationRules: [{ type: "required" }]
        // },
        // {
        //     dataField: 'PA',
        //     lookup: {
        //         dataSource: [1,2,3,4,5],
        //         searchEnabled: false
        //     },
        // },
        // {
        //     dataField: 'gender',
        //     lookup: {
        //         dataSource: ['Male','Female'],
        //         searchEnabled: false
        //     },
        //     validationRules: [{ type: "required" }]
        // },
        // {
        //     dataField: 'educationDegree',
        // },
        // {
        //     dataField: 'educationFieldofStudy',
        //     caption: "Education Field Of Study",
        // },
        // {
        //     dataField: 'ethnic',
        //     lookup: {
        //         dataSource: listOption('/list-ethnic','id','ethnicname'),  
        //         valueExpr: 'ethnicname',
        //         displayExpr: 'ethnicname',
        //     },
        // },
        // {
        //     dataField: 'religion',
        //     lookup: {
        //         dataSource: listOption('/list-religion','id','religionname'),  
        //         valueExpr: 'religionname',
        //         displayExpr: 'religionname',
        //     },
        // },
        // {
        //     dataField: 'nationality',
        //     lookup: {
        //         dataSource: listOption('/list-nationality','id','nationalityname'),  
        //         valueExpr: 'nationalityname',
        //         displayExpr: 'nationalityname',
        //     },
        // },
    ],
    onEditorPreparing: function (e) {
        if (e.dataField == "department_id" && e.parentType == "dataRow") {
            e.editorName = "dxDropDownBox";                
            e.editorOptions.dropDownOptions = {                
                height: 500
            };
            e.editorOptions.contentTemplate = function (args, container) {

                var value = args.component.option("value"),
                    $dataGrid = $("<div>").dxDataGrid({
                        width: '100%',
                        dataSource: args.component.option("dataSource"),
                        keyExpr: "id",
                        columns: ["DepartmentName","DepartmentGroup"],
                        // columns: [{dataField:"DepartmentName",width:450},{dataField:"DepartmentGroup",width:100}],
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
                            // args.component.close();
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
        if (e.dataField == "designation_id" && e.parentType == "dataRow") {
            e.editorName = "dxDropDownBox";                
            e.editorOptions.dropDownOptions = {                
                height: 500
            };
            e.editorOptions.contentTemplate = function (args, container) {

                var value = args.component.option("value"),
                    $dataGrid = $("<div>").dxDataGrid({
                        width: '100%',
                        dataSource: args.component.option("dataSource"),
                        keyExpr: "id",
                        columns: ["DesignationName"],
                        // columns: ["DesignationName","SAPCode"],
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
                            // args.component.close();
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

const accordionItems = [
    {
        ID: 1,
        Title: '<i class="fa fa-industry"> Company </i>',
    },
    {
        ID: 2,
        Title: '<i class="fa fa-building"> Department </i>',
    },
    {
        ID: 3,
        Title: '<i class="fa fa-map-pin"> Location </i>',
    },
    {
        ID: 4,
        Title: '<i class="fa fa-briefcase"> Position </i>',
    },
    {
        ID: 5,
        Title: '<i class="fa fa-thumbs-up"> Grade </i>',
    },
    {
        ID: 6,
        Title: '<i class="fa fa-address-card"> Level </i>',
    },
    {
        ID: 7,
        Title: '<i class="fa fa-hands"> Point Appraisal </i>',
    },
];

const popupContentTemplate = function (reqid) {

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
                    return $("<div id='historyhcbu'>").dxDataGrid({    
                        dataSource: storedetail('historyhcbu',reqid),
                        allowColumnReordering: true,
                        allowColumnResizing: true,
                        columnsAutoWidth: true,
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: false,
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            { 
                                dataField: "column",
                                editorOptions: {
                                    readOnly: true,
                                },
                                validationRules: [
                                    { 
                                        type: "required" 
                                    }
                                ]
                            },
                            { 
                                dataField: "value",
                                caption: "BU",
                                lookup: {
                                    dataSource: listOption('/list-company','id','companycode'),  
                                    valueExpr: 'id',
                                    displayExpr: 'companycode',
                                },
                                // validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: "created_at",
                                sortOrder: "asc",
                                caption: "Date",
                                dataType: "date",
                                format: "dd-MM-yyyy",
                            },
                        ],
                        export: {
                            enabled: true,
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
                            e.data.column = 'BU'
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
                            })
                        },
                    })
                } else if(data.ID == 2) {
                    return $("<div id='historyhcdepartment'>").dxDataGrid({    
                        dataSource: storedetail('historyhcdepartment',reqid),
                        allowColumnReordering: true,
                        allowColumnResizing: true,
                        columnsAutoWidth: true,
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: false,
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            { 
                                dataField: "column",
                                editorOptions: {
                                    readOnly: true,
                                },
                                validationRules: [
                                    { 
                                        type: "required" 
                                    }
                                ]
                            },
                            { 
                                dataField: "value",
                                caption: "Department",
                                lookup: {
                                    dataSource: listOption('/list-department','id','DepartmentName'),
                                    displayExpr: "DepartmentName",
                                    valueExpr: "id",
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: "created_at",
                                sortOrder: "asc",
                                caption: "Date",
                                dataType: "date",
                                format: "dd-MM-yyyy",
                            },
                        ],
                        export: {
                            enabled: true,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGrid2 = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onInitNewRow : function(e) {
                            e.data.column = 'Department'
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGrid2.refresh();
                                    }
                                }
                            })
                        },
                    })
                } else if(data.ID == 3) {
                    return $("<div id='historyhclocation'>").dxDataGrid({    
                        dataSource: storedetail('historyhclocation',reqid),
                        allowColumnReordering: true,
                        allowColumnResizing: true,
                        columnsAutoWidth: true,
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: false,
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            { 
                                dataField: "column",
                                editorOptions: {
                                    readOnly: true,
                                },
                                validationRules: [
                                    { 
                                        type: "required" 
                                    }
                                ]
                            },
                            { 
                                dataField: "value",
                                caption: "Location",
                                lookup: {
                                    dataSource: listOption('/list-location','id','location'),  
                                    valueExpr: 'id',
                                    displayExpr: 'location',
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: "created_at",
                                sortOrder: "asc",
                                caption: "Date",
                                dataType: "date",
                                format: "dd-MM-yyyy",
                            },
                        ],
                        export: {
                            enabled: true,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGrid3 = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onInitNewRow : function(e) {
                            e.data.column = 'Location'
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGrid3.refresh();
                                    }
                                }
                            })
                        },
                    })
                } else if(data.ID == 4) {
                    return $("<div id='historyhcposition'>").dxDataGrid({    
                        dataSource: storedetail('historyhcposition',reqid),
                        allowColumnReordering: true,
                        allowColumnResizing: true,
                        columnsAutoWidth: true,
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: false,
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            { 
                                dataField: "column",
                                editorOptions: {
                                    readOnly: true,
                                },
                                validationRules: [
                                    { 
                                        type: "required" 
                                    }
                                ]
                            },
                            { 
                                dataField: "value",
                                caption: "Position",
                                lookup: {
                                    dataSource: listOption('/list-designation','id','DesignationName'),  
                                    valueExpr: 'id',
                                    displayExpr: 'DesignationName',
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: "created_at",
                                sortOrder: "asc",
                                caption: "Date",
                                dataType: "date",
                                format: "dd-MM-yyyy",
                            },
                        ],
                        export: {
                            enabled: true,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGrid4 = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onInitNewRow : function(e) {
                            e.data.column = 'Position'
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGrid4.refresh();
                                    }
                                }
                            })
                        },
                    })
                } else if(data.ID == 5) {
                    return $("<div id='historyhcgrade'>").dxDataGrid({    
                        dataSource: storedetail('historyhcgrade',reqid),
                        allowColumnReordering: true,
                        allowColumnResizing: true,
                        columnsAutoWidth: true,
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: false,
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            { 
                                dataField: "column",
                                editorOptions: {
                                    readOnly: true,
                                },
                                validationRules: [
                                    { 
                                        type: "required" 
                                    }
                                ]
                            },
                            { 
                                dataField: "value",
                                caption: "Grade",
                                lookup: {
                                    dataSource: listOption('/list-grade','id','grade'),  
                                    valueExpr: 'id',
                                    displayExpr: 'grade',
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: "created_at",
                                sortOrder: "asc",
                                caption: "Date",
                                dataType: "date",
                                format: "dd-MM-yyyy",
                            },
                        ],
                        export: {
                            enabled: true,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGrid5 = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onInitNewRow : function(e) {
                            e.data.column = 'Grade'
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGrid5.refresh();
                                    }
                                }
                            })
                        },
                    })
                } else if(data.ID == 6) {
                    return $("<div id='historyhclevel'>").dxDataGrid({    
                        dataSource: storedetail('historyhclevel',reqid),
                        allowColumnReordering: true,
                        allowColumnResizing: true,
                        columnsAutoWidth: true,
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: false,
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            { 
                                dataField: "column",
                                editorOptions: {
                                    readOnly: true,
                                },
                                validationRules: [
                                    { 
                                        type: "required" 
                                    }
                                ]
                            },
                            { 
                                dataField: "value",
                                caption: "Level",
                                lookup: {
                                    dataSource: listOption('/list-level','id','level'),  
                                    valueExpr: 'id',
                                    displayExpr: 'level',
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: "created_at",
                                sortOrder: "asc",
                                caption: "Date",
                                dataType: "date",
                                format: "dd-MM-yyyy",
                            },
                        ],
                        export: {
                            enabled: true,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGrid6 = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onInitNewRow : function(e) {
                            e.data.column = 'Level'
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGrid6.refresh();
                                    }
                                }
                            })
                        },
                    })
                } else if(data.ID == 7) {
                    return $("<div id='historyhcappraisal'>").dxDataGrid({    
                        dataSource: storedetail('historyhcappraisal',reqid),
                        allowColumnReordering: true,
                        allowColumnResizing: true,
                        columnsAutoWidth: true,
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: false,
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            { 
                                dataField: "column",
                                editorOptions: {
                                    readOnly: true,
                                },
                                validationRules: [
                                    { 
                                        type: "required" 
                                    }
                                ]
                            },
                            { 
                                dataField: "value",
                                caption: "Point Appraisal",
                                // lookup: {
                                //     dataSource: listOption('/list-department','id','DepartmentName'),
                                //     displayExpr: "DepartmentName",
                                //     valueExpr: "id",
                                // },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: "created_at",
                                sortOrder: "asc",
                                caption: "Date",
                                dataType: "date",
                                format: "dd-MM-yyyy",
                            },
                        ],
                        export: {
                            enabled: true,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGrid7 = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onInitNewRow : function(e) {
                            e.data.column = 'Appraisal'
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGrid7.refresh();
                                    }
                                }
                            })
                        },
                    })
                }
            }
        })
    
    );
        
    scrollView.append("<hr>"),
    scrollView.append('<button type="button" onClick="btnreqcancel()" class="btn btn-danger waves-effect btn-label waves-light m-1"><i class="bx bx-block label-icon "></i> Cancel</button>'),

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
    title: 'History Employee',
    visible: false,
    dragEnabled: false,
    hideOnOutsideClick: false,
    showCloseButton: true,
    fullScreen : false

}).dxPopup('instance');