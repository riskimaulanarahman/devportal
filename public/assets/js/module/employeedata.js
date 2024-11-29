var modname = 'employeedata';
var modelclass = 'ActiveDirectory';
var popupmode;

function moveEditColumnToLeft(dataGrid) {
    dataGrid.columnOption("command:edit", { 
        visibleIndex: -1,
        width: 80 
    });
}

function delay(ms) {
    var deferred = $.Deferred();
    setTimeout(deferred.resolve, ms);
    return deferred.promise();
}

checkUserAccess(modname, usersid).then(permissions => {
    var dataGrid = $("#gridContainer").dxDataGrid({    
        dataSource: store(modname),
        allowColumnReordering: true,
        allowColumnResizing: true,
        columnMinWidth: 80,
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
            mode: "popup",
            allowAdding: (admin == 1) ? true : permissions.allowAdd,
            allowUpdating: (admin == 1) ? true : permissions.allowEdit,
            allowDeleting: (admin == 1) ? true : permissions.allowDelete,
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
            {
                caption: 'AD',
                fixed: true,
                width: 80,
                visible: (permissions.allowAction == 1 || admin == 1) ? true : false,
                formItem: {
                    visible: false
                },
                cellTemplate: function(container, options) {

                    var reqid = options.data.id;
                    var isad = options.data.isAD;
                    if((options.data.LoginName == null || options.data.LoginName == '') && isad !== 1) {
                        $('<button class="btn btn-xs btn-success" id="btnreqid'+reqid+'"><i class="fa fa-upload"></i></button>').on('dxclick', function(evt) {
                            evt.stopPropagation();
                            runpopup(options,1); // 1 create

                            popup.option({
                                contentTemplate: () => popupContentTemplate(reqid,options),
                            });
                            popup.show();
                            
                        }).appendTo(container);
                    }

                }
            },
            {
                caption: 'Terminate',
                fixed: true,
                width: 80,
                visible: (permissions.allowAction == 1 || admin == 1) ? true : false,
                formItem: {
                    visible: false
                },
                cellTemplate: function(container, options) {

                    var reqid = options.data.id;
                    var isad = options.data.isAD;
                    if(options.data.LoginName && (isad !== 1)) {
                        $('<button class="btn btn-xs btn-danger" id="btnreqid'+reqid+'" style="margin-left: 3px;"><i class="fa fa-times"></i></button>').on('dxclick', function(evt) {
                            evt.stopPropagation();
                            runpopup(options,2); // 2 delete

                            popup.option({
                                contentTemplate: () => popupContentTemplate(reqid,options),
                            });
                            popup.show();
                            
                        }).appendTo(container);
                    }
                }
            },
            {
                dataField: "sys_id",
                dataType: "string",
                fixed: true,
                width: 150,
                editorOptions: { 
                    readOnly: true
                },
            },
            {
                dataField: "LoginName",
                dataType: "string",
                fixed: true,
                visible: (admin == 1 || permissions.allowAction == 1) ? true : false,
                width: 150,
                formItem: {
                    visible: (admin == 1) ? true : false
                },
                editorOptions: { 
                    readOnly: (admin == 1) ? false : true
                },
            },
            {
                dataField: "SAPID",
                dataType: "string",
                fixed: false,
                width: 100,
                validationRules: [{ type: "required" }]
            },
            {
                dataField: "FullName",
                sortOrder: "asc",
                dataType: "string",
                width: 150,
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
                width: 150,
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
                dataField: "designation.SAPCode",
                caption: "Position No",
                width: 250,
                editorOptions: { 
                    readOnly: true,
                },
                formItem: {
                    visible: false
                },
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
                width: 150,
                validationRules: [{ type: "required" }]
            },
            {
                dataField: "BirthOfDate",
                dataType: "date",
                format: "dd-MM-yyyy",
                width: 150,
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
                caption: 'Superior',
                dataField: 'superiorName',
                lookup: {
                    dataSource: listOption('/list-employeeall','id','fullname'),  
                    valueExpr: 'fullname',
                    displayExpr: 'fullname',
                },
                width: 150,
            },
            {
                caption: 'Department Head',
                dataField: 'deptheadName',
                lookup: {
                    dataSource: listOption('/list-employeeall','id','fullname'),  
                    valueExpr: 'fullname',
                    displayExpr: 'fullname',
                },
                width: 150,
                validationRules: [{ type: "required" }]
            },
            {
                dataField: 'isActive',
                dataType: 'boolean',
                visible: (admin == 1) ? true : false
            },
        ],
        onEditorPreparing: function (e) {
            if ((e.dataField == "superiorName") && e.parentType == "dataRow") {
                e.editorName = "dxDropDownBox";                
                e.editorOptions.dropDownOptions = {                
                    height: 500,
                    width: 600
                };
                e.editorOptions.contentTemplate = function (args, container) {
                    // console.log(args)

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
                                const datas = selectedItems.selectedRowsData;
                                const hasSelection = datas.length;
                                if(hasSelection !== 0) {
                                    args.component.option('value', datas[0].fullname);
                                    args.component.close();
                                }
                            }
                        });
                    // console.log(value)
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
            if ((e.dataField == "deptheadName") && e.parentType == "dataRow") {
                e.editorName = "dxDropDownBox";                
                e.editorOptions.dropDownOptions = {                
                    height: 500,
                    width: 600
                };
                e.editorOptions.contentTemplate = function (args, container) {
                    // console.log(args)

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
                                const datas = selectedItems.selectedRowsData;
                                const hasSelection = datas.length;
                                if(hasSelection !== 0) {
                                    args.component.option('value', datas[0].fullname);
                                    args.component.close();
                                }
                            }
                        });
                    // console.log(value)
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

    function runpopup(options,mode) {
        var mode = (mode == 1) ? "Create" : "Delete";
        popup = $('#popup').dxPopup({
            contentTemplate: popupContentTemplate,
            width: 600,
            height: 400,
            container: '.content',
            showTitle: true,
            title: 'PIC',
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
                        text: 'Submit',
                        onClick: function () {
                            const selectedValue = $('#dropdown').dxDropDownBox("option", "value");
                            if(selectedValue == null) {
                                DevExpress.ui.dialog.alert("Please Select an Employee", "error");
                                return false;
                            }

                            var result = confirm('Are you sure you want to '+mode+' Active Directory "'+options.data.FullName+'" and send this submission ?');

                                if (result) {
                                    showLoadingScreen();
                                    // First request with a delay
                                    delay(1000).then(function() {
                                        return sendRequest(apiurl + "/adrequest", "POST", {
                                            employee_id: options.data.id,
                                            pic_empid: selectedValue,
                                            requestType: mode+' Account'
                                        });
                                    }).then(function(response) {
                                        const dataid = response.data.id;
                                        // Second request with a delay
                                        return delay(2000).then(function() {
                                            return sendRequest(apiurl + "/submissionrequest/" + dataid + "/" + modelclass, "POST", {
                                                requestStatus: 1,
                                                action: 'submission',
                                                approvalAction: 1,
                                                approvalType: null,
                                                remarks: null
                                            });
                                        });
                                    }).then(function(response) {
                                        if (response.status != 'error') {
                                            dataGrid.refresh();
                                        }
                                        hideLoadingScreen();
                                    }).fail(function(error) {
                                        console.error("An error occurred:", error);
                                        hideLoadingScreen();
                                    });
                                } else {
                                    alert('Cancelled.');
                                }

                            popup.hide();

                        },
                    },
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
            }]
    
        }).dxPopup('instance');
    }
    
    const popupContentTemplate = function (reqid,options) {
    
        const scrollView = $('<div />');
    
        scrollView.append("<hr>");

         // Menambahkan dropdown
         const dropdown = $('<div id="dropdown"></div>').appendTo(scrollView);

         // Mengambil data dari fungsi listOption
        const employeeOptions = listOption("/list-employeead", "id", "fullname");

         // Inisialisasi dropdown
         dropdown.dxDropDownBox({
             value: null,
             dataSource: employeeOptions.store,
             displayExpr(item) {
                return item && `${item.fullname}`;
            },
             contentTemplate: function (args, container) {
                 const $dataGrid = $("<div>").dxDataGrid({
                     width: '100%',
                     dataSource: args.component.option("dataSource"),
                     keyExpr: "id",
                     columns: ["sapid", "fullname", "companycode", "departmentname"],
                     hoverStateEnabled: true,
                     paging: { enabled: true, pageSize: 10 },
                     filterRow: { visible: true },
                     height: '90%',
                     showRowLines: true,
                     showBorders: true,
                     selection: { mode: "single" },
                     searchPanel: {
                         visible: true,
                         width: 265,
                         placeholder: "Search..."
                     },
                     onSelectionChanged: function (selectedItems) {
                         const keys = selectedItems.selectedRowKeys;
                         const hasSelection = keys.length;
                         args.component.option('value', hasSelection ? keys[0] : null);
                         if (hasSelection) {
                            args.component.close();
                         }
                     }
                 });
 
                 const dataGrid = $dataGrid.dxDataGrid("instance");
 
                 args.component.on("valueChanged", function (args) {
                     const value = args.value;
                     dataGrid.selectRows(value, false);
                 });
 
                 container.append($dataGrid);
                 $("<div>").dxButton({
                     text: "Close",
                     onClick: function () {
                         args.component.close();
                     }
                 }).css({ float: "right", marginTop: "10px" }).appendTo(container);
 
                 return container;
             },
             dropDownOptions: {
                 height: 550,
                 width: 900
             }
         });

        scrollView.dxScrollView({
            width: '100%',
            height: '100%',
        })
    
        return scrollView;
    
    };
});
var dataGridhistory = $("#loghistory").dxDataGrid({    
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

