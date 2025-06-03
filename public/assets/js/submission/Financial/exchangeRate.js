var modname = 'exchange_rate';

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
    filterRow: { visible: false },
    filterPanel: { visible: false },
    headerFilter: { visible: false },
    searchPanel: {
        visible: false,
        width: 240,
        placeholder: 'Search...',
    },
    editing: {
        useIcons:true,
        mode: "row",
        allowAdding: false,
        allowUpdating: true,
        allowDeleting: false,
    },
    scrolling: {
        mode: "virtual"
    },
    columns: [
        { 
            dataField: "rate",
            dataType: 'number',
            format: "fixedPoint",
            editorOptions: {
                format: "fixedPoint",
            },
            validationRules: [{ type: "required" }],
        },
		{ 
			dataField: "currency",
            editorOptions : {
                readOnly : true
            }
        },
    ],
    export: {
        enabled: false,
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