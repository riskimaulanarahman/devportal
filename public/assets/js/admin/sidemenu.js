var modname = 'sidemenu';

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
			dataField: "title",
            sortOrder: "asc",
            validationRules: [
                { 
                    type: "required" 
                }
            ]
        },
        {
			dataField: "route",
            validationRules: [
                { 
                    type: "required" 
                }
            ]
        },
        { 
			dataField: "modules",
            lookup: {
                dataSource: listOption('/list-module','id','module'),  
                valueExpr: 'id',
                displayExpr: 'module',
            },
        },
        {
            dataField: "icon_id",
            caption: "Icons",
            lookup: {
                dataSource: listOption('/list-icon','id','name'),  
                valueExpr: 'id',
                displayExpr: 'name',
            },
            validationRules: [
                { 
                    type: "required" 
                }
            ]
        },
        {
            dataField: "parent_id",
            caption: "Parents",
            lookup: {
                dataSource: listOption('/list-sidemenu','id','title'),  
                valueExpr: 'id',
                displayExpr: 'title',
            },
        },
        {
            dataField: "sequence_id",
            caption: "Sequence",
            lookup: {
                dataSource: listOption('/list-sequence','id','title'),  
                valueExpr: 'id',
                displayExpr: 'title',
            },
            validationRules: [
                { 
                    type: "required" 
                }
            ]
        },
        {
			dataField: "is_active",
            dataType: "boolean"
        },
        {
			dataField: "is_admin",
            dataType: "boolean"
        },
        {
			dataField: "is_parent",
            dataType: "boolean"
        },
        {
			dataField: "is_secondary_menu",
            dataType: "boolean"
        },
        {
			dataField: "must_full_title",
            dataType: "boolean"
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