var modname = 'approvaluser';

function moveEditColumnToLeft(dataGrid) {
    dataGrid.columnOption("command:edit", { 
        visibleIndex: -1,
        width: 80 
    });
}


// var typeData = sendRequest('/list-approvaltype','POST','').then(function(resp) {
//     return resp;
// })

// async function getTypeData() {
//     const resp = await sendRequest('/api/list-approvaltype', 'POST', '');
//     return resp;
//   }
  
// async function getTypeDatxa() {
//   const typeData = await getTypeData();
//   return typeData;
// }

// async function getTypeData() {
//   const resp = await sendRequest('/api/list-approvaltype', 'POST', '');
//   return resp; // nilai yang dikembalikan harus berupa sebuah array
// }

// var getTypeData;
// sendRequest('/api/list-approvaltype','POST','').then(function(itemsData) {
//     getTypeData = itemsData;
// })

function getData() {
    return sendRequest(apiurl+'/list-approvaltype','POST','');
  }

getData().then(function(getTypeData) {
    var itemsData = getTypeData.items;
    processData(itemsData);
});

function processData(itemsData) {

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
        selection: {
            mode: 'multiple',
            recursive: true,
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
                dataField: "module",
                sortOrder: "asc",
                lookup: {
                    dataSource: listOption('/list-module','id','module'),  
                    valueExpr: 'module',
                    displayExpr: 'module',
                },
            },
            {
                caption: "Approval Type",
                dataField: "approvaltype_id",
                lookup: {
                    dataSource: function (options) {
                        return {
                            store: {
                                type: 'array',
                                data: itemsData
                            },
                            filter: options.data ? ["Module", "=", options.data.module] : null
                        };
                    },
                    valueExpr: 'id',
                    displayExpr: 'ApprovalType',
                },
                validationRules: [
                    { 
                        type: "required" 
                    }
                ]
            },
            {
                caption: "Employee",
                dataField: "employee_id",
                lookup: {
                    dataSource: listOption('/list-employee','id','fullname'),  
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
                dataField: "sequence",
                validationRules: [
                    { 
                        type: "required" 
                    }
                ]
            },
            {
                dataField: "companylist",
                caption: "Company"
            }, 
            {
                caption: "Category",
                dataField: "category_id",
                width: 300,
                // editorType: "dxDropDownBox",
                // lookup: {
                //     dataSource: listOption('/list-categoryform','id','nameCategory'),  
                //     valueExpr: 'id',
                //     displayExpr: 'nameCategory',
                // },
            },
            { 
                dataField: "isFinal",
                dataType: "boolean"
            }, 
            { 
                dataField: "isActive",
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
                                console.log(keys)
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
            if (e.dataField == "category_id" && e.parentType == "dataRow") {
                e.editorName = "dxDropDownBox";                
                e.editorType = "dxDropDownBox";                
                e.editorOptions.dropDownOptions = {                
                    height: 500,
                    width: 600
                };
                e.editorOptions.dataSource = listOption('/list-categoryform','id','nameCategory');
                e.editorOptions.valueExpr = 'id',
                e.editorOptions.displayExpr = 'nameCategory',
                e.editorOptions.searchEnabled = true,
                e.editorOptions.contentTemplate = function (args, container) {
    
                    var value = args.component.option("value"),
                        $dataGrid = $("<div>").dxDataGrid({
                            width: '100%',
                            dataSource: args.component.option("dataSource"),
                            keyExpr: "id",
                            columns: ["module.module","nameCategory"],
                            // hoverStateEnabled: true,
                            paging: { enabled: true, pageSize: 10 },
                            filterRow: { visible: true },
                            height: '90%',
                            showRowLines: true,
                            showBorders: true,
                            selection: { mode: "multiple" },
                            // selectedRowKeys: value,
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
                                args.component.option('value', hasSelection ? keys : null);
                                // args.component.option('value',keys);
                                console.log(keys)
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
    }).dxDataGrid("instance");
}

processData();