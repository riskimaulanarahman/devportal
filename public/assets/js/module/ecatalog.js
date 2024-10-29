var modname = 'ecatalog';

function moveEditColumnToLeft(dataGrid) {
    dataGrid.columnOption("command:edit", { 
        visibleIndex: -1,
        width: 80 
    });
}

buyers = [
    {"pg": "P34", "name": "Erliana"},
    {"pg": "P38", "name": "Jimmy Sirait"},
    {"pg": "P49", "name": "Mandala Putra Tan"},
    {"pg": "P51", "name": "Roy Syahrizal"},
    {"pg": "P52", "name": "Naftalia Silaban"},
    {"pg": "P53", "name": "Muhammad Maulana Zarkasyi"},
    {"pg": "P54", "name": "Jeheskiel Pinem"},
    {"pg": "P57", "name": "Wahyu Prasetiyo"}
];

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
    pager: {
        visible: true,
        showInfo: true,
    },
    columns: [
        { 
			dataField: "materialCode",
            validationRules: [{ type: "required" }]
        },
        { 
            dataField: "category",
            validationRules: [{ type: "required" }]
        },
        { 
            dataField: "description",
            sortOrder: "asc",
            validationRules: [{ type: "required" }]
        },
        { 
            dataField: "part_number",
            dataType: 'number',
        },
        { 
            dataField: "brand",
        },
        { 
            dataField: "uom",
        },
        { 
            dataField: "historicalPrice",
            dataType: 'number',
            format: "fixedPoint",
            editorOptions: {
                format: "fixedPoint",
            },
            validationRules: [{ type: "required" }]
        },
        { 
            dataField: "currency",
            lookup: {
                dataSource: ['IDR','USD'],  
            },
        },
        { 
            dataField: "lastUpdated",
            dataType: "date",
            format: "dd-MM-yyyy", 
            validationRules: [{ type: "required" }]
        },
        { 
            dataField: "pg",
            lookup: {
                dataSource: listOption('/list-pg','id','code'),  
                valueExpr: 'code',
                displayExpr: 'code',
            },
            validationRules: [{ type: "required" }]
        },
        { 
            dataField: "buyer_name",
            editorOptions: {
                readOnly: true
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
    onEditorPreparing: function (e) {
        if (e.dataField == "pg" && e.parentType == "dataRow") {
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
                        keyExpr: "code",
                        columns: ["employee.FullName","code"],
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
                            const datas = selectedItems.selectedRowsData;
                            const hasSelection = datas.length;
                            if(hasSelection !== 0) {
                                args.component.option('value', datas[0].code);
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