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
const infoContentcontract = $("<div id='infoContentcontract'>");

  if (data.ID === 2) {
    const detailsStore = storewithmodule("wphc_detail", modelclass, reqid);

    // ========= Helper umum =========
    const normalizeDate = (date) => {
      const d = new Date(date);
      d.setHours(0, 0, 0, 0);
      return d;
    };

    const formatDateKey = (date) => {
      const d = normalizeDate(date);
      return (
        d.getFullYear() +
        "-" +
        String(d.getMonth() + 1).padStart(2, "0") +
        "-" +
        String(d.getDate()).padStart(2, "0")
      );
    };

    const isSunday = (date) => normalizeDate(date).getDay() === 0;
    const isWeekday = (date) => {
      const day = normalizeDate(date).getDay();
      return day >= 1 && day <= 6;
    };

    const showError = (message) => {
      DevExpress.ui.notify({
        message,
        type: "error",
        displayTime: 3000,
        position: { my: "top center", at: "top center" }
      });
    };

    // Ambil semua Sunday dalam bulan yang sama dengan date
    const getMonthSundays = (date) => {
      const d = normalizeDate(date);
      const month = d.getMonth();
      const year = d.getFullYear();
      const sundays = [];

      const cursor = new Date(year, month, 1);
      cursor.setHours(0, 0, 0, 0);

      while (cursor.getMonth() === month) {
        if (cursor.getDay() === 0) {
          sundays.push(new Date(cursor.getTime()));
        }
        cursor.setDate(cursor.getDate() + 1);
      }
      return sundays;
    };

    // ========= Ambil holiday dari API =========
    $.getJSON("api/holiday", (response) => {
      const holidaysRaw = response?.data || [];
      const holidayDates = holidaysRaw.map(h => h.HolidayDate);

      detailsStore.load().done((items) => {
        let appointmentsNorm = Array.isArray(items)
          ? items.map(a => ({
              ...a,
              startDate: normalizeDate(a.startDate),
              endDate: a.endDate ? normalizeDate(a.endDate) : undefined
            }))
          : [];

        // Aturan enabled
        const isEnabledDate = (date) => {
          const d = normalizeDate(date);
          const key = formatDateKey(d);
          const isHoliday = holidayDates.includes(key);

          if (isHoliday) return true; // holiday selalu aktif
          if (isWeekday(d)) return false; // weekday non-holiday disable

          if (isSunday(d)) {
            const monthSundays = getMonthSundays(d);
            const idx = monthSundays.findIndex(s => s.getTime() === d.getTime());
            if (idx === -1) return false;
            if (idx === 0) return true; // Sunday pertama bulan → aktif

            const prevSunday = monthSundays[idx - 1];
            const prevKey = formatDateKey(prevSunday);

            const hasDataPrevSunday = appointmentsNorm.some(
              a => formatDateKey(a.startDate) === prevKey
            );
            return !hasDataPrevSunday; 
          }
          return false;
        };

        const isDisabledDate = (date) => !isEnabledDate(date);

        const schedulerElement = $("<div id='formcontract'>");
        infoContentcontract.append(schedulerElement);

        schedulerElement.dxScheduler({
          dataSource: new DevExpress.data.DataSource({ store: detailsStore }),
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

          // === Template cell kalender (visual) ===
          dataCellTemplate(cellData, cellIndex, cellElement) {
            const cellDate = normalizeDate(cellData.startDate);
            const enabled = isEnabledDate(cellDate);

            const element = $("<div>")
              .addClass("dx-scheduler-date-table-cell-text")
              .css({ fontSize: "10px", padding: "2px", fontWeight: 600 })
              .text(cellDate.getDate());

            if (!enabled) {
              element.css({
                backgroundColor: "#f0f0f0",
                color: "#999",
                opacity: 0.6,
                border: "1px solid #ddd",
                backgroundImage:
                  "repeating-linear-gradient(45deg, #f0f0f0, #f0f0f0 6px, #e0e0e0 6px, #e0e0e0 12px)"
              });
            } else {
              element.css({
                backgroundColor: "#e6ffe6",
                color: "#060",
                fontWeight: "bold"
              });
            }

            return cellElement.append(element);
          },

          // === Validasi ===
          onAppointmentFormOpening(e) {
            const date = normalizeDate(e.appointmentData.startDate);
            if (isDisabledDate(date)) {
              e.cancel = true;
              showError("Tanggal ini tidak dapat dipilih (aturan holiday & Sunday berturut-turut).");
            }
            const form = e.form;
            e.popup.option("title", "Form Pengajuan Jadwal");
            form.option("items", [
                {
                dataField: "text",
                label: { text: "Judul" },
                editorType: "dxTextBox",
                editorOptions: { placeholder: "Masukkan judul kegiatan" }
                },
                {
                dataField: "startDate",
                label: { text: "Tanggal Mulai" },
                editorType: "dxDateBox",
                editorOptions: { type: "datetime" }
                },
                {
                dataField: "endDate",
                label: { text: "Tanggal Selesai" },
                editorType: "dxDateBox",
                editorOptions: { type: "datetime" }
                },
                {
                dataField: "remarks",
                label: { text: "Remarks" },
                editorType: "dxTextArea",
                editorOptions: {
                    placeholder: "Tambahkan catatan atau remarks",
                    height: 80
                }
                }
            ]);
          },
          onAppointmentAdding(e) {
            const date = normalizeDate(e.appointmentData.startDate);
            if (isDisabledDate(date)) {
              e.cancel = true;
              showError("Tanggal ini tidak dapat dipilih (aturan holiday & Sunday berturut-turut).");
            }
          },
          onAppointmentAdded(e) {
            detailsStore.load().done((items) => {
                appointmentsNorm = Array.isArray(items)
                ? items.map(a => ({
                    ...a,
                    startDate: normalizeDate(a.startDate),
                    endDate: a.endDate ? normalizeDate(a.endDate) : undefined
                    }))
                : [];

                console.log("[AppointmentAdded] Reloaded appointmentsNorm:", appointmentsNorm.map(a => formatDateKey(a.startDate)));

                // Trigger repaint supaya cell template dievaluasi ulang
                schedulerElement.dxScheduler("instance").repaint();
            });
            },
          onAppointmentUpdating(e) {
            const date = normalizeDate(e.newData.startDate);
            if (isDisabledDate(date)) {
              e.cancel = true;
              showError("Tanggal ini tidak dapat dipilih (aturan holiday & Sunday berturut-turut).");
            }
          },

          onAppointmentDeleted(e) {
            const key = formatDateKey(normalizeDate(e.appointmentData.startDate));
            appointmentsNorm = appointmentsNorm.filter(
              a => formatDateKey(a.startDate) !== key
            );
            schedulerElement.dxScheduler("instance").repaint();
          }
        });
      });
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