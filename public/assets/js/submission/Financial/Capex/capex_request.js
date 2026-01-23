var modname = 'capexrequest';
var modelclass = 'Capex';
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
    columnHidingEnabled: true,
    rowAlternationEnabled: false,
    wordWrapEnabled: true,
    autoExpandAll: true,
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
        allowDeleting: true,
    },
    scrolling: {
        mode: "standart"
    },
    paging : {
        enabled: false
    },
    pager: {
        visible: false,
        showInfo: true,
    },
    columns: [
        { 
			dataField: "title",
            width: 180
        },
        {
            caption: 'Action',
            width: 140,
            cellTemplate: function(container, options) {

                var isMine = options.data.isMine;
                var isPendingOnMe = options.data.isPendingOnMe;
                var reqid = options.data.id;
                var reqstatus = options.data.requestStatus;
                var mode = (reqstatus == 0 || reqstatus == 2 && (isMine == 1)) ? 'edit' : (reqstatus == 1 && ((isMine == 0 && isPendingOnMe == 1) || (isMine == 1 && isPendingOnMe == 1)) ? 'approval' : 'view') ;
                var arrColor = [
                    "btn-secondary",
                    (mode == 'approval' && reqstatus == 1) ? "btn-danger" : "btn-primary",
                    "btn-warning",
                    "btn-success",
                    "btn-danger",
                ];

                var viewIcon = (mode == 'approval' && reqstatus == 1) ? "fa-check" : "fa-search";
    
                $('<button class="btn '+arrColor[reqstatus]+'" id="btnreqid'+reqid+'"><i class="fa '+viewIcon+'"></i></button>').on('dxclick', function(evt) {
                    evt.stopPropagation();
                
                            popup.option({
                                contentTemplate: () => popupContentTemplate(reqid,mode,options),
                            });
                            popup.show();

                }).appendTo(container);
                if((reqstatus == 1 || reqstatus == 2) && ((isMine == 1 && (isPendingOnMe == 0 || isPendingOnMe == null)))) {
                    $('<button class="btn btn-danger" id="btnreqid'+reqid+'" style="margin-left: 3px;">Cancel</button>').on('dxclick', function(evt) {
                        evt.stopPropagation();

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "Are you sure you want to cancel this submission?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, cancel it'
                          }).then((result) => {
                            if (result.isConfirmed) {
                              sendRequest(apiurl + "/submissionrequest/"+reqid+"/"+modelclass, "POST", {
                                requestStatus:0,
                                action:'submission',
                                approvalAction: 0
                              }).then(function(response){
                                if(response.status != 'error') {
                                    dataGrid.refresh();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Saved',
                                        text: 'The submission has been cancelled.',
                                    });
                                }
                              });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Cancelled',
                                    text: 'The submission cancellation has been cancelled.'
                                });
                            }
                          });
    
                    }).appendTo(container); 
                }
            
            }
        },
        {
            caption: "Code",
            dataField: 'code',
            width: 180,
            // sortOrder: "desc"
        },
        { 
			dataField: "bu",
            caption: 'BU',
            width: 180
        },
        { 
			dataField: "estate",
            caption: 'Estate',
            width: 180
        },
        { 
			dataField: "user.fullname",
            caption: 'Creator Name',
            width: 180
        },
        {
            caption: 'Request Status',
            dataField: 'requestStatus',
            lookup: {
                dataSource: [
                    { id: 0, name: 'Draft' },
                    { id: 1, name: 'Waiting Approval' },
                    { id: 2, name: 'Rework' },
                    { id: 3, name: 'Approved' },
                    { id: 4, name: 'Rejected' },
                ],
                valueExpr: 'id',
                displayExpr: 'name'
            },
            cellTemplate: function(container, options) {
                var arrText = [
                    "<span class='btn btn-secondary btn-xs btn-status'>Draft</span>",
                    "<span class='btn btn-primary btn-xs btn-status'>Waiting Approval</span>",
                    "<span class='btn btn-warning btn-xs btn-status'>Rework</span>",
                    "<span class='btn btn-success btn-xs btn-status'>Approved</span>",
                    "<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
                ];
                container.html(arrText[options.data.requestStatus]);
            }
        },
        {
            dataField: "approveddoc",
            caption:"Approval Doc",
            allowFiltering: false,
            allowSorting: false,
            formItem: { visible: false},
            cellTemplate: function (container, options) {
                if ((options.value!="") && (options.value)){
                    $("<div />").dxButton({
                        icon: 'download',
                        type: "success",
                        text: "Download",
                        onClick: function (e) {
                            window.open(options.value, '_blank');
                        }
                    }).appendTo(container);
                }
            }
        },
      
    ],
    masterDetail: {
        enabled: true,
        template: function(container, options) {
            var currentRequest = options.data;
            var reqid = currentRequest.id;

            $("<div>").dxTabPanel({
                items: [
                    {
                        title: "Approver List",
                        template: function() {
                            return $("<div>").dxDataGrid({
                                dataSource: storewithmodule('approverlistrequest', modelclass, reqid),
                                columnAutoWidth: true,
                                showBorders: true,
                                columns: [
                                    {
                                        caption: "Fullname",
                                        dataField: "approver_id",
                                        lookup: {
                                            dataSource: listOption('/list-approver/' + modelclass, 'id', 'fullname'),
                                            valueExpr: 'id',
                                            displayExpr: 'fullname',
                                        }
                                    },
                                    "ApprovalType",
                                    {
                                        dataField: "approvalDate",
                                        dataType: "datetime",
                                        format: "dd-MM-yyyy hh:mm:ss",
                                    },
                                    {
                                        caption: "Approval Status",
                                        dataField: "approvalAction",
                                        encodeHtml: false,
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
                                    "remarks"
                                ]
                            });
                        }
                    },
                    {
                        title: "Expenditure Items",
                        template: function() {
                            return $("<div>").dxDataGrid({
                                dataSource: storewithmodule('capexdetail', modelclass, reqid),
                                columnAutoWidth: true,
                                showBorders: true,
                                columns: [
                                    {
                                        caption: 'Expenditure Item',
                                        dataField: 'expenditure_item',
                                    },
                                    {
                                        dataField: 'quantity',
                                        dataType: 'number',
                                    },
                                    {
                                        dataField: 'amount',
                                        dataType: 'number',
                                        format: "fixedPoint",
                                    },
                                    {
                                        caption: 'Sub Total',
                                        dataField: 'subtotal',
                                        dataType: 'number',
                                        format: "fixedPoint",
                                    },
                                ],
                                summary: {
                                    totalItems: [
                                        {
                                            column: "subtotal",
                                            summaryType: "sum",
                                            displayFormat: "Total: {0}",
                                            valueFormat: "fixedPoint",
                                        }
                                    ]
                                }
                            });
                        }
                    }
                ]
            }).appendTo(container);
        }
    },
    export: {
        enabled: true,
        fileName: modname,
        excelFilterEnabled: true,
        allowExportSelectedData: true
    },
    onExporting: function(e) {
        var workbook = new ExcelJS.Workbook();
        var worksheet = workbook.addWorksheet('Capex Request');

        // Map request status id ke nama
        var requestStatusMap = [
            "Draft",            // id 0
            "Waiting Approval", // id 1
            "Rework",           // id 2
            "Approved",         // id 3
            "Rejected"          // id 4
        ];

        // Header
        var headerRow = [];
        e.component.getVisibleColumns().forEach(function(column) {
            if (column.command || column.caption === 'Action') {
                return;
            }
            if (column.caption) {
                headerRow.push(column.caption);
            }
        });
        headerRow.push("Last Approver");
        headerRow.push("Total");
        worksheet.addRow(headerRow).font = { bold: true };

        // Muat seluruh data (bukan hanya yang "visible")
        e.component.getDataSource().load().then(function(fullData) {
            // Async export (biar detail tetap bisa pakai promise)
            var queue = Promise.resolve();
            fullData.forEach(function(masterData) {
                queue = queue.then(function() {
                    var reqid = masterData.id;

                    var approverPromise = storewithmodule('approverlistrequest', modelclass, reqid).load();
                    var expenditurePromise = storewithmodule('capexdetail', modelclass, reqid).load();
                    var approverListPromise = new DevExpress.data.DataSource(listOption('/list-approver/' + modelclass, 'id', 'fullname')).load();

                    return Promise.all([approverPromise, expenditurePromise, approverListPromise]).then(([approverData, expenditureData, approverList]) => {

                        // Find last approver
                        var lastApprover = '';
                        if (approverData.length > 0) {
                            var waitingApprovers = approverData.filter(a => a.approvalAction === 1);
                            if (waitingApprovers.length > 0) {
                                var approverId = waitingApprovers[0].approver_id;
                                var approver = approverList.find(a => a.id === approverId);
                                if (approver) {
                                    lastApprover = approver.fullname;
                                }
                            }
                        }

                        // Calculate total
                        var total = 0;
                        if (expenditureData.length > 0) {
                            expenditureData.forEach((detail) => {
                                total += parseFloat(detail.subtotal) || 0;
                            });
                        }

                        // Buat kolom per data
                        var visibleColumns = e.component.getVisibleColumns();
                        var rowValue = [];

                        visibleColumns.forEach(function(column) {
                            if (column.command || column.caption === 'Action') {
                                return;
                            }
                            if (column.dataField === "approveddoc") {
                                if (masterData.approveddoc) {
                                    rowValue.push({ text: 'Click to Download', hyperlink: baseurl+ '/' + masterData.approveddoc });
                                } else {
                                    rowValue.push('');
                                }
                            } else if (column.dataField === "requestStatus") {
                                rowValue.push(requestStatusMap[masterData.requestStatus]);
                            } else if (column.dataField && column.dataField.indexOf('.') > -1) {
                                // Nested field (misal: "user.fullname")
                                var parts = column.dataField.split('.');
                                var value = masterData;
                                parts.forEach(function(part) {
                                    value = value ? value[part] : '';
                                });
                                rowValue.push(value);
                            } else {
                                rowValue.push(masterData[column.dataField]);
                            }
                        });
                        rowValue.push(lastApprover);
                        rowValue.push(total);

                        // Add Master Row
                        var addedRow = worksheet.addRow(rowValue);

                        // Style hyperlink pada kolom approveddoc
                        var approvedDocIndex = -1;
                        visibleColumns.forEach(function(column, idx) {
                            if (column.dataField === 'approveddoc') {
                                approvedDocIndex = idx;
                            }
                        });
                        if (approvedDocIndex > -1) {
                            addedRow.getCell(approvedDocIndex + 1).font = {
                                color: { argb: 'FF0000FF' },
                                underline: true
                            };
                        }

                        // Add detail rows (approver list)
                        if (approverData.length > 0) {
                            worksheet.addRow(['', 'Approver List:']).font = { bold: true };
                            worksheet.lastRow.outlineLevel = 1;
                            var approverHeader = ['', 'Fullname', 'Approval Type', 'Approval Date', 'Approval Status', 'Remarks'];
                            worksheet.addRow(approverHeader).font = { bold: true };
                            worksheet.lastRow.outlineLevel = 1;
                            approverData.forEach(function(item) {
                                var approver = approverList.find(a => a.id === item.approver_id);
                                var formattedDate = '';
                                if (item.approvalDate) {
                                    var date = new Date(item.approvalDate);
                                    var year = date.getFullYear();
                                    var month = ('0' + (date.getMonth() + 1)).slice(-2);
                                    var day = ('0' + date.getDate()).slice(-2);
                                    formattedDate = year + '-' + month + '-' + day;
                                }
                                var detailRow = [
                                    '',
                                    approver ? approver.fullname : '',
                                    item.ApprovalType,
                                    formattedDate,
                                    requestStatusMap[item.approvalAction],
                                    item.remarks
                                ];
                                worksheet.addRow(detailRow);
                                worksheet.lastRow.outlineLevel = 1;
                            });
                        }

                        // Add detail rows (expenditure items)
                        if (expenditureData.length > 0) {
                            worksheet.addRow(['', 'Expenditure Items:']).font = { bold: true };
                            worksheet.lastRow.outlineLevel = 1;
                            var expenditureHeader = ['', 'Expenditure Item', 'Quantity', 'Amount', 'Sub Total'];
                            worksheet.addRow(expenditureHeader).font = { bold: true };
                            worksheet.lastRow.outlineLevel = 1;
                            expenditureData.forEach(function(item) {
                                var detailRow = [
                                    '',
                                    item.expenditure_item,
                                    item.quantity,
                                    item.amount,
                                    item.subtotal
                                ];
                                worksheet.addRow(detailRow);
                                worksheet.lastRow.outlineLevel = 1;
                            });

                            // Add total row for expenditure items (sums the 'subtotal' column)
                            let row = worksheet.addRow([]);
                            Object.assign(row.getCell(4), { // Corresponds to the 'Sub Total' column
                                value: "Total:",
                                font: { bold: true }
                            });
                            Object.assign(row.getCell(5), {
                                value: total,
                                font: { bold: true },
                                numFmt: '#,##0.00'
                            });
                            worksheet.lastRow.outlineLevel = 1;
                        }
                    });
                });
            });

            // Setelah semua promise selesai, baru ekspor file
            queue.then(function() {
                workbook.xlsx.writeBuffer().then(function(buffer) {
                    saveAs(new Blob([buffer], { type: 'application/octet-stream' }), 'CapexRequest.xlsx');
                });
            });
        });

        e.cancel = true;
    },
    // onExporting: function(e) {
    //     var workbook = new ExcelJS.Workbook();
    //     var worksheet = workbook.addWorksheet('Capex Request');

    //     var requestStatusMap = [
    //         "Draft", "Waiting Approval", "Rework", "Approved", "Rejected"
    //     ];

    //     // Header
    //     var headerRow = [];
    //     e.component.getVisibleColumns().forEach(function(column) {
    //         if (column.command || column.caption === 'Action') return;
    //         if (column.caption) headerRow.push(column.caption);
    //     });
    //     headerRow.push("Last Approver");
    //     headerRow.push("Total");
    //     worksheet.addRow(headerRow).font = { bold: true };

    //     e.component.getDataSource().load().then(function(fullData) {
    //         fullData.forEach(function(masterData) {
    //             // Find last approver
    //             var lastApprover = '';
    //             if (masterData.approverlist && masterData.approverlist.length > 0) {
    //                 // ambil yang approvalAction = 1, kalau tidak ada ambil paling akhir
    //                 var waiting = masterData.approverlist.filter(a => a.approvalAction === 1);
    //                 if (waiting.length > 0) {
    //                     lastApprover = waiting[0].approver_id;
    //                 } else {
    //                     // fallback
    //                     lastApprover = masterData.approverlist[masterData.approverlist.length - 1].approver_id;
    //                 }
    //                 // Jika mau ambil nama, masterData.approverlistX.approver.fullname (request eager loading relasi)!
    //             }

    //             var total = masterData.total || masterData.additional_budget || 0;

    //             // Row Value
    //             var visibleColumns = e.component.getVisibleColumns();
    //             var rowValue = [];
    //             visibleColumns.forEach(function(column) {
    //                 if (column.command || column.caption === 'Action') return;
    //                 if (column.dataField === "approveddoc") {
    //                     if (masterData.approveddoc) {
    //                         rowValue.push({ text: 'Click to Download', hyperlink: baseurl + '/' + masterData.approveddoc });
    //                     } else {
    //                         rowValue.push('');
    //                     }
    //                 } else if (column.dataField === "requestStatus") {
    //                     rowValue.push(requestStatusMap[masterData.requestStatus]);
    //                 } else if (column.dataField && column.dataField.indexOf('.') > -1) {
    //                     var parts = column.dataField.split('.');
    //                     var value = masterData;
    //                     parts.forEach(function(part) {
    //                         value = value ? value[part] : '';
    //                     });
    //                     rowValue.push(value);
    //                 } else {
    //                     rowValue.push(masterData[column.dataField]);
    //                 }
    //             });
    //             rowValue.push(lastApprover);
    //             rowValue.push(total);

    //             var addedRow = worksheet.addRow(rowValue);

    //             // Styling hyperlink
    //             var approvedDocIndex = -1;
    //             visibleColumns.forEach(function(column, idx) {
    //                 if (column.dataField === 'approveddoc') {
    //                     approvedDocIndex = idx;
    //                 }
    //             });
    //             if (approvedDocIndex > -1) {
    //                 addedRow.getCell(approvedDocIndex + 1).font = {
    //                     color: { argb: 'FF0000FF' },
    //                     underline: true
    //                 };
    //             }

    //             // Detail: Approver List
    //             if (masterData.approverlist && masterData.approverlist.length > 0) {
    //                 worksheet.addRow(['', 'Approver List:']).font = { bold: true };
    //                 worksheet.lastRow.outlineLevel = 1;
    //                 worksheet.addRow(['', 'ApproverId', 'Approval Date', 'Approval Status', 'Remarks']).font = { bold: true };
    //                 worksheet.lastRow.outlineLevel = 1;
    //                 masterData.approverlist.forEach(function(item) {
    //                     worksheet.addRow([
    //                         '',
    //                         item.approver_id,
    //                         item.approvalDate ? item.approvalDate.split('T')[0] : '',
    //                         requestStatusMap[item.approvalAction],
    //                         item.remarks || ''
    //                     ]);
    //                     worksheet.lastRow.outlineLevel = 1;
    //                 });
    //             }
    //         });

    //         workbook.xlsx.writeBuffer().then(function(buffer) {
    //             saveAs(new Blob([buffer], { type: 'application/octet-stream' }), 'CapexRequest.xlsx');
    //         });
    //     });

    //     e.cancel = true;
    // },
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

$('#HistoryButton').on('click',function(){
    var dataGridhistory = $("#historyCapex").dxDataGrid({    
        dataSource: store('capexhistoryApp'),
        allowColumnReordering: true,
        allowColumnResizing: true,
        columnHidingEnabled: true,
        rowAlternationEnabled: false,
        wordWrapEnabled: true,
        autoExpandAll: true,
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
        pager: {
            visible: false,
            showInfo: true,
        },
        columns: [
            {
                caption: "Code",
                dataField: 'code',
                width: 180,
            },
            { 
                caption: 'BU',
                dataField: "bu",
                width: 80
            },
            { 
                caption: 'Estate',
                dataField: "estate",
                width: 100
            },
            { 
                caption: 'Creator Name',
                dataField: "user.fullname",
                width: 180
            },
            { 
                caption: 'Title',
                dataField: "title",
                width: 200
            },
            { 
                caption: 'Form Type',
                dataField: "form_type",
                lookup: {
                    dataSource: [
                        {form:'Low Value Asset'},
                        {form:'Operating/Maintenance Capex'},
                        {form:'Project Capex'},
                    ],
                    valueExpr: 'form',
                    displayExpr: 'form',
                },
            },
            { 
                caption: 'Request Type',
                dataField: "request_type",
                lookup: { 
                    dataSource: categoryType,  
                    valueExpr: 'request',
                    displayExpr: 'request',
                },
            },
            
            {
                dataField: 'requestStatus',
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
                },
            },
            {
                dataField: "approveddoc",
                caption:"Approval Doc",
                allowFiltering: false,
                allowSorting: false,
                formItem: { visible: false},
                cellTemplate: function (container, options) {
                    var value = options.value;
                    var origin = window.location.origin;
                    if (value && value.includes('doc')) {
                        var baseUrl = origin + '/oasys/';
                    } else {
                        var baseUrl = origin + '/devportal/';
                    }
                    var fullUrl = baseUrl + value;
                    if ((value!="") && (value)){
                        $("<div />").dxButton({
                            icon: 'download',
                            type: "success",
                            text: "Download",
                            onClick: function (e) {
                                window.open(fullUrl, '_blank');

                            }
                        }).appendTo(container);
                    }
                }
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
            dataGridhistory = e.component;

            e.toolbarOptions.items.unshift({						
                location: "after",
                widget: "dxButton",
                options: {
                    hint: "Refresh Data",
                    icon: "refresh",
                    onClick: function() {
                        dataGridhistory.refresh();
                    }
                }
            })
        },
        onDataErrorOccurred: function(e) {
            // Menampilkan pesan kesalahan
            console.log("Terjadi kesalahan saat memuat data (0):", e.error.message);

            // Memuat ulang Page
            // dataGridhistory.refresh();
        }
    }).dxDataGrid("instance");
})

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
        ID: 6,
        Title: '<i class="fas fa-list"> Expenditure Items </i>',
        visible: true
    },
    {
        ID: 5,
        Title: '<i class="fas fa-list"> Justifications </i>',
        visible: true
    },
    {
        ID: 7,
        Title: '<i class="fas fa-list"> Questionnaire </i>',
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

    var dataSector = [
        { bu: 'IHM', sector: 'TRN' },
        { bu: 'IHM', sector: 'SPU' },
        { bu: 'IHM', sector: 'SNI' },
        { bu: 'IHM', sector: 'HO' },
        { bu: 'AHL', sector: 'SBG' },
        { bu: 'AHL', sector: 'SBS' },
        { bu: 'AHL', sector: 'SSP' },
        { bu: 'AHL', sector: 'HO' },
        { bu: 'NKL', sector: 'NKL' },
        { bu: 'KPSI', sector: 'KPSI' },
        { bu: 'MHS', sector: 'MHS' },
    ];

    var categoryType = [
        { form: 'Low Value Asset', request: 'Budgeted' },
        { form: 'Low Value Asset', request: 'Unbudgeted Swap Available' },
        { form: 'Operating/Maintenance Capex', request: 'Budgeted' },
        { form: 'Operating/Maintenance Capex', request: 'Unbudgeted Swap Available' },
        { form: 'Operating/Maintenance Capex', request: 'Unbudgeted Swap Unavailable' },
        { form: 'Project Capex', request: 'Budgeted' },
        { form: 'Project Capex', request: 'Unbudgeted Swap Available' },
        { form: 'Project Capex', request: 'Unbudgeted Swap Unavailable' },
    ];

const popupContentTemplate = function (reqid,mode,options) {

    var isMine = options.data.isMine;
    var isPendingOnMe = options.data.isPendingOnMe;
    isChecker = options.data.isChecker;

    var validationRules = [];

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
                if(data.ID == 1) {
                    if (mode == 'add' || mode == 'edit'){
                        $("<span style='color:red;font-size:11pt'>").html('Silahkan lengkapi <b><i class="far fa-newspaper tips"> Form Data </i></b> dan lampirkan <i class="fas fa-file tips"> Supporting Document </i> sebelum klik tombol <span class="tips"><i class="bx bx-check-double label-icon"></i> Submit Submission</span>').appendTo(container);
                    }
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add') ? true : (admin == 1 ? true : false),
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
                                width: 120,
                                editorOptions: { 
                                    readOnly: true
                                }
                            },
                            {
                                dataField: 'title',
                                dataType: 'string',
                                width: 150,
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'BU',
                                dataField: 'bu',
                                lookup: {
                                    dataSource: [{bu:'IHM'},{bu:'AHL'},{bu:'NKL'},{bu:'KPSI'},{bu:'MHS'}],
                                    valueExpr: 'bu',
                                    displayExpr: 'bu',
                                },
                                setCellValue: function (rowData, value) {
                                    rowData.bu = value;
                                    if (value === "IHM") {
                                        rowData.estate = "HO";
                                    } else if (value === "AHL") {
                                        rowData.estate = "HO";
                                    } else if (value === "NKL") {
                                        rowData.estate = "NKL";
                                    } else if (value === "MHS") {
                                        rowData.estate = "MHS";
                                    } 
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Estate',
                                dataField: 'estate',
                                lookup: {
                                    dataSource: function (options) {
                                        return {
                                            store: {
                                                type: 'array',
                                                data: dataSector
                                            },
                                            filter: options.data ? ["bu", "=", options.data.bu] : null
                                        };
                                    },
                                    valueExpr: 'sector',
                                    displayExpr: 'sector',
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                caption: 'Business Type',
                                dataField: 'business_type',
                                editorOptions: { 
                                    readOnly: true,
                                },
                            },
                            {
                                caption: 'Project Type',
                                dataField: 'project_type',
                                lookup: {
                                    dataSource: [
                                        {value:'New'},
                                        {value:'Modification'},
                                        {value:'Repair'},
                                        {value:'Replace'},
                                    ],
                                    valueExpr: 'value',
                                    displayExpr: 'value',
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                            },
                            {
                                dataField: 'equipment',
                                lookup: {
                                    dataSource: [
                                        { value: 'Building' },
                                        { value: 'Computer Hardware & Accessories' },
                                        { value: 'Heavy Equipment' },
                                        { value: 'Infrastructure' },
                                        { value: 'Lab & Survey Equipment' },
                                        { value: 'Light Vehicle' },
                                        { value: 'Low Value Asset' },
                                        { value: 'Office Mess & Telkom Equipment' },
                                        { value: 'Other Equipment' },
                                        { value: 'Plant & Machinery' },
                                        { value: 'Transport Equipment' },
                                        { value: 'Water & Electricity Equipment' },
                                        { value: 'Workshop & Agriculture Equipment' }
                                    ],
                                    valueExpr: 'value',
                                    displayExpr: 'value',
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                }
                            },
                            {
                                dataField: 'cost_center',
                                dataType: 'string',
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                }
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
                                        dataGrid1a.refresh();
                                    }
                                }
                            });
                        },
                        onEditorPreparing: function (e) {
                        },
                        onRowUpdating: function(e) {
                            
                        },
                        onCellPrepared: function (e) {
                            if (e.column.index == 0 && e.rowType == "data") {
                                if(e.data.code === null) {
                                    $("#formdata").dxDataGrid('columnOption','code', 'visible', false);
                                } else {
                                    $("#formdata").dxDataGrid('columnOption','code', 'visible', true);
                                }
                            }
                            if ( e.rowType == "data" && ((e.column.index>0 && e.column.index<7))) {
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
                    }).appendTo(container);

                    // Spacer antara dua DataGrid
                    $("<div style='height: 20px;'>").appendTo(container);

                    var secondGrid  = $("<div id='secondGrid'>").dxDataGrid({    
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
                            mode: "cell",
                            allowAdding: false,
                            // allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add') ? true : (admin == 1 ? true : false),
                            allowUpdating: ((isMine == 1) && (mode == 'edit' || mode == 'add')) ? true : (admin == 1 ? true : (isChecker == 1 ? true : false)),
                            allowDeleting: false,
                        },
                        scrolling: {
                            mode: "virtual"
                        },
                        columns: [
                            {
                                caption: 'Form Type',
                                dataField: 'form_type',
                                lookup: {
                                    dataSource: [
                                        {form:'Low Value Asset'},
                                        {form:'Operating/Maintenance Capex'},
                                        {form:'Project Capex'},
                                    ],
                                    valueExpr: 'form',
                                    displayExpr: 'form',
                                },
                                setCellValue: function (rowData, value) {
                                    rowData.form_type = value;
                                    if (value === "Low Value Asset") {
                                        rowData.request_type = "Budgeted";
                                    } else if (value === "Operating/Maintenance Capex") {
                                        rowData.request_type = "Budgeted";
                                    } else if (value === "Project Capex") {
                                        rowData.request_type = "Budgeted";
                                    }
                                },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false,
                                },
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Request Type',
                                dataField: 'request_type',
                                lookup: {
                                    dataSource: function (options) {
                                        return {
                                            store: {
                                                type: 'array',
                                                data: categoryType
                                            },
                                            filter: options.data ? ["form", "=", options.data.form_type] : null
                                        };
                                    },
                                    valueExpr: 'request',
                                    displayExpr: 'request',
                                },
                                // setCellValue: function (rowData, value) {
                                //     rowData.request_type = value;
                                //     console.log(value)
                                //     if (value === 'Unbudgeted - Swap Unavailable' || value === 'Unbudgeted - Swap Available') {
                                //         validationRules.length = 0;
                                //         validationRules.push({
                                //             type: "required", 
                                //             message: "This item is required"
                                //         });
                                //     } else {
                                //         validationRules.length = 0;
                                //     }
                                // },
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: "Reason Unbudgeted",
                                dataField:'reason_unbudgeted',
                                dataType: "string",
                                validationRules: [{
                                    type: "custom",
                                    message: "Reason Unbudgeted is required when Request Type is Unbudgeted.",
                                    validationCallback: function(e) {
                                        const requestType = e.data.request_type;
                                        return !(requestType === 'Unbudgeted Swap Unavailable' || requestType === 'Unbudgeted Swap Available') || (e.value !== null && e.value !== '');
                                    }
                                }],
                                editorOptions: { 
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                // validationRules : validationRules
                            },
                            {
                                dataField: 'approved_budget',
                                dataType: 'number',
                                format: "fixedPoint",
                                editorOptions: {
                                    readOnly: (mode == 'approval') ? true : false,
                                    format: "fixedPoint",
                                },
                                // validationRules: [{ type: "required" }],
                            },
                            {
                                dataField: 'additional_budget',
                                dataType: 'number',
                                format: "fixedPoint",
                                editorOptions: {
                                    format: "fixedPoint",
                                    readOnly: (mode == 'approval') ? true : false
                                },
                                allowUpdating: (isChecker == 1) ? true : false,
                                validationRules: [{
                                    type: "custom",
                                    message: "Additional Budget is required when Request Type is Unbudgeted Swap Available.",
                                    validationCallback: function(e) {
                                        const requestType = e.data.request_type;
                                        return !(requestType === 'Unbudgeted Swap Available') || (e.value !== null && e.value !== '' && e.value !== 0);
                                    }
                                }],
                                // validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Additional Approver',
                                dataField: 'additional_approver',
                                lookup: {
                                    dataSource: listOption('/list-employee','id','fullname'),
                                    valueExpr: 'id',
                                    displayExpr: function(item) {
                                        return item ? item.fullname + " (" + item.sapid + ")" : "";
                                    }
                                },
                                visible: (isChecker == 1 || admin == 1) ? true : false,
                            },
                        ],
                        export: {
                            enabled: false,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGrid1a = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onInitNewRow : function(e) {
                        },
                        onToolbarPreparing: function(e) {
                        },
                        onEditorPreparing: function (e) {
                        },
                        onRowUpdating: function(e) {
                            
                        },
                        onCellPrepared: function (e) {
                            if ( e.rowType == "data" && ((e.column.index < 2))) {
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
                            dataGrid1a.refresh();
                        }
                    }).appendTo(container);

                    return container
                }
                else if(data.ID == 6) {
                    var containerdetail = $("<div>");
                    $("<span style='color:red;font-size:11pt'>").html('Total Amount Expenditur Items harus balance dengan Approved Budget').appendTo(containerdetail);

                    var formData = $("<div id='formdetail'>").dxDataGrid({    
                        dataSource: storewithmodule('capexdetail',modelclass,reqid),
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
                            visible: true,
                            width: 240,
                            placeholder: 'Search...',
                        },
                        sorting: {
                            mode: "none" // or "multiple" | "none"
                        },
                        editing: {
                            useIcons:true,
                            mode: "cell",
                            allowAdding: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowDeleting: false,
                        },
                        scrolling: {
                            rowRenderingMode: 'virtual',
                        },
                        paging: {
                            pageSize: 25,
                        },
                        pager: {
                            visible: true,
                            allowedPageSizes: [5, 15, 'all'],
                            showPageSizeSelector: true,
                            showInfo: true,
                            showNavigationButtons: true,
                        },
                        columns: [
                            {
                                caption: 'Expenditure Item',
                                dataField: 'expenditure_item',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                dataField: 'quantity',
                                dataType: 'number',
                                width: 100,
                                validationRules: [{ type: "required" }],
                            },
                            {
                                dataField: 'amount',
                                dataType: 'number',
                                format: "fixedPoint",
                                editorOptions: {
                                    format: "fixedPoint",
                                },
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Sub Total',
                                dataField: 'subtotal',
                                dataType: 'number',
                                format: "fixedPoint",
                                editorOptions: {
                                    format: "fixedPoint",
                                    readOnly: true
                                },
                            },
                        ],
                        summary: {
                            totalItems: [
                                {
                                    column: "subtotal",
                                    summaryType: "sum",
                                    displayFormat: "Total: {0}",
                                    valueFormat: "fixedPoint",
                                    format: "fixedPoint",
                                    editorOptions: {
                                        format: "fixedPoint",
                                    }
                                }
                            ]
                        },
                        export: {
                            enabled: false,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGriddetail = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGriddetail.refresh();
                                    }
                                }
                            });
                        },
                        onEditorPrepared: function (e) {
                        },
                        onCellPrepared: function (e) {
                        },
                        onDataErrorOccurred: function(e) {
                            // Menampilkan pesan kesalahan
                            console.log("Terjadi kesalahan saat memuat data (6):", e.error.message);
                    
                            // Memuat ulang DataGrid
                            dataGriddetail.refresh();
                        }
                    }).appendTo(containerdetail);

                    return containerdetail
                }
                else if(data.ID == 5) {
                    var containerdetail = $("<div>");

                    var formData = $("<div id='formjustification'>").dxDataGrid({    
                        dataSource: storewithmodule('capexjustification',modelclass,reqid),
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
                            visible: true,
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
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowDeleting: false,
                        },
                        scrolling: {
                            rowRenderingMode: 'virtual',
                        },
                        paging: {
                            pageSize: 25,
                        },
                        pager: {
                            visible: true,
                            allowedPageSizes: [5, 15, 'all'],
                            showPageSizeSelector: true,
                            showInfo: true,
                            showNavigationButtons: true,
                        },
                        columns: [
                            {
                                caption: 'What is currently available?',
                                dataField: 'justification_1',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Why is expenditure needed?',
                                dataField: 'justification_2',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Can expenditure be deferred to next year? If not, why?',
                                dataField: 'justification_3',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'What are the consequences if expenditure is denied?',
                                dataField: 'justification_4',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'What is the impact HES/Health, Environment Safety?',
                                dataField: 'justification_5',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'Will there be any adverse impact on existing operations (e.g disruption, downtime)?',
                                dataField: 'justification_6',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'When will expenditure be made (month/year)?',
                                dataField: 'justification_7',
                                validationRules: [{ type: "required" }],
                            },
                            {
                                caption: 'What is the project duration? When will project be completed?',
                                dataField: 'justification_8',
                                validationRules: [{ type: "required" }],
                            },
                        ],
                        export: {
                            enabled: false,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGridjustification = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGridjustification.refresh();
                                    }
                                }
                            });
                        },
                        onEditorPrepared: function (e) {
                        },
                        onCellPrepared: function (e) {
                        },
                        onDataErrorOccurred: function(e) {
                            // Menampilkan pesan kesalahan
                            console.log("Terjadi kesalahan saat memuat data (6):", e.error.message);
                    
                            // Memuat ulang DataGrid
                            dataGridjustification.refresh();
                        }
                    }).appendTo(containerdetail);

                    return containerdetail
                }
                else if(data.ID == 7) {
                    var containerdetail = $("<div>");

                    var formData = $("<div id='formquestion'>").dxDataGrid({    
                        dataSource: storewithmodule('capexquestion',modelclass,reqid),
                        allowColumnReordering: false,
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
                            mode: "cell",
                            allowAdding: false,
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowDeleting: false,
                        },
                        scrolling: {
                            rowRenderingMode: 'virtual',
                        },
                        paging: {
                            pageSize: 25,
                        },
                        pager: {
                            visible: true,
                            allowedPageSizes: [5, 15, 'all'],
                            showPageSizeSelector: true,
                            showInfo: true,
                            showNavigationButtons: true,
                        },
                        grouping: {
                            autoExpandAll: false,
                        },
                        groupPanel: {
                            visible: true,
                        },
                        columns : [
                            {
                                caption: 'Sequence',
                                dataField: 'refcapexq.sequence',
                                width: 80,
                                groupIndex: 0,
                                editorOptions: { 
                                    readOnly: true
                                }
                            },
                            {
                                caption: 'Number',
                                dataField: 'refcapexq.number',
                                width: 80,
                                editorOptions: { 
                                    readOnly: true
                                }
                            },
                            {
                                caption: 'Question',
                                dataField: 'refcapexq.question',
                                editorOptions: { 
                                    readOnly: true
                                }
                            },
                            // { 
                            //     dataField: "answer", 
                            //     cellTemplate: function(container, options) {
                            //         const value = options.value;
                            //         const name = `answer_${options.rowIndex}`;
                            //         const questionId = options.data.refcapexq.id;

                            //         let items;
                            //         if (questionId <= 7 || questionId == 9 ) {
                            //             items = ['Yes', 'No'];
                            //         } else if (questionId === 8) {
                            //             items = ['Must Have', 'Need to Have', 'Nice to Have'];
                            //         }
                                    
                            //         $('<div>').dxRadioGroup({
                            //             items: items,
                            //             value: value,
                            //             layout: 'horizontal',
                            //             onValueChanged: function(e) {
                            //                 // Aktifkan mode edit terlebih dahulu
                            //                 options.component.editCell(options.rowIndex, "answer");
                            //                 options.setValue(e.value);
                            //             }
                            //         }).appendTo(container);
                            //     },
                            //     editCellTemplate: function(container, options) {
                            //         const questionId = options.row.data.refcapexq.id;

                            //         let items;
                            //         if (questionId <= 7 || questionId == 9 ) {
                            //             items = ['Yes', 'No'];
                            //         } else if (questionId === 8) {
                            //             items = ['Must Have', 'Need to Have', 'Nice to Have'];
                            //         }
                                    
                            //         $('<div>').dxRadioGroup({
                            //             items: items,
                            //             value: options.value,
                            //             layout: 'horizontal',
                            //             onValueChanged: function(e) {
                            //                 options.setValue(e.value);
                            //             }
                            //         }).appendTo(container);
                            //     },
                            //     validationRules: [{ type: "required" }]
                            // },
                            // {
                            //     dataField: "answer",
                            //     cellTemplate: function(container, options) {
                            //         const value = options.value;
                            //         const questionId = options.data.refcapexq.id;
                            
                            //         let items;
                            //         if (questionId <= 7 || questionId == 9) {
                            //             items = ['Yes', 'No'];
                            //             $('<div>').dxRadioGroup({
                            //                 items: items,
                            //                 value: value,
                            //                 layout: 'horizontal',
                            //                 onValueChanged: function(e) {
                            //                     options.component.editCell(options.rowIndex, "answer");
                            //                     options.setValue(e.value);
                            //                 }
                            //             }).appendTo(container);
                            //         } else if (questionId === 8) {
                            //             items = ['Must Have', 'Need to Have', 'Nice to Have'];
                            //             $('<div>').dxRadioGroup({
                            //                 items: items,
                            //                 value: value,
                            //                 layout: 'horizontal',
                            //                 onValueChanged: function(e) {
                            //                     options.component.editCell(options.rowIndex, "answer");
                            //                     options.setValue(e.value);
                            //                 }
                            //             }).appendTo(container);
                            //         } else if (questionId >= 10) {
                            //             $('<input>')
                            //                 .attr('type', 'text')
                            //                 .val(value || '')
                            //                 .on('input', function(e) {
                            //                     options.component.editCell(options.rowIndex, "answer");
                            //                     options.setValue(e.target.value);
                            //                 })
                            //                 .appendTo(container);
                            //         }
                            //     },
                            //     editCellTemplate: function(container, options) {
                            //         const questionId = options.row.data.refcapexq.id;
                            
                            //         let items;
                            //         if (questionId <= 7 || questionId == 9) {
                            //             items = ['Yes', 'No'];
                            //             $('<div>').dxRadioGroup({
                            //                 items: items,
                            //                 value: options.value,
                            //                 layout: 'horizontal',
                            //                 onValueChanged: function(e) {
                            //                     options.setValue(e.value);
                            //                 }
                            //             }).appendTo(container);
                            //         } else if (questionId === 8) {
                            //             items = ['Must Have', 'Need to Have', 'Nice to Have'];
                            //             $('<div>').dxRadioGroup({
                            //                 items: items,
                            //                 value: options.value,
                            //                 layout: 'horizontal',
                            //                 onValueChanged: function(e) {
                            //                     options.setValue(e.value);
                            //                 }
                            //             }).appendTo(container);
                            //         } else if (questionId >= 10) {
                            //             $('<input>')
                            //                 .attr('type', 'text')
                            //                 .val(options.value || '')
                            //                 .on('input', function(e) {
                            //                     options.setValue(e.target.value);
                            //                 })
                            //                 .appendTo(container);
                            //         }
                            //     },
                            //     validationRules: [{ type: "required" }]
                            // },
                            {
                                dataField: "answer",
                                cellTemplate: function(container, options) {
                                    const value = options.value;
                                    const questionId = options.data.refcapexq.id;
                            
                                    if (questionId >= 10 && questionId !== 20 && questionId !== 21) {
                                        createTextEditor(container, value, options);
                                    } else if (questionId === 20 || questionId === 21) {
                                        createDateEditor(container, value, options);
                                    } else {
                                        const items = getItems(questionId);
                                        createRadioGroup(container, items, value, options);
                                    }
                                },
                                editCellTemplate: function(container, options) {
                                    const questionId = options.row.data.refcapexq.id;
                            
                                    if (questionId >= 10 && questionId !== 20 && questionId !== 21) {
                                        createTextEditor(container, options.value, options);
                                    } else if (questionId === 20 || questionId === 21) {
                                        createDateEditor(container, options.value, options);
                                    } else {
                                        const items = getItems(questionId);
                                        createRadioGroup(container, items, options.value, options);
                                    }
                                },
                                validationRules: [{ type: "required" }]
                            },
                            {
                                dataField: "remarks",
                                // validationRules: [{ type: "required" }]
                            }
                        ],
                        export: {
                            enabled: false,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGridquestion = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGridquestion.refresh();
                                    }
                                }
                            });
                        },
                        onEditorPrepared: function (e) {
                        },
                        onCellPrepared: function (e) {
                        },
                        onDataErrorOccurred: function(e) {
                            // Menampilkan pesan kesalahan
                            console.log("Terjadi kesalahan saat memuat data (6):", e.error.message);
                    
                            // Memuat ulang DataGrid
                            dataGridquestion.refresh();
                        }
                    }).appendTo(containerdetail);

                    function getItems(questionId) {
                        if (questionId <= 7 || questionId == 9) {
                            return ['Yes', 'No'];
                        } else if (questionId === 8) {
                            return ['Must Have', 'Need to Have', 'Nice to Have'];
                        }
                        return null;
                    }
                    
                    function createRadioGroup(container, items, value, options) {
                        $('<div>').dxRadioGroup({
                            items: items,
                            value: value,
                            layout: 'horizontal',
                            onValueChanged: function(e) {
                                options.component.editCell(options.rowIndex, "answer");
                                options.setValue(e.value);
                            }
                        }).appendTo(container);
                    }
                    
                    function createTextEditor(container, value, options) {
                        $('<div>')
                        .dxTextBox({
                            value: value || '',
                            onValueChanged: function(e) {
                                options.component.editCell(options.rowIndex, "answer");
                                options.setValue(e.value);
                            }
                        })
                        .appendTo(container);
                    }

                    function createDateEditor(container, value, options) {
                        $('<div>')
                            .dxDateBox({
                                value: value || null,
                                type: 'date', // Anda bisa mengatur jenis seperti 'datetime' jika diperlukan
                                displayFormat: 'dd/MM/yyyy', // Format tampilan tanggal
                                onValueChanged: function(e) {
                                    options.component.editCell(options.rowIndex, "answer");
                                    options.setValue(e.value);
                                }
                            })
                            .appendTo(container);
                    }

                    $("<span style='color:red;font-size:11pt'>").html('G : Forecasted Cash Flow Details').appendTo(containerdetail);

                    var formData = $("<div id='formdetail'>").dxDataGrid({    
                        dataSource: storewithmodule('capexquestioncf',modelclass,reqid),
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
                            visible: true,
                            width: 240,
                            placeholder: 'Search...',
                        },
                        sorting: {
                            mode: "none" // or "multiple" | "none"
                        },
                        editing: {
                            useIcons:true,
                            mode: "cell",
                            allowAdding: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowDeleting: false,
                        },
                        scrolling: {
                            rowRenderingMode: 'virtual',
                        },
                        paging: {
                            pageSize: 25,
                        },
                        pager: {
                            visible: true,
                            allowedPageSizes: [5, 15, 'all'],
                            showPageSizeSelector: true,
                            showInfo: true,
                            showNavigationButtons: true,
                        },
                        columns: [
                            {
                                dataField: 'year',
                                dataType: 'number',
                                width: 100,
                                lookup: {
                                    dataSource: generateYearOptions(),
                                    valueExpr: 'value',
                                    displayExpr: 'text'
                                },
                            },
                            {
                                dataField: 'q1',
                                dataType: 'number',
                                format: "fixedPoint",
                                editorOptions: {
                                    format: "fixedPoint",
                                },
                            },
                            {
                                dataField: 'q2',
                                dataType: 'number',
                                format: "fixedPoint",
                                editorOptions: {
                                    format: "fixedPoint",
                                },
                            },
                            {
                                dataField: 'q3',
                                dataType: 'number',
                                format: "fixedPoint",
                                editorOptions: {
                                    format: "fixedPoint",
                                },
                            },
                            {
                                dataField: 'q4',
                                dataType: 'number',
                                format: "fixedPoint",
                                editorOptions: {
                                    format: "fixedPoint",
                                },
                            },
                        ],
                        summary: {
                            totalItems: [
                                {
                                    column: "subtotal",
                                    summaryType: "sum",
                                    displayFormat: "Total: {0}",
                                    valueFormat: "fixedPoint",
                                    format: "fixedPoint",
                                    editorOptions: {
                                        format: "fixedPoint",
                                    }
                                }
                            ]
                        },
                        export: {
                            enabled: false,
                            fileName: modname,
                            excelFilterEnabled: true,
                            allowExportSelectedData: true
                        },
                        onInitialized: function(e) {
                            dataGridquestioncf = e.component;
                        },
                        onContentReady: function(e){
                            moveEditColumnToLeft(e.component);
                        },
                        onToolbarPreparing: function(e) {
                            e.toolbarOptions.items.unshift({						
                                location: "after",
                                widget: "dxButton",
                                options: {
                                    hint: "Refresh Data",
                                    icon: "refresh",
                                    onClick: function() {
                                        dataGridquestioncf.refresh();
                                    }
                                }
                            });
                        },
                        onEditorPrepared: function (e) {
                        },
                        onCellPrepared: function (e) {
                        },
                        onDataErrorOccurred: function(e) {
                            // Menampilkan pesan kesalahan
                            console.log("Terjadi kesalahan saat memuat data (6):", e.error.message);
                    
                            // Memuat ulang DataGrid
                            dataGridquestioncf.refresh();
                        }
                    }).appendTo(containerdetail);

                    return containerdetail
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
                            allowAdding: (((isMine == 1) && mode == 'view') ? true : (isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowUpdating: (((isMine == 1) && mode == 'view') ? true : (isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                            allowDeleting: (((isMine == 1) && mode == 'view') ? true : (isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 ? true : false),
                        },
                        paging: { enabled: true, pageSize: 10 },
                        columns: [
                            { 
                                caption: 'Attachment',
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
    // btnSubmit.prop('disabled', true);
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

    sendRequest(apiurl + "/capexcheck/"+reqid, "POST").then(function(response){
        if(response.status == 'error') {
            btnSubmit.prop('disabled', false);
        } else {
            Swal.fire({
                title: 'Are you sure?',
                text: "Are you sure you want to send this submission?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, send it!'
              }).then((result) => {
                if (result.isConfirmed) {
                    // showLoadingScreen();
                    sendRequest(apiurl + "/submissionrequest/"+reqid+"/"+modelclass, "POST", {
                        requestStatus:1,
                        action: actionForm,
                        approvalAction: (valapprovalAction == null) ? 1 : parseInt(valapprovalAction),
                        approvalType: valApprovalType,
                        remarks: valremarks
                    }).then(function(response){
                            if(response.status == 'error') {
                                btnSubmit.prop('disabled', false);
                                hideLoadingScreen();
                            } else {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Saved',
                                    text: 'The submission has been submited.',
                                });
                                popup.hide();
                                hideLoadingScreen();
                            }
                    });
                } else {
                    btnSubmit.prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Cancelled',
                        text: 'The submission has been cancelled.',
                        confirmButtonColor: '#3085d6'
                    });
                    hideLoadingScreen();
                }
              });
        }
    });

    

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
  
        cellElement.append(fileUploaderElement);
        cellElement.append(buttonElement);
  
}

// Fungsi untuk generate array tahun
function generateYearOptions() {
    const currentYear = new Date().getFullYear(); // Tahun sekarang (2025)
    const years = [];
    
    for (let i = 0; i <= 5; i++) {
        const year = currentYear - i;
        years.push({
            value: year,
            text: year.toString()
        });
    }
    
    return years;
}