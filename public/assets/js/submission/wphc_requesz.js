var modname = 'wphc_request';
var modelclass = 'Wphc';
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

                var isMine = options.data.isMine;
                var isPendingOnMe = options.data.isPendingOnMe;
                var reqid = options.data.id;
                var reqstatus = options.data.requestStatus;
                var mode = (reqstatus == 0 || reqstatus == 2 && (isMine == 1)) ? 'edit' : (reqstatus == 1 && ((isMine == 0 && isPendingOnMe == 1) || (isMine == 1 && isPendingOnMe == 1)) ? 'approval' : 'view');
                var arrColor = [
                    "btn-secondary",
                    (mode == 'approval' && reqstatus == 1) ? "btn-danger" : "btn-primary",
                    "btn-warning",
                    "btn-success",
                    "btn-danger",
                ];

                var viewIcon = (mode == 'approval' && reqstatus == 1) ? "fa-check" : "fa-search";

                $('<button class="btn ' + arrColor[reqstatus] + '" id="btnreqid' + reqid + '"><i class="fa ' + viewIcon + '"></i></button>').on('dxclick', function (evt) {
                    evt.stopPropagation();

                    popup.option({
                        contentTemplate: () => popupContentTemplate(reqid, mode, options),
                    });
                    popup.show();

                }).appendTo(container);
                if ((reqstatus == 1 || reqstatus == 2) && ((isMine == 1 && (isPendingOnMe == 0 || isPendingOnMe == null)))) {
                    $('<button class="btn btn-danger" id="btnreqid' + reqid + '" style="margin-left: 3px;">Cancel</button>').on('dxclick', function (evt) {
                        evt.stopPropagation();

                        var result = confirm('Are you sure you want to cancel this submission ?');

                        if (result) {
                            sendRequest(apiurl + "/submissionrequest/" + reqid + "/" + modelclass, "POST", {
                                requestStatus: 0,
                                action: 'submission',
                                approvalAction: 0
                            }).then(function (response) {
                                if (response.status != 'error') {
                                    dataGrid.refresh();
                                }
                            });
                        } else {
                            alert('Cancelled.');
                        }

                    }).appendTo(container);
                }

            }
        },
        {
            caption: "Name",
            dataField: 'user.fullname',
            alignment: "left"
        },
        {
            caption: "Work Date",
            dataField: 'wphc_detail.work_date',
            alignment: "left"
        },
        {
            caption: 'BU',
            dataField: 'bu',
            alignment: "left"
        },
        {
            caption: 'Sector',
            dataField: "sector",
            alignment: "left",
        },
        {
            dataField: 'requestStatus',
            encodeHtml: false,
            allowFiltering: false,
            allowHeaderFiltering: true,
            alignment: "left",
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
        {
            dataField: "created_at",
            dataType: "date",
            format: "dd-MM-yyyy",
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
            // Pastikan 'data' adalah array objek { id, fullname, sapid, companycode, … }
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
                                    caption: 'Create for other:',
                                    dataField: 'employee_id',
                                    lookup: {
                                        dataSource: listOption('/list-employee', 'id', 'fullname'),
                                        valueExpr: 'id',
                                        displayExpr: item => item ? `${item.fullname} (${item.sapid})` : ''
                                    },
                                    setCellValue: function (rowData, value) {
                                        rowData.employee_id = value;
                                        const emp = employeeCache.find(e => e.id === value);
                                        if (emp) {
                                            rowData.bu = emp.companycode;
                                            // rowData.DeptHead = emp.deptheadName - > id;
                                            const deptHead = employeeCache.find(e => e.fullname === emp.deptheadName);
                                            rowData.DeptHead = deptHead ? deptHead.id : null;

                                            rowData.sector = ["IHM", "AHL", "KPSI", "NKL"].includes(emp.companycode) ?
                                                "HO" :
                                                emp.companycode;

                                            rowData.level = emp.level_id;

                                            // mapping category_id berdasarkan level
                                            if (['1', '2', '3'].includes(String(emp.level_id))) {
                                                rowData.category_id = 30;
                                            } else if (String(emp.level_id) === '4') {
                                                rowData.category_id = 32;
                                            } else {
                                                rowData.category_id = null; // fallback kalau level tidak sesuai
                                            }
                                        }
                                    }
                                },
                                {
                                    caption: 'BU',
                                    dataField: 'bu',
                                    editorOptions: {
                                        disabled: true
                                    }
                                },
                                {
                                    caption: 'Superior',
                                    dataField: 'Superior',
                                    lookup: {
                                        dataSource: listOption('/list-employee', 'id', 'fullname'),
                                        valueExpr: 'id',
                                        displayExpr: function (item) {
                                            return item ? item.fullname + " (" + item.sapid + ")" : "";
                                        }
                                    }
                                },
                                {
                                    caption: 'Department Head',
                                    dataField: 'DeptHead',
                                    editorOptions: {
                                        disabled: true
                                    },
                                    lookup: {
                                        dataSource: listOption('/list-employee', 'id', 'fullname'),
                                        valueExpr: 'id',
                                        displayExpr: function (item) {
                                            return item ? item.fullname + " (" + item.sapid + ")" : "";
                                        }
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
                                if ((e.dataField == "Superior" || e.dataField == "employee_id") && e.parentType == "dataRow") {
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

                                    // Inject companycode ke request_wphc.bu
                                    if (e.row && e.row.data) {
                                        e.row.data.request_wphc = e.row.data.request_wphc || {};
                                        e.row.data.request_wphc.bu = selectedData.companycode;
                                    }
                                    console.log("Injected to:", e.row.data.request_wphc);

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
const isSunday = (date) => date && date.getDay() === 0;

const notifyDisableDate = () => {
    DevExpress.ui.notify('Tidak bisa membuat WPHC di hari kerja.', 'warning', 2000);
};

function getWeekKey(d) {
    const year = d.getFullYear();
    // ISO week calculation is not strictly necessary here; we just need consistent week grouping by year+week number
    const week = Math.ceil(((d - new Date(year, 0, 1)) / 86400000 + new Date(year, 0, 1).getDay() + 1) / 7);
    return `${year}-W${week}`;
}

const infoContentcontract = $("<div id='infoContentcontract'>");

if (data.ID === 2) {
    console.log("🔄 Memulai load data WPHC untuk scheduler (reqid):", reqid);

    // Load existing detail records to analyze filled weeks and as the data source.
    // storewithmodule(...) expected to return a DevExpress store (CustomStore).
    const detailsStore = storewithmodule('wphc_detail', modelclass, reqid);

    // Load the current detail data for analysis of filled weeks
    detailsStore.load().done(existingData => {
        console.log("✅ Data WPHC berhasil dimuat untuk analisis:", existingData);

        const filledWeeks = new Set();
        existingData.forEach(item => {
            try {
                const start = new Date(item.startDate);
                const wk = getWeekKey(start);
                filledWeeks.add(wk);
                console.log(`📌 Found WPHC entry: startDate=${item.startDate} => weekKey=${wk}`);
            } catch (err) {
                console.warn("⚠️ Skip invalid item when building filledWeeks", item, err);
            }
        });

        // Add scheduler container to DOM first (to avoid render issues)
        const schedulerElement = $("<div id='formcontract'>");
        infoContentcontract.append(schedulerElement);

        // Render scheduler in next tick to ensure DOM attachment
        setTimeout(() => {
            console.log("🛠️ Rendering scheduler now (after DOM ready).");

            // Use a DevExtreme DataSource backed by the detailsStore so scheduler can CRUD via the store
            const detailsDataSource = new DevExpress.data.DataSource({ store: detailsStore });

            // define window range for next 2 weeks (from today)
            const today = new Date();
            const twoWeeksFromToday = new Date(today);
            twoWeeksFromToday.setDate(today.getDate() + 14);

            schedulerElement.dxScheduler({
                dataSource: detailsDataSource,
                keyExpr: "id",
                views: ["month"],
                currentView: "month",
                currentDate: new Date(),
                startDayHour: 7,
                endDayHour: 18,
                height: 600,
                startDateExpr: "startDate",
                endDateExpr: "endDate",
                textExpr: "text",
                editing: {
                    allowAdding: true,
                    allowUpdating: true,
                    allowDeleting: true,
                },
                showAllDayPanel: false,
                showCurrentTimeIndicator: true,
                shadeUntilCurrentTime: true,
                maxAppointmentsPerCell: "unlimited",

                // Visual shading and clickability logic
                dataCellTemplate(itemData, itemIndex, itemElement) {
                    // itemData.startDate may be present depending on view; try both
                    const date = itemData.startDate || itemData.date;
                    if (!date) return;

                    const cellDate = new Date(date);
                    const isSundayCell = isSunday(cellDate);
                    const isDisabled = !isSundayCell;

                    // always show the date in each cell (prepend so appointments don't overwrite)
                    const dateLabel = $("<div>")
                        .addClass("dx-scheduler-date-table-cell-text")
                        .css({ fontSize: "10px", padding: "2px", fontWeight: 600 })
                        .text(cellDate.getDate());
                    itemElement.prepend(dateLabel);

                    if (isDisabled) {
                        // Shade weekdays so users know they can't pick these
                        itemElement.css({
                            backgroundImage: "repeating-linear-gradient(45deg, #f0f0f0, #f0f0f0 6px, #e9e9e9 6px, #e9e9e9 12px)",
                            backgroundSize: "12px 12px",
                            color: "#999"
                        });
                    } else {
                        // For Sundays, check "previous week filled" rule but only if current Sunday is within next 2 weeks
                        // Calculate previous week key
                        const prevWeekDate = new Date(cellDate);
                        prevWeekDate.setDate(cellDate.getDate() - 7);
                        const prevWeekKey = getWeekKey(prevWeekDate);

                        // only consider disabling if this sunday is within [today, today + 14 days]
                        if (cellDate >= today && cellDate <= twoWeeksFromToday && filledWeeks.has(prevWeekKey)) {
                            console.log(`⛔ Disabling Sunday ${cellDate.toDateString()} because previous week ${prevWeekKey} is filled and within 2-week window.`);
                            itemElement.css({
                                backgroundColor: "#ffe0e0",
                                pointerEvents: "none",
                                cursor: "not-allowed",
                                opacity: 0.6
                            });
                            itemElement.attr("title", "Tidak bisa ambil WPHC dua minggu berturut-turut dalam rentang 2 minggu ke depan");
                        }
                    }
                },

                // Month view date cell (the non-appointment cell)
                dateCellTemplate(itemData, itemIndex, itemElement) {
                    const date = itemData.date;
                    if (!date) return;
                    const cellDate = new Date(date);
                    const isDisabled = !isSunday(cellDate);

                    const element = $(`<div>${cellDate.getDate()}</div>`).css({ padding: "2px", fontSize: "11px", fontWeight: 600 });

                    if (isDisabled) {
                        element.css({
                            backgroundImage: "repeating-linear-gradient(45deg, #f0f0f0, #f0f0f0 6px, #e9e9e9 6px, #e9e9e9 12px)",
                            color: "#999",
                            opacity: 0.8
                        });
                    } else {
                        // For Sunday cells, check previous-week rule only when within 2-week window
                        const prevWeek = new Date(cellDate);
                        prevWeek.setDate(cellDate.getDate() - 7);
                        const prevWeekKey = getWeekKey(prevWeek);
                        if (cellDate >= today && cellDate <= twoWeeksFromToday && filledWeeks.has(prevWeekKey)) {
                            console.log(`⛔ dateCell: Disable Sunday ${cellDate.toDateString()} because prev week ${prevWeekKey} filled.`);
                            element.css({
                                backgroundColor: "#ffe0e0",
                                pointerEvents: "none",
                                cursor: "not-allowed",
                                opacity: 0.6
                            });
                            element.attr("title", "Tidak bisa ambil WPHC dua minggu berturut-turut dalam rentang 2 minggu ke depan");
                        }
                    }

                    itemElement.append(element);
                },

                // When form opens, ensure only Sundays allowed and set proper form fields
                onAppointmentFormOpening(e) {
                    const appt = e.appointmentData || {};
                    const start = new Date(appt.startDate || appt.start);
                    console.debug("onAppointmentFormOpening appointmentData:", appt);

                    if (!isSunday(start)) {
                        console.warn("Attempt to open appointment form for non-Sunday:", start);
                        e.cancel = true;
                        notifyDisableDate();
                        return;
                    }

                    // If the Sunday is within 2-week window and prev week is filled, disallow editing via form too
                    const prevWeekDate = new Date(start);
                    prevWeekDate.setDate(start.getDate() - 7);
                    const prevWeekKey = getWeekKey(prevWeekDate);
                    if (start >= today && start <= twoWeeksFromToday && filledWeeks.has(prevWeekKey)) {
                        console.warn("Appointment form blocked because previous week is filled:", prevWeekKey);
                        e.cancel = true;
                        DevExpress.ui.notify('Tidak bisa ambil WPHC dua minggu berturut-turut dalam rentang 2 minggu ke depan', 'warning', 3000);
                        return;
                    }

                    // Configure form fields
                    e.form.option("items", [
                        {
                            label: { text: "Start Date" },
                            dataField: "startDate",
                            editorType: "dxDateBox",
                            editorOptions: { type: "datetime" }
                        },
                        {
                            label: { text: "End Date" },
                            dataField: "endDate",
                            editorType: "dxDateBox",
                            editorOptions: { type: "datetime" }
                        },
                        {
                            label: { text: "Reason" },
                            dataField: "text",
                            editorType: "dxTextBox"
                        }
                    ]);
                },

                // Validate before adding
                onAppointmentAdding(e) {
                    const start = new Date(e.appointmentData.startDate || e.startDate || e.appointmentData.start);
                    console.debug("onAppointmentAdding payload:", e.appointmentData);
                    if (!isSunday(start)) {
                        console.warn("Blocked adding on non-Sunday:", start);
                        e.cancel = true;
                        notifyDisableDate();
                        return;
                    }

                    // previous week rule only for appointments in next 2 weeks
                    const prevWeek = new Date(start);
                    prevWeek.setDate(start.getDate() - 7);
                    const prevWeekKey = getWeekKey(prevWeek);

                    if (start >= today && start <= twoWeeksFromToday && filledWeeks.has(prevWeekKey)) {
                        console.warn("Blocked adding because previous week filled:", prevWeekKey);
                        e.cancel = true;
                        DevExpress.ui.notify('Tidak bisa ambil WPHC dua minggu berturut-turut dalam rentang 2 minggu ke depan', 'warning', 3000);
                        return;
                    }
                },

                // After adding on UI, persist to store/back-end
                onAppointmentAdded(e) {
                    console.debug("onAppointmentAdded (UI) appointmentData:", e.appointmentData);
                    const payload = Object.assign({}, e.appointmentData || {});
                    // ensure we link to parent wphc header
                    payload.wphc_id = reqid;

                    // Insert using the store so backend API is used consistently
                    console.log("📤 Inserting new WPHC detail to store:", payload);
                    detailsStore.insert(payload).done(result => {
                        console.log("✅ Insert success:", result);
                        // Refresh datasource to reflect new id and keep filledWeeks accurate
                        detailsDataSource.load().done(newData => {
                            // update filledWeeks set with newly inserted entry (if any)
                            try {
                                if (result && result.startDate) {
                                    const wk = getWeekKey(new Date(result.startDate));
                                    filledWeeks.add(wk);
                                    console.log("📌 filledWeeks updated with:", wk);
                                }
                            } catch (err) {
                                console.warn("⚠️ Failed to update filledWeeks from insert result", err);
                            }
                        });
                    }).fail(err => {
                        console.error("❌ Insert failed:", err);
                        DevExpress.ui.notify('Gagal menyimpan WPHC ke server.', 'error', 3000);
                        // Force reload UI datasource to avoid stale UI state
                        detailsDataSource.load();
                    });
                },

                // Persist updates
                onAppointmentUpdating(e) {
                    console.debug("onAppointmentUpdating event:", e);
                    const id = e.appointmentData && (e.appointmentData.id || e.appointmentData.ID || e.appointmentData.key) || e.key;
                    const newData = e.newData || {};
                    if (!id) {
                        console.warn("Cannot update appointment - no id found", e);
                        return;
                    }

                    // Validate date change (only Sundays allowed)
                    if (newData.startDate) {
                        const newStart = new Date(newData.startDate);
                        if (!isSunday(newStart)) {
                            e.cancel = true;
                            DevExpress.ui.notify('Hanya hari Minggu yang dapat dipilih untuk WPHC.', 'warning', 3000);
                            return;
                        }
                        // check prev-week rule for new date when within 2-week window
                        const prevWeekDate = new Date(newStart);
                        prevWeekDate.setDate(newStart.getDate() - 7);
                        const prevWeekKey = getWeekKey(prevWeekDate);
                        if (newStart >= today && newStart <= twoWeeksFromToday && filledWeeks.has(prevWeekKey)) {
                            e.cancel = true;
                            DevExpress.ui.notify('Tidak bisa ambil WPHC dua minggu berturut-turut dalam rentang 2 minggu ke depan', 'warning', 3000);
                            return;
                        }
                    }

                    console.log("🔁 Updating store id:", id, "with", newData);
                    detailsStore.update(id, newData).done(res => {
                        console.log("✅ Update success:", res);
                        detailsDataSource.load();
                    }).fail(err => {
                        console.error("❌ Update failed:", err);
                        DevExpress.ui.notify('Gagal memperbarui WPHC.', 'error', 3000);
                        detailsDataSource.load();
                    });
                },

                // Persist deletes
                onAppointmentDeleting(e) {
                    console.debug("onAppointmentDeleting:", e);
                    const id = e.appointmentData && (e.appointmentData.id || e.appointmentData.ID || e.appointmentData.key) || e.key;
                    if (!id) {
                        console.warn("Cannot delete appointment - no id found", e);
                        return;
                    }
                    console.log("🗑️ Removing id:", id);
                    detailsStore.remove(id).done(res => {
                        console.log("✅ Remove success:", res);
                        // remove any week record if present and refresh datasource
                        detailsDataSource.load().done(newData => {
                            try {
                                const removedStart = e.appointmentData.startDate;
                                if (removedStart) {
                                    const wk = getWeekKey(new Date(removedStart));
                                    if (filledWeeks.has(wk)) {
                                        filledWeeks.delete(wk);
                                        console.log("📌 removed week from filledWeeks:", wk);
                                    }
                                }
                            } catch (err) {
                                console.warn("⚠️ Failed to update filledWeeks after remove", err);
                            }
                        });
                    }).fail(err => {
                        console.error("❌ Remove failed:", err);
                        DevExpress.ui.notify('Gagal menghapus WPHC.', 'error', 3000);
                        detailsDataSource.load();
                    });
                },

                appointmentTemplate(modelData, itemIndex, container) {
                    container.append(
                        $("<div>").addClass("dx-scheduler-appointment-content").text(modelData.text)
                    );
                }
            });

            console.log("✅ Scheduler rendered and initialized.");
        }, 0); // Delay render 1 tick
    }).fail(err => {
        console.error("❌ Gagal load detailsStore untuk reqid", reqid, err);
        // still append empty scheduler but disabled
        infoContentcontract.append($("<div>").text("Gagal memuat data WPHC. Silakan coba lagi."));
    });

    return infoContentcontract;


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

let logIssueData = [];

$('#logIssue').click(function () {
  const modal = new bootstrap.Modal(document.getElementById('logIssueModal'));
  modal.show();
  showLoadingScreen();

  $.get('api/logreportwphc', function (response) {
    hideLoadingScreen();

    const data = response.data || [];
    if (!Array.isArray(data)) {
      Swal.fire({ icon: 'error', title: 'Gagal Memuat Data', text: 'Data tidak valid.' });
      return;
    }

    // Hitung status aktif berdasarkan work_date + 3 bulan >= hari ini
    const now = new Date();
        logIssueData = data.map(item => {
        const workDate = new Date(item.work_date);
        const aktifUntil = new Date(workDate);
        aktifUntil.setMonth(aktifUntil.getMonth() + 3);

        const isAktif = aktifUntil >= now;

        const diffTime = aktifUntil.getTime() - now.getTime();
        const sisaHari = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        const aktifSampaiDengan = aktifUntil.getDate().toString().padStart(2, '0') + '-' +
                                    (aktifUntil.getMonth() + 1).toString().padStart(2, '0') + '-' +
                                    aktifUntil.getFullYear();

        return {
            ...item,
            status_wphc_aktif: isAktif ? 'aktif' : 'non-aktif',
            aktif_sampai_dengan: aktifSampaiDengan,
            sisa_hari_aktif: sisaHari
        };
    });





    const activeCount = logIssueData.filter(item => item.status_wphc_aktif === 'aktif').length;
    const inactiveCount = logIssueData.filter(item => item.status_wphc_aktif !== 'aktif').length;

    $('#wphcActiveCount').text(activeCount);
    $('#wphcInactiveCount').text(inactiveCount);

    renderLogIssueAccordion('all');
  }).fail(function () {
    hideLoadingScreen();
    Swal.fire({ icon: 'error', title: 'Gagal Memuat Data', text: 'Terjadi kesalahan saat mengambil data.' });
  });
});

function renderLogIssueAccordion(filter) {
  const accordion = $('#wphcDetailsAccordion');
  accordion.empty();

  const filtered = logIssueData.filter(item => {
    if (filter === 'all') return true;
    return item.status_wphc_aktif === filter;
  });

  filtered.forEach((item, index) => {
    const collapseId = `collapseIssue${index}`;
    const headingId = `headingIssue${index}`;
    const badgeClass = item.status_wphc_aktif === 'aktif' ? 'bg-success' : 'bg-secondary';

    const html = `
      <div class="accordion-item">
        <h2 class="accordion-header" id="${headingId}">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
            Log Issue #${index + 1} - ${item.status_wphc_aktif.toUpperCase()}
          </button>
        </h2>
        <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}" data-bs-parent="#wphcDetailsAccordion">
          <div class="accordion-body">
            <ul class="list-group list-group-flush">
              <li class="list-group-item"><strong>Tanggal Kerja:</strong> ${item.work_date}</li>
              <li class="list-group-item"><strong>Status Aktif:</strong> <span class="badge ${badgeClass}">${item.status_wphc_aktif}</span></li>
              <li class="list-group-item"><strong>Aktif Sampai:</strong> ${item.aktif_sampai_dengan || '-'}</li>
              <li class="list-group-item"><strong>Alasan:</strong> ${item.text || '-'}</li>
              <li class="list-group-item"><strong>Catatan:</strong> ${item.remarks || '-'}</li>
              <li class="list-group-item"><strong>Dibuat Pada:</strong> ${item.created_at}</li>
              <li class="list-group-item"><strong>Terakhir Diperbarui:</strong> ${item.updated_at}</li>
            </ul>
          </div>
        </div>
      </div>
    `;
    accordion.append(html);
  });
}

// Event listener untuk card filter
$('#filterCardAktif').on('click', function () {
  renderLogIssueAccordion('aktif');
});

$('#filterCardNonAktif').on('click', function () {
  renderLogIssueAccordion('non-aktif');
});


function renderLogIssueAccordion(filter) {
  const accordion = $('#wphcDetailsAccordion');
  accordion.empty();

  const filtered = logIssueData.filter(item => {
    if (filter === 'all') return true;
    return item.status_wphc_aktif === filter;
  });

  filtered.forEach((item, index) => {
    const collapseId = `collapseIssue${index}`;
    const headingId = `headingIssue${index}`;
    const badgeClass = item.status_wphc_aktif === 'aktif' ? 'bg-success' : 'bg-secondary';

    const html = `
      <div class="accordion-item">
        <h2 class="accordion-header" id="${headingId}">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}">
            Log Issue #${index + 1} - ${item.status_wphc_aktif.toUpperCase()}
          </button>
        </h2>
        <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}" data-bs-parent="#wphcDetailsAccordion">
          <div class="accordion-body">
            <ul class="list-group list-group-flush">
              <li class="list-group-item"><strong>Tanggal Kerja:</strong> ${item.work_date}</li>
              <li class="list-group-item"><strong>Status Aktif:</strong> <span class="badge ${badgeClass}">${item.status_wphc_aktif}</span></li>
              <li class="list-group-item"><strong>Aktif Sampai:</strong> ${item.aktif_sampai_dengan || '-'}</li>
              <li class="list-group-item"><strong>Alasan:</strong> ${item.text || '-'}</li>
            </ul>
          </div>
        </div>
      </div>
    `;
    accordion.append(html);
  });
}

// Event listener untuk card filter
$('#filterCardAktif').on('click', function () {
  renderLogIssueAccordion('aktif');
});

$('#filterCardNonAktif').on('click', function () {
  renderLogIssueAccordion('non-aktif');
});



// let id = 1;
var dataGridhistory = $("#loghistory").dxDataGrid({
    dataSource: store('logreportwphc'),
    allowColumnReordering: false,
    allowColumnResizing: true,
    columnsAutoWidth: true,
    columnHidingEnabled: false,
    rowAlternationEnabled: true,
    wordWrapEnabled: false,
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
    columnFixing: {
        enabled: true,
    },
    editing: {
        useIcons: true,
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
        {
            caption: "#",
            width: 60,
            cellTemplate: function (container, options) {
                container.text(options.rowIndex + 1);
            }
        },
        {
            dataField: 'work_date',
            caption: "Work Date",
        },
        {
            dataField: 'remarks',
            caption: "Remarks",
        },
        {
            dataField: 'text',
            caption: "Objectives",
        },
        {
            dataField: 'status_wphc_aktif',
            caption: "Status",
        },
        {
            dataField: 'aktif_sampai_dengan',
            caption: "Issue Date",
        },
    ],
    export: {
        enabled: true,
        fileName: 'log history',
        excelFilterEnabled: true,
        allowExportSelectedData: false
    },
    onContentReady: function (e) {
        moveEditColumnToLeft(e.component);
    },
    onToolbarPreparing: function (e) {
        dataGridlog = e.component;

        e.toolbarOptions.items.unshift({
            location: "after",
            widget: "dxButton",
            options: {
                hint: "Refresh Data",
                icon: "refresh",
                onClick: function () {
                    dataGridlog.refresh();
                }
            }
        })
    },
}).dxDataGrid("instance");
// var infoContentcontract = $("<div id='infoContentcontract'>");
                    // if (data.ID == 2) {
                    //     let formDataContract = $("<div id='formcontract'>").dxDataGrid({
                    //         // dataSource: storedetail(modname, reqid),
                    //         dataSource: storewithmodule('wphc_detail', modelclass, reqid),
                    //         allowColumnReordering: true,
                    //         allowColumnResizing: true,
                    //         columnsAutoWidth: true,
                    //         rowAlternationEnabled: true,
                    //         wordWrapEnabled: true,
                    //         showBorders: true,
                    //         showColumnLines: true,
                    //         filterRow: {
                    //             visible: false
                    //         },
                    //         filterPanel: {
                    //             visible: false
                    //         },
                    //         headerFilter: {
                    //             visible: false
                    //         },
                    //         searchPanel: {
                    //             visible: true,
                    //             width: 240,
                    //             placeholder: 'Search...',
                    //         },
                    //         editing: {
                    //             useIcons: true,
                    //             mode: "cell",
                    //             allowAdding: true,
                    //             allowUpdating: true,
                    //             allowDeleting: true,
                    //         },
                    //         scrolling: {
                    //             mode: "virtual"
                    //         },
                    //         paging: {
                    //             pageSize: 5,
                    //         },
                    //         pager: {
                    //             visible: true,
                    //             allowedPageSizes: [5, 15, 'all'],
                    //             showPageSizeSelector: true,
                    //             showInfo: true,
                    //             showNavigationButtons: true,
                    //         },
                    //         columns: [
                    //             {
                    //                 caption: 'Work Date',
                    //                 dataField: 'work_date',
                    //                 width: 200,
                    //                 dataType: "date",
                    //                 // editorOptions: {
                    //                 //     min: new Date(new Date().setDate(new Date().getDate() - 7)) // hanya bisa pilih backdate maksimal 7 hari
                    //                 // },
                    //                 // validationRules: [
                    //                 //     {
                    //                 //         type: "required",
                    //                 //                 message: "Tanggal wajib diisi"
                    //                 //     },
                    //                 //     {
                    //                 //         type: "custom",
                    //                 //         validationCallback: function(e) {
                    //                 //             const today = new Date();
                    //                 //             const selected = new Date(e.value);
                    //                 //             const diff = (today - selected) / (1000 * 60 * 60 * 24); // selisih dalam hari
                    //                 //             return diff >= 0 && diff <= 7;
                    //                 //         },
                    //                 //         message: "Tanggal harus dalam rentang H-7 dari hari ini"
                    //                 //     }
                    //                 // ]
                    //             },
                    //             {
                    //                 caption: 'Reason',
                    //                 dataField: 'text',
                    //                 // width: 200,
                    //                 editorOptions: {
                    //                     readOnly: false,
                    //                 }
                    //             },
                    //             {
                    //                 caption: 'Remarks',
                    //                 dataField: 'remarks',
                    //                 // width: 200,
                    //                 editorOptions: {
                    //                     readOnly: false,
                    //                 }
                    //             },

                    //         ],
                    //         export: {
                    //             enabled: false,
                    //             fileName: modname,
                    //             excelFilterEnabled: true,
                    //             allowExportSelectedData: true
                    //         },
                    //         onInitialized: function (e) {
                    //             dataGriddetail = e.component;
                    //         },
                    //         onContentReady: function (e) {
                    //             moveEditColumnToLeft(e.component);
                    //         },
                    //         onToolbarPreparing: function (e) {
                    //             e.toolbarOptions.items.unshift({
                    //                 location: "after",
                    //                 widget: "dxButton",
                    //                 options: {
                    //                     hint: "Refresh Data",
                    //                     icon: "refresh",
                    //                     onClick: function () {
                    //                         dataGriddetail.refresh();
                    //                     }
                    //                 }
                    //             });
                    //         },                            
                    //         onDataErrorOccurred: function (e) {
                    //             // Menampilkan pesan kesalahan
                    //             console.log("Terjadi kesalahan saat memuat data (6):", e.error.message);

                    //             // Memuat ulang DataGrid
                    //             dataGriddetail.refresh();
                    //         }
                    //     }).appendTo(infoContentcontract)
                    //     return infoContentcontract