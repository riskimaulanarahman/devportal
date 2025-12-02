var modname = 'spkl_request';
var modelclass = 'Spkl';
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
    filterRow: {
        visible: true
    },
    filterPanel: {
        visible: true
    },
    headerFilter: {
        visible: true
    },
    searchPanel: {
        visible: true,
        width: 240,
        placeholder: 'Search...',
    },
    editing: {
        useIcons: true,
        mode: "popup",
        allowAdding: false,
        allowUpdating: false,
        allowDeleting: true,
    },
    scrolling: {
        mode: "virtual"
    },
    pager: {
        visible: false,
        showInfo: true,
    },
    columns: [{
            caption: "Code",
            dataField: 'code',
            alignment: "left"
        },
        {
            caption: 'Action',
            width: 140,
            cellTemplate: function (container, options) {
                const data = options.data;
                const isMine = data.isMine;
                const isPendingOnMe = data.isPendingOnMe;
                const reqid = data.id;
                const reqstatus = data.requestStatus;
                const tms = data.tms;

                // Mode default: view
                let mode = 'view';

                // Jika masih di fase 1 (tms == 0), gunakan logika requestStatus
                if (tms === 33) {
                    if ((reqstatus === 0 || reqstatus === 2) && isMine === 1) {
                        mode = 'edit';
                    } else if (reqstatus === 1 && isPendingOnMe === 1) {
                        mode = 'approval';
                    }
                }

                // Warna tombol berdasarkan mode dan status
                let buttonColor = "btn-primary";
                let buttonIcon = "fa-search";

                if (tms === 34) {
                    // Fase 2 → SPKL sudah full approve → tombol tetap hijau dan view
                    buttonColor = "btn-success";
                    buttonIcon = "fa-search";
                } else {
                    // Fase 1 → warna berdasarkan status
                    const arrColor = [
                        "btn-secondary", // Draft
                        (mode === 'approval' && reqstatus === 1) ? "btn-danger" : "btn-primary", // Waiting
                        "btn-warning", // Rework
                        "btn-success", // Approved
                        "btn-danger", // Rejected
                    ];
                    buttonColor = arrColor[reqstatus];
                    buttonIcon = (mode === 'approval' && reqstatus === 1) ? "fa-check" : "fa-search";
                }

                // Tombol utama (selalu ada)
                $('<button class="btn ' + buttonColor + '" id="btnreqid' + reqid + '"><i class="fa ' + buttonIcon + '"></i></button>')
                    .on('dxclick', function (evt) {
                        evt.stopPropagation();
                        popup.option({
                            contentTemplate: () => popupContentTemplate(reqid, mode, options),
                        });
                        popup.show();
                    })
                    .appendTo(container);

                // Tombol Cancel hanya muncul di fase 1 (tms == 0) dan status 1 atau 2, milik sendiri, dan bukan pending
                if (tms === 33 && (reqstatus === 1 || reqstatus === 2) && isMine === 1 && (!isPendingOnMe || isPendingOnMe === 0)) {
                    $('<button class="btn btn-danger" id="btnreqid' + reqid + '" style="margin-left: 3px;">Cancel</button>')
                        .on('dxclick', function (evt) {
                            evt.stopPropagation();
                            const result = confirm('Are you sure you want to cancel this submission ?');
                            if (result) {
                                sendRequest(apiurl + "/submissionrequest/" + reqid + "/" + modelclass, "POST", {
                                    requestStatus: 0,
                                    action: 'submission',
                                    approvalAction: 0
                                }).then(function (response) {
                                    if (response.status !== 'error') {
                                        dataGrid.refresh();
                                    }
                                });
                            } else {
                                alert('Cancelled.');
                            }
                        })
                        .appendTo(container);
                }
            }
        },
        {
            caption: "Creation Date",
            dataField: "created_at",
            dataType: "date",
            format: "dd-MM-yyyy",
        },
        {
            caption: 'BU',
            dataField: 'bu',
            alignment: "left"
        },
        {
            dataField: 'requestStatus',
            encodeHtml: false,
            allowFiltering: false,
            allowHeaderFiltering: true,
            alignment: "left",
            cellTemplate: function (container, options) {
                const status = options.value;
                const tms = options.data.tms;

                if (tms === 34) {
                    container.html("<span class='btn btn-success btn-xs btn-status'>Approved</span>");
                    return;
                }

                const arrText = [
                    "<span class='btn btn-secondary btn-xs btn-status'>Draft</span>",
                    "<span class='btn btn-primary btn-xs btn-status'>Waiting Approval</span>",
                    "<span class='btn btn-warning btn-xs btn-status'>Rework</span>",
                    "<span class='btn btn-success btn-xs btn-status'>Approved</span>",
                    "<span class='btn btn-danger btn-xs btn-status'>Rejected</span>",
                ];

                container.html(arrText[status]);
            }
        },
        {
            dataField: "approveddoc",
            caption: "Approval Doc",
            allowFiltering: false,
            allowSorting: false,
            formItem: {
                visible: false
            },
            cellTemplate: function (container, options) {
                if ((options.value != "") && (options.value)) {
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
    columnChooser: {
        enabled: true,
    },
    export: {
        enabled: true,
        fileName: modname,
        excelFilterEnabled: true,
        allowExportSelectedData: true
    },
    onContentReady: function (e) {
        moveEditColumnToLeft(e.component);
        runpopup();
    },
    onCellPrepared: function (e) {
        if (e.rowType == "data") {
            if (e.data.isParent === 1) {
                e.cellElement.css('background', 'rgba(128, 128, 0,0.1)')
            }
        }
    },
    onToolbarPreparing: function (e) {
        dataGrid = e.component;

        e.toolbarOptions.items.unshift({
            location: "after",
            widget: "dxButton",
            options: {
                hint: "Refresh Data",
                icon: "refresh",
                onClick: function () {
                    dataGrid.refresh();
                }
            }
        })
    },
    onDataErrorOccurred: function (e) {
        // Menampilkan pesan kesalahan
        console.log("Terjadi kesalahan saat memuat data (0):", e.error.message);

        // Memuat ulang Page
        location.reload();
    }
}).dxDataGrid("instance");

$('#btnadd').on('click', function () {
    sendRequest(apiurl + "/" + modname, "POST", {
        requestStatus: 0
    }).then(function (response) {
        const reqid = response.data.id;
        const mode = 'add';
        const options = {
            "data": {
                "isMine": 1
            }
        };
        popup.option({
            contentTemplate: () => popupContentTemplate(reqid, mode, options),
        });
        popup.show();
    });
})

const accordionItems = [{
        ID: 1,
        Title: '<i class="far fa-newspaper"> Form Data 1 </i>',
        visible: true
    },
    {
        ID: 2,
        Title: '<i class="fas fa-list-ul"> Detail Work Date Request </i>',
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

var dataSektor = [
    // { bu: 'IHM', sector: 'NKL' },
    {
        bu: 'IHM',
        sector: 'TRN'
    },
    {
        bu: 'IHM',
        sector: 'SPU'
    },
    {
        bu: 'IHM',
        sector: 'SNI'
    },
    {
        bu: 'IHM',
        sector: 'HO'
    },
    {
        bu: 'AHL',
        sector: 'SBG'
    },
    {
        bu: 'AHL',
        sector: 'SBS'
    },
    {
        bu: 'AHL',
        sector: 'SSP'
    },
    {
        bu: 'AHL',
        sector: 'NURSERY'
    },
    {
        bu: 'AHL',
        sector: 'HO'
    },
    {
        bu: 'NKL',
        sector: 'NKL'
    },
    {
        bu: 'KPSI',
        sector: 'KPSI'
    },
];

const popupContentTemplate = function (reqid, mode, options) {

    isMine = options.data.isMine;
    var isPendingOnMe = options.data.isPendingOnMe;
    isBCIDv = options.data.isBCIDv;

    var validationRules = [];
    var validationRules2 = [];

    popupid = reqid;

    console.log(mode)

    const scrollView = $('<div />');

    if ((isMine == 1 || isPendingOnMe == 1) && (mode == 'add' || mode == 'edit' || mode == 'approval')) {
        if ((isPendingOnMe == 1) && (mode == 'approval')) {
            var approvalOptions =
                '<div class="row">' +
                '<div class="col-md-6">' +
                '<label for="remarks">Approval Action :</label>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="approvalaction" id="rappraction1" value="3">' +
                '<label class="form-check-label" for="rappraction1">' +
                'Approved' +
                '</label>' +
                '</div>' +
                '<div class="form-check">' +
                '<input class="form-check-input" type="radio" name="approvalaction" id="rappraction2" value="2">' +
                '<label class="form-check-label" for="rappraction2">' +
                'Reworked' +
                '</label>' +
                '</div>' +
                '<div class="form-check mb-3">' +
                '<input class="form-check-input" type="radio" name="approvalaction" id="rappraction3" value="4">' +
                '<label class="form-check-label" for="rappraction3">' +
                'Rejected' +
                '</label>' +
                '</div>' +
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
            '<button id="btn-submit" type="button" onClick="btnreqsubmit(' + reqid + ',\'' + mode + '\')" class="btn btn-success waves-effect btn-label waves-light m-1"><i class="bx bx-check-double label-icon"></i> Submit Submission</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>');
    }

    if (options.data.requestStatus == 3 || (isPendingOnMe && isBCIDv)) {
        updateVisibleById(7, true);
    } else {
        updateVisibleById(7, false);
    }

    // Di luar definisi grid, saat page load
    let employeeCache = [];

    fetch('/devportal/api/list-getemployee')
        .then(res => res.json())
        .then(data => {
            employeeCache = data;
        })
        .catch(err => console.error('Gagal preload employee list:', err));

    function resolveEmployeeData(rowData, employee_id) {
        const emp = employeeCache.find(e => e.id === employee_id);
        if (!emp) return;

        rowData.bu = emp.companycode;

        const deptHead = employeeCache.find(e =>
            e.fullname.trim().toLowerCase() === emp.deptheadName.trim().toLowerCase()
        );
        rowData.DeptHead = deptHead ? deptHead.id : null;

        rowData.sector = ["IHM", "AHL", "KPSI", "NKL"].includes(emp.companycode) ? "HO" : emp.companycode;
        rowData.level = emp.level_id;

        rowData.category_id = ['1', '2', '3'].includes(String(emp.level_id)) ? 30 :
            String(emp.level_id) === '4' ? 32 : null;
    }
    scrollView.append("<hr>"),

        scrollView.append(

            $("<div>").dxAccordion({
                dataSource: accordionItems,
                animationDuration: 600,
                selectedItems: [accordionItems[0], accordionItems[1], accordionItems[2], accordionItems[3], accordionItems[4], accordionItems[5], accordionItems[6], accordionItems[7]],
                collapsible: true,
                multiple: true,
                itemTitleTemplate: function (data) {
                    return '<small style="margin-bottom:10px !important ;">' + data.Title + '</small>'
                },
                itemTemplate: function (data) {
                    var container = $("<div>");
                    if (data.ID == 1) {
                        if (mode == 'add' || mode == 'edit') {
                            $("<span style='color:red;font-size:11pt'>").html('Silahkan lengkapi <b><i class="far fa-newspaper tips"> Form Data </i></b> dan lampirkan <i class="fas fa-file tips"> Supporting Document </i> sebelum klik tombol <span class="tips"><i class="bx bx-check-double label-icon"></i> Submit Submission</span>').appendTo(container);
                        }
                        // console.log(storedetail);
                        var formData = $("<div id='formdata'>").dxDataGrid({
                            dataSource: storedetail(modname, reqid),
                            allowColumnReordering: true,
                            allowColumnResizing: true,
                            columnsAutoWidth: true,
                            rowAlternationEnabled: true,
                            wordWrapEnabled: true,
                            showBorders: true,
                            showColumnLines: true,
                            filterRow: {
                                visible: false
                            },
                            filterPanel: {
                                visible: false
                            },
                            headerFilter: {
                                visible: false
                            },
                            searchPanel: {
                                visible: false,
                                width: 240,
                                placeholder: 'Search...',
                            },
                            sorting: {
                                mode: "none" // or "multiple" | "none"
                            },
                            editing: {
                                useIcons: true,
                                mode: "cell",
                                allowAdding: false,
                                allowUpdating: false,
                                allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add') ? true : (admin == 1 ? true : false),
                                allowDeleting: false,
                            },
                            scrolling: {
                                mode: "virtual"
                            },
                            columns: [{
                                    caption: 'Code',
                                    dataField: 'code',
                                },
                                {
                                    caption: 'Creator',
                                    dataField: 'user.fullname',
                                    editorOptions: {
                                        readOnly: true
                                    },
                                },
                                {
                                    caption: 'Department Head',
                                    dataField: 'DeptHead',
                                    lookup: {
                                        dataSource: listOption('/list-employee', 'id', 'fullname'),
                                        valueExpr: 'id',
                                        displayExpr: function (item) {
                                            return item ? item.fullname + " (" + item.sapid + ")" : "";
                                        }
                                    }
                                },
                                {
                                    caption: 'Work Date',
                                    dataField: 'work_date',
                                    width: 200,
                                    dataType: "date",
                                    editorOptions: {
                                        min: new Date(new Date().setDate(new Date().getDate() + 1)) // besok
                                    }
                                },
                                {
                                    caption: 'Outstanding Tasks',
                                    dataField: 'remarks',
                                },
                            ],
                            export: {
                                enabled: false,
                                fileName: modname,
                                excelFilterEnabled: true,
                                allowExportSelectedData: true
                            },
                            onInitialized: function (e) {
                                dataGrid1 = e.component;
                            },
                            onContentReady: function (e) {
                                moveEditColumnToLeft(e.component);
                            },
                            onInitNewRow: function (e) {
                                if (e.data.employee_id) {
                                    resolveEmployeeData(e.data, e.data.employee_id);
                                }
                            },
                            onToolbarPreparing: function (e) {
                                e.toolbarOptions.items.unshift({
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function () {
                                            dataGrid1.refresh();
                                        }
                                    }
                                });
                            },
                            onEditorPreparing: function (e) {
                                if ((e.dataField == "DeptHead" || e.dataField == "employee_id" || e.dataField == "Superior") && e.parentType == "dataRow") {
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
                                                columns: ["sapid", "companycode", "fullname", "departmentname", "levels"],
                                                hoverStateEnabled: true,
                                                paging: {
                                                    enabled: true,
                                                    pageSize: 10
                                                },
                                                filterRow: {
                                                    visible: true
                                                },
                                                height: '90%',
                                                showRowLines: true,
                                                showBorders: true,
                                                selection: {
                                                    mode: "single"
                                                },
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
                                                    console.log(keys)
                                                    const hasSelection = keys.length;
                                                    args.component.option('value', hasSelection ? keys[0] : null);
                                                    if (hasSelection !== 0) {
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
                                        }).css({
                                            float: "right",
                                            marginTop: "10px"
                                        }).appendTo(container);
                                        return container;

                                    };
                                }
                            },
                            onSelectionChanged: function (selectedItems) {
                                const keys = selectedItems.selectedRowKeys;
                                const hasSelection = keys.length;

                                if (hasSelection !== 0) {
                                    const selectedData = selectedItems.selectedRowsData[0]; // ambil data lengkap dari row
                                    args.component.option('value', selectedData.id); // set employee_id
                                    console.log("Selected:", selectedItems.selectedRowsData[0]);

                                    // Inject companycode ke request_spkl.bu
                                    if (e.row && e.row.data) {
                                        e.row.data.request_spkl = e.row.data.request_spkl || {};
                                        e.row.data.request_spkl.bu = selectedData.companycode;
                                    }
                                    console.log("Injected to:", e.row.data.request_spkl);

                                    args.component.close();
                                } else {
                                    args.component.option('value', null);
                                }

                            },

                            onRowInserting: function (e) {
                                console.log('INSERT payload:', e.data);
                            },
                            onRowUpdating: function (e) {
                                console.log('UPDATE payload:', e.newData);
                            },

                            onCellPrepared: function (e) {
                                if (e.column.index == 0 && e.rowType == "data") {
                                    if (e.data.code === null) {
                                        $("#formdata").dxDataGrid('columnOption', 'code', 'visible', false);
                                    } else {
                                        $("#formdata").dxDataGrid('columnOption', 'code', 'visible', true);
                                    }
                                }
                                if (e.rowType == "data" && (e.column.index == 1 || e.column.index > 2 && e.column.index < 8)) {
                                    if (e.value === "" || e.value === null || e.value === undefined || /^\s*$/.test(e.value)) {
                                        e.cellElement.css({
                                            "backgroundColor": "#ffe6e6",
                                            "border": "0.5px solid #f56e6e"
                                        })
                                    }
                                }
                            },
                            onDataErrorOccurred: function (e) {
                                // Menampilkan pesan kesalahan
                                console.log("Terjadi kesalahan saat memuat data (1):", e.error.message);

                                // Memuat ulang DataGrid
                                dataGrid1.refresh();
                            }
                        }).appendTo(container)

                        return container;

                    }
                    var infoContentcontract = $("<div id='infoContentcontract'>");
                    if (data.ID == 2) {
                        let formDataContract = $("<div id='formcontract'>").dxDataGrid({
                            // dataSource: storedetail(modname, reqid),
                            dataSource: storewithmodule('spkl_detail', modelclass, reqid),
                            allowColumnReordering: true,
                            allowColumnResizing: true,
                            columnsAutoWidth: true,
                            rowAlternationEnabled: true,
                            wordWrapEnabled: true,
                            showBorders: true,
                            showColumnLines: true,
                            filterRow: {
                                visible: false
                            },
                            filterPanel: {
                                visible: false
                            },
                            headerFilter: {
                                visible: false
                            },
                            searchPanel: {
                                visible: true,
                                width: 240,
                                placeholder: 'Search...',
                            },
                            editing: {
                                useIcons: true,
                                mode: "batch",
                                allowAdding: true,
                                allowUpdating: ((isMine == 1) && mode == 'edit' || mode == 'add' ) ? true : (admin == 1 || developer ? true : false),
                                // allowUpdating: true,
                                allowDeleting: true,
                            },
                            scrolling: {
                                mode: "virtual"
                            },
                            paging: {
                                pageSize: 5,
                            },
                            pager: {
                                visible: true,
                                allowedPageSizes: [5, 15, 'all'],
                                showPageSizeSelector: true,
                                showInfo: true,
                                showNavigationButtons: true,
                            },
                            columns: [{
                                    caption: 'Create for other:',
                                    dataField: 'employee_id',
                                    lookup: {
                                        dataSource: listOption('/list-spkl', 'id', 'fullname'),
                                        valueExpr: 'id',
                                        displayExpr: item => item ? `${item.fullname} (${item.sapid || ''})` 
                                        : ''
                                    },
                                    setCellValue: function (rowData, value) {
                                        rowData.employee_id = value;
                                        const emp = employeeCache.find(e => e.id === value);
                                        if (emp) {
                                            rowData.bu = emp.companycode;
                                            rowData.sapid = emp.sapid; 
                                            rowData.position = emp.designationName; 

                                            rowData.sector = ["IHM", "AHL", "KPSI", "NKL"].includes(emp.companycode) ? "HO" : emp.companycode;
                                            rowData.level = emp.level_id;

                                            rowData.category_id = ['1', '2', '3'].includes(String(emp.level_id)) ? 30 :
                                                String(emp.level_id) === '4' ? 32 : null;

                                            const deptHead = employeeCache.find(e =>
                                                e.fullname.trim().toLowerCase() === emp.deptheadName ?.trim().toLowerCase()
                                            );
                                            rowData.DeptHead = deptHead ? deptHead.id : null;
                                        }
                                    },
                                    validationRules: [{
                                        type: "required"
                                    }]
                                },
                                {
                                    caption: 'Normal Hours Estimate (hrs)',
                                    dataField: 'EstimateNormalHours',
                                    dataType: 'number',
                                    validationRules: [{
                                        type: "required"
                                    }]
                                },
                                {
                                    caption: 'Overtime Hours Estimate (hrs)',
                                    dataField: 'EstimateOvertimeHours',
                                    dataType: 'number',
                                    validationRules: [{
                                        type: "required"
                                    }]
                                },
                                {
                                    caption: 'Target Work',
                                    dataField: 'Target',
                                    validationRules: [{
                                        type: "required"
                                    }]
                                },
                            ],
                            export: {
                                enabled: false,
                                fileName: modname,
                                excelFilterEnabled: true,
                                allowExportSelectedData: true
                            },
                            // onRowUpdating: function(e) {
                            //     const raw = e.newData.EstimateOvertimeHours;

                            //     if (raw === undefined) return;

                            //     const overtime = parseFloat(String(raw).trim());
                            //     console.log('Parsed Overtime:', overtime);

                            //     if (!isFinite(overtime)) return;

                            //     e.newData.moreThanTwoHours = overtime > 2 ? 1 : 0;
                            // },
                            // onRowInserting: function(e) {
                            //     const raw = e.data.EstimateOvertimeHours;
                            //     const overtime = parseFloat(String(raw).trim());

                            //     if (!isFinite(overtime)) return;

                            //     e.data.moreThanTwoHours = overtime > 2 ? 1 : 0;
                            // },
                            onInitialized: function (e) {
                                dataGriddetail = e.component;
                            },
                            onContentReady: function (e) {
                                moveEditColumnToLeft(e.component);
                            },
                            onToolbarPreparing: function (e) {
                                e.toolbarOptions.items.unshift({
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function () {
                                            dataGriddetail.refresh();
                                        }
                                    }
                                });
                            },
                            onDataErrorOccurred: function (e) {
                                // Menampilkan pesan kesalahan
                                console.log("Terjadi kesalahan saat memuat data (6):", e.error.message);

                                // Memuat ulang DataGrid
                                dataGriddetail.refresh();
                            }
                        }).appendTo(infoContentcontract)
                        return infoContentcontract

                    } else if (data.ID == 3) {
                        return $("<div id='formapproverlist'>").dxDataGrid({
                            dataSource: storewithmodule('approverlistrequest', modelclass, reqid),
                            allowColumnReordering: true,
                            allowColumnResizing: true,
                            columnsAutoWidth: true,
                            rowAlternationEnabled: true,
                            wordWrapEnabled: true,
                            showBorders: true,
                            filterRow: {
                                visible: false
                            },
                            filterPanel: {
                                visible: false
                            },
                            headerFilter: {
                                visible: false
                            },
                            searchPanel: {
                                visible: true,
                                width: 240,
                                placeholder: 'Search...',
                            },
                            editing: {
                                useIcons: true,
                                mode: "cell",
                                allowAdding: (admin == 1) ? true : false,
                                allowUpdating: (admin == 1) ? true : false,
                                allowDeleting: (admin == 1) ? true : false,
                            },
                            scrolling: {
                                mode: "virtual"
                            },
                            columns: [{
                                    caption: "Fullname",
                                    dataField: "approver_id",
                                    lookup: {
                                        dataSource: listOption('/list-approver/' + modelclass, 'id', 'fullname'),
                                        valueExpr: 'id',
                                        displayExpr: 'fullname',
                                    },
                                    validationRules: [{
                                        type: "required"
                                    }]
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
                            onInitialized: function (e) {
                                dataGridApproverList = e.component;
                            },
                            onContentReady: function (e) {
                                moveEditColumnToLeft(e.component);
                            },
                            onInitNewRow: function (e) {},
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
                                                columns: ["fullname", "ApprovalType"],
                                                hoverStateEnabled: true,
                                                paging: {
                                                    enabled: true,
                                                    pageSize: 10
                                                },
                                                filterRow: {
                                                    visible: true
                                                },
                                                height: '90%',
                                                showRowLines: true,
                                                showBorders: true,
                                                selection: {
                                                    mode: "single"
                                                },
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
                                                    if (hasSelection !== 0) {
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
                                        }).css({
                                            float: "right",
                                            marginTop: "10px"
                                        }).appendTo(container);
                                        return container;

                                    };
                                }
                            },
                            onToolbarPreparing: function (e) {
                                e.toolbarOptions.items.unshift({
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function () {
                                            dataGridApproverList.refresh();
                                        }
                                    }
                                })
                            },
                            onDataErrorOccurred: function (e) {
                                // Menampilkan pesan kesalahan
                                console.log("Terjadi kesalahan saat memuat data (3):", e.error.message);

                                // Memuat ulang DataGrid
                                dataGridApproverList.refresh();
                            }
                        })

                    } else if (data.ID == 4) {
                        return $("<div id='formhistorylist'>").dxDataGrid({
                            dataSource: storewithmodule('approverlisthistory', modelclass, reqid),
                            allowColumnReordering: true,
                            allowColumnResizing: true,
                            columnsAutoWidth: true,
                            rowAlternationEnabled: true,
                            wordWrapEnabled: true,
                            showBorders: true,
                            filterRow: {
                                visible: false
                            },
                            filterPanel: {
                                visible: false
                            },
                            headerFilter: {
                                visible: false
                            },
                            searchPanel: {
                                visible: true,
                                width: 240,
                                placeholder: 'Search...',
                            },
                            editing: {
                                useIcons: true,
                                mode: "cell",
                                allowAdding: false,
                                allowUpdating: false,
                                allowDeleting: false,
                            },
                            paging: {
                                enabled: true,
                                pageSize: 10
                            },
                            columns: [{
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
                            onInitialized: function (e) {
                                dataGridApproverHistory = e.component;
                            },
                            onContentReady: function (e) {
                                moveEditColumnToLeft(e.component);
                            },
                            onInitNewRow: function (e) {},
                            onToolbarPreparing: function (e) {
                                e.toolbarOptions.items.unshift({
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function () {
                                            dataGridApproverHistory.refresh();
                                        }
                                    }
                                })
                            },
                            onDataErrorOccurred: function (e) {
                                // menampilkan pesan kesalahan
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

function btnreqsubmit(reqid, mode) {
    console.log('reqidbtn', reqid);
    if (mode == 'add' || mode == 'edit') {
        var dataGridAssignment = $("#formdata").dxDataGrid("instance");
        var dataSource = dataGridAssignment.getDataSource();
        var rowCount = dataSource.items().length;

        if (rowCount === 0) {
            DevExpress.ui.dialog.alert("The Details does not exist. Please add one.", "Warning");
            return false;
        }
    }

    var btnSubmit = $('#btn-submit');
    btnSubmit.prop('disabled', true);

    var actionForm = (mode == 'approval') ? 'approval' : 'submission';

    if (mode == 'approval') {
        var valapprovalAction = $('input[name="approvalaction"]:checked').val(); // mengambil nilai dari radio button
        var valremarks = $('#remarks').val(); // mengambil nilai dari text area
        if (!valapprovalAction) {
            DevExpress.ui.dialog.alert("Please select approval action.", "Warning");
            btnSubmit.prop('disabled', false);
            return false;
        } else if (!valremarks) {
            DevExpress.ui.dialog.alert("Please enter remarks.", "Warning");
            btnSubmit.prop('disabled', false);
            return false;
        }

    }

    var valApprovalType = valapprovalAction == 3 ? 'Approved' : valapprovalAction == 2 ? 'Reworked' : valapprovalAction == 4 ? 'Rejected' : '';

    Swal.fire({
        title: 'Are you sure?',
        text: "Are you sure you want to send this submission?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#73a3cfff',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, send it!'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoadingScreen();
            sendRequest(apiurl + "/submissionrequest/" + reqid + "/" + modelclass, "POST", {
                requestStatus: 1,
                action: actionForm,
                approvalAction: (valapprovalAction == null) ? 1 : parseInt(valapprovalAction),
                approvalType: valApprovalType,
                remarks: valremarks
            }).then(function (response) {
                if (response.status == 'error') {
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
        fullScreen: false,
        onShowing: function (e) {},
        onShown: function (e) {},
        onHidden: function (e) {
            dataGrid.refresh();
        },
        toolbarItems: [{
                widget: 'dxButton',
                toolbar: 'bottom', // Set the button to the bottom toolbar
                location: 'after',
                options: {
                    text: "Fullscreen",
                    onClick: function () {
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