var modname = 'purchasinguser';

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
        mode: "batch",
        allowAdding: true,
        allowUpdating: true,
        allowDeleting: true,
    },
    scrolling: {
        mode: "virtual"
    },
    columns: [
        { 
			caption: "Employee Name",
			dataField: "employee_id",
            lookup: {
                dataSource: listOption('/list-employee','id','fullname'),  
                valueExpr: 'id',
                displayExpr: 'fullname',
            },
        },
        { 
            dataField: "code",
        },
        // { 
        //     dataField: "description",
        //     sortOrder: "asc",
        // },
        // { 
        //     dataField: "part_number",
        //     dataType: 'number',
        // },
        // { 
        //     dataField: "brand",
        // },
        // { 
        //     dataField: "uom",
        // },
        // { 
        //     dataField: "historicalPrice",
        //     dataType: 'number',
        //     format: "fixedPoint",
        //     editorOptions: {
        //         format: "fixedPoint",
        //     }
        // },
        // { 
        //     dataField: "lastUpdated",
        //     dataType: "date",
        //     format: "dd-MM-yyyy", 
        // },
        // { 
        //     dataField: "pg",
        //     lookup: { 
        //         dataSource: buyers,  
        //         valueExpr: 'pg',
        //         displayExpr: 'name',
        //     },
        // },
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