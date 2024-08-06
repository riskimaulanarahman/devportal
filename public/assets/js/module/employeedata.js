var modname = 'employeedata';
var modelclass = 'AD';
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
    // columnsAutoWidth: true,
    columnMinWidth: 150,
    columnHidingEnabled: false,
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
    columnFixing: {
      enabled: true,
    },
    editing: {
        useIcons:true,
        mode: "batch",
        allowAdding: true,
        // allowUpdating: (admin == 1 || developer) ? true : false,
        allowUpdating: true,
        allowDeleting: true,
    },
    scrolling: {
        mode: "virtual"
    },
    sorting: {
        mode: 'multiple',
    },
    pager: {
        visible: true,
        showInfo: true,
      },
    columns: [
        // {
        //     caption: 'Create AD',
        //     fixed: true,
        //     width: 100,
        //     cellTemplate: function(container, options) {

        //         var reqid = options.data.id;
        //         $('<button class="btn btn-success" id="btnreqid'+reqid+'"><i class="fa fa-upload"></i></button>').on('dxclick', function(evt) {
        //             evt.stopPropagation();
                
                    
        //             alert('Create and Send AD Submission')

        //         }).appendTo(container);
        //     }
        // },
        // {
        //     caption: 'Terminate',
        //     fixed: true,
        //     width: 100,
        //     cellTemplate: function(container, options) {

        //         var reqid = options.data.id;
        //         $('<button class="btn btn-danger" id="btnreqid'+reqid+'" style="margin-left: 3px;"><i class="fa fa-times"></i></button>').on('dxclick', function(evt) {
        //             evt.stopPropagation();
                
                    
        //             alert('Terminate')

        //         }).appendTo(container);
        //     }
        // },
        // {
        //     dataField: "id",
        //     fixed: true,
        //     validationRules: [{ type: "required" }]
        // },
        {
            dataField: "sys_id",
            dataType: "string",
            fixed: true,
            editorOptions: { 
                readOnly: true
            },
        },
        {
            dataField: "LoginName",
            dataType: "string",
            fixed: true,
            visible: (admin == 1) ? true : false,
            editorOptions: { 
                readOnly: (admin == 1) ? true : false
            },
        },
        {
            dataField: "SAPID",
            dataType: "string",
            fixed: false,
            validationRules: [{ type: "required" }]
        },
        {
            dataField: "FullName",
            sortOrder: "asc",
            dataType: "string",
            validationRules: [{ type: "required" }]
        },
        
        { 
            dataField: "company_id",
            caption: "BU",
            sortOrder: "asc",
            lookup: {
                dataSource: listOption('/list-company','id','CompanyCode'),  
                valueExpr: 'id',
                displayExpr: 'CompanyCode',
            },
            validationRules: [{ type: "required" }]
        },
        {
            dataField: "department_id",
            caption: "Department",
            width: 250,
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
            width: 250,
            lookup: {
                dataSource: listOption('/list-designation','id','DesignationName'),  
                valueExpr: 'id',
                displayExpr: 'DesignationName',
            },
            validationRules: [{ type: "required" }]
        },
        { 
            dataField: "location_id",
            caption: "Location",
            lookup: {
                dataSource: listOption('/list-location','id','location'),  
                valueExpr: 'id',
                displayExpr: 'Location',
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
            validationRules: [{ type: "required" }]
        },
        {
            dataField: "BirthOfDate",
            dataType: "date",
            format: "dd-MM-yyyy",
            validationRules: [{ type: "required" }]
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
        {
            dataField: 'CostCenter',
            validationRules: [{ type: "required" }]
        },
        {
            caption: 'Department Head',
            dataField: 'deptheadName',
            lookup: {
                dataSource: listOption('/list-employeeall','id','fullname'),  
                valueExpr: 'fullname',
                displayExpr: 'fullname',
            },
            validationRules: [{ type: "required" }]
        },
    ],
    onEditorPreparing: function (e) {
        if ((e.dataField == "deptheadName") && e.parentType == "dataRow") {
            e.editorName = "dxDropDownBox";                
            e.editorOptions.dropDownOptions = {                
                height: 500,
                width: 600
            };
            e.editorOptions.contentTemplate = function (args, container) {
                console.log(args)

                var value = args.component.option("value"),
                    $dataGrid = $("<div>").dxDataGrid({
                        width: '100%',
                        dataSource: args.component.option("dataSource"),
                        keyExpr: "id",
                        columns: ["sys_id","sapid","companycode","fullname","departmentname","levels"],
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
                            // const keys = selectedItems.selectedRowKeys;
                            const datas = selectedItems.selectedRowsData;
                            // console.log(datas)   
                            // console.log(keys)   
                            const hasSelection = datas.length;
                            // args.component.option('value', hasSelection ? datas[0].fullname : null);
                            if(hasSelection !== 0) {
                                args.component.option('value', datas[0].fullname);
                                args.component.close();
                            }
                        }
                    });
                console.log(value)
                var dataGrid = $dataGrid.dxDataGrid("instance");

                args.component.on("valueChanged", function (args) {
                    var value = args.value;
                    // var value = args.previousValue;


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
        if (e.dataField == "department_id" && e.parentType == "dataRow") {
            e.editorName = "dxDropDownBox";                
            e.editorOptions.dropDownOptions = {                
                height: 500,
                width: 600
            };
            e.editorOptions.contentTemplate = function (args, container) {

                var value = args.component.option("value"),
                    $dataGrid = $("<div>").dxDataGrid({
                        width: '100%',
                        // dataSource: args.component.option("dataSource"),
                        dataSource: store('department'),
                        keyExpr: "id",
                        columns: ["SAPCode","DepartmentName","DepartmentGroup"],
                        hoverStateEnabled: true,
                        editing: {
                            useIcons:true,
                            mode: "row",
                            allowAdding: true,
                            allowUpdating: true,
                            allowDeleting: true,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        pager: {
                            visible: true,
                            showInfo: true,
                        },
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
        if (e.dataField == "designation_id" && e.parentType == "dataRow") {
            e.editorName = "dxDropDownBox";                
            e.editorOptions.dropDownOptions = {                
                height: 500,
                width: 600
            };
            e.editorOptions.contentTemplate = function (args, container) {

                var value = args.component.option("value"),
                    $dataGrid = $("<div>").dxDataGrid({
                        width: '100%',
                        dataSource: store('position'),
                        keyExpr: "id",
                        columns: ["SAPCode","DesignationName"],
                        hoverStateEnabled: true,
                        editing: {
                            useIcons:true,
                            mode: "row",
                            allowAdding: true,
                            allowUpdating: true,
                            allowDeleting: true,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        pager: {
                            visible: true,
                            showInfo: true,
                        },
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
        if (e.dataField == "location_id" && e.parentType == "dataRow") {
            e.editorName = "dxDropDownBox";                
            e.editorOptions.dropDownOptions = {                
                height: 500,
                width: 600
            };
            e.editorOptions.contentTemplate = function (args, container) {

                var value = args.component.option("value"),
                    $dataGrid = $("<div>").dxDataGrid({
                        width: '100%',
                        dataSource: store('location'),
                        keyExpr: "id",
                        columns: ["SAPCode","Location"],
                        hoverStateEnabled: true,
                        editing: {
                            useIcons:true,
                            mode: "row",
                            allowAdding: true,
                            allowUpdating: true,
                            allowDeleting: true,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        pager: {
                            visible: true,
                            showInfo: true,
                        },
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
        if (e.dataField == "company_id" && e.parentType == "dataRow") {
            e.editorName = "dxDropDownBox";                
            e.editorOptions.dropDownOptions = {                
                height: 500,
                width: 600
            };
            e.editorOptions.contentTemplate = function (args, container) {

                var value = args.component.option("value"),
                    $dataGrid = $("<div>").dxDataGrid({
                        width: '100%',
                        dataSource: store('company'),
                        keyExpr: "id",
                        columns: ["SAPCode","CompanyCode"],
                        hoverStateEnabled: true,
                        editing: {
                            useIcons:true,
                            mode: "row",
                            allowAdding: true,
                            allowUpdating: true,
                            allowDeleting: true,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        pager: {
                            visible: true,
                            showInfo: true,
                        },
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
    onDataErrorOccurred: function(e) {
        // Menampilkan pesan kesalahan
        console.log("Terjadi kesalahan saat memuat data (0):", e.error.message);

        // Memuat ulang Page
        location.reload();
    }
}).dxDataGrid("instance");


var dataGrid = $("#loghistory").dxDataGrid({    
    dataSource: store('logsuccess'),
    allowColumnReordering: false,
    allowColumnResizing: true,
    columnsAutoWidth: true,
    // columnMinWidth: 150,
    columnHidingEnabled: false,
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
    columnFixing: {
      enabled: true,
    },
    editing: {
        useIcons:true,
        mode: "batch",
        allowAdding: false,
        allowUpdating: false,
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
        showInfo: true,
      },
    columns: [
        'id','user','url','action','values','created_at'
    ],
    export: {
        enabled: true,
        fileName: 'log history',
        excelFilterEnabled: true,
        allowExportSelectedData: false
    },
    onContentReady: function(e){
        moveEditColumnToLeft(e.component);
    },
    onToolbarPreparing: function(e) {
        dataGridlog = e.component;

        e.toolbarOptions.items.unshift({						
            location: "after",
            widget: "dxButton",
            options: {
                hint: "Refresh Data",
                icon: "refresh",
                onClick: function() {
                    dataGridlog.refresh();
                }
            }
        })
    },
}).dxDataGrid("instance");