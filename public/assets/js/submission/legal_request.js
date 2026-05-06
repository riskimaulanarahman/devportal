var modname = 'legalrequest';
var modelclass = 'Legal';
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
            caption: "Reference Number",
            dataField: 'code',
            // width: 180,
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
            caption: "Contractor",
            dataField: 'countersigningParty',
            // width: 180,
            alignment: "left"
        },
        {
            caption: "Title of Document",
            dataField: 'titleOfDocument',
            // width: 180,
            alignment: "left"
        },
        {
            caption: 'BU',
            dataField: 'bu',
            // width: 100,
            alignment: "left"
        },
        {
            caption: 'Creator Name',
            dataField: "user.fullname",
            // width: 180,
            alignment: "left",
        },
        {
            caption: 'RFC Number',
            dataField: 'rfc.RFCNo',
        },
        {
            caption: 'Contract No',
            dataField: 'contractNumber',
        },
        {
            caption: 'Start Contract',
            dataField: 'rfc.PeriodStart',
        },
        {
            caption: 'End Contract',
            dataField: 'rfc.PeriodEnd',
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
            caption: 'Next Approver',
            dataField: "nextApproverName",
            // width: 180,
            alignment: "left",
        },
        {
            caption: 'Last Approved',
            dataField: "lastApprovalDate",
            alignment: "left",
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
        Title: '<i class="fas fa-file"> Supporting Document (Surat Perjanjian & RFC / etc) </i>',
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
        sektor: 'TRN'
    },
    {
        bu: 'IHM',
        sektor: 'SPU'
    },
    {
        bu: 'IHM',
        sektor: 'SNI'
    },
    {
        bu: 'IHM',
        sektor: 'HO'
    },
    {
        bu: 'AHL',
        sektor: 'SBG'
    },
    {
        bu: 'AHL',
        sektor: 'SBS'
    },
    {
        bu: 'AHL',
        sektor: 'SSP'
    },
    {
        bu: 'AHL',
        sektor: 'NURSERY'
    },
    {
        bu: 'AHL',
        sektor: 'HO'
    },
    {
        bu: 'NKL',
        sektor: 'NKL'
    },
    {
        bu: 'KPSI',
        sektor: 'KPSI'
    },
    {
        bu: 'MHS',
        sektor: 'JLA'
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
                                    caption: 'Business Group',
                                    dataField: 'businessGroup',
                                    editorOptions: {
                                        readOnly: true,
                                    },
                                },
                                {
                                    caption: 'BU',
                                    dataField: 'bu',
                                    lookup: {
                                        dataSource: [{
                                            bu: 'IHM'
                                        }, {
                                            bu: 'AHL'
                                        }, {
                                            bu: 'NKL'
                                        }, {
                                            bu: 'KPSI'
                                        }, {
                                            bu: 'MHS'
                                        }],
                                        valueExpr: 'bu',
                                        displayExpr: 'bu',
                                    },
                                    setCellValue: function (rowData, value) {
                                        rowData.bu = value;
                                        if (value === "IHM") {
                                            rowData.sektor = "HO";
                                        } else if (value === "AHL") {
                                            rowData.sektor = "HO";
                                        } else if (value === "NKL") {
                                            rowData.sektor = "NKL";
                                        } else if (value === "PTSI") {
                                            rowData.sektor = "PTSI";
                                        } else if (value === "MHS") {
                                            rowData.sektor = "JLA";
                                        }
                                    },
                                    editorOptions: {
                                        readOnly: (mode == 'approval') ? true : false
                                        // readOnly: true
                                    },
                                    validationRules: [{
                                        type: "required"
                                    }]
                                },
                                // {
                                //     caption: 'Sektor',
                                //     dataField: 'sektor',
                                //     lookup: {
                                //         dataSource: function (options) {
                                //             return {
                                //                 store: {
                                //                     type: 'array',
                                //                     data: dataSektor
                                //                 },
                                //                 filter: options.data ? ["bu", "=", options.data.bu] : null
                                //             };
                                //         },
                                //         valueExpr: 'sektor',
                                //         displayExpr: 'sektor',
                                //     },
                                //     editorOptions: { 
                                //         readOnly: (mode == 'approval') ? true : false
                                //     },
                                //     validationRules: [{ type: "required" }]
                                // },               
                                {
                                    caption: 'Form Group',
                                    dataField: 'formGroup',
                                    lookup: {
                                        dataSource: [{
                                                formGroup: 'Capital Expenditure'
                                            },
                                            {
                                                formGroup: 'CCM Request'
                                            },
                                            {
                                                formGroup: 'Contract Review and Approval'
                                            },
                                            {
                                                formGroup: 'Bank Accounts'
                                            },
                                            {
                                                formGroup: 'Change of Company Particulars'
                                            },
                                            {
                                                formGroup: 'Legal Operational Site'
                                            },
                                            {
                                                formGroup: 'Other'
                                            },
                                        ],
                                        valueExpr: 'formGroup',
                                        displayExpr: 'formGroup',
                                    },
                                    editorOptions: {
                                        readOnly: false
                                    },
                                    validationRules: [{
                                        type: "required"
                                    }]
                                },
                                {
                                    caption: 'Form Type',
                                    dataField: 'formType',
                                    lookup: {
                                        dataSource: [{
                                                formType: 'Standard'
                                            },
                                            {
                                                formType: 'Non-Standard'
                                            },
                                            {
                                                formType: 'Advance'
                                            }
                                        ],
                                        valueExpr: 'formType',
                                        displayExpr: 'formType',
                                    },
                                    editorOptions: {
                                        readOnly: false
                                    },
                                    validationRules: [{
                                        type: "required"
                                    }]
                                },
                                {
                                    caption: 'Request Type',
                                    dataField: 'requestType',
                                    lookup: {
                                        dataSource: [{
                                                requestType: 'Budgeted'
                                            },
                                            {
                                                requestType: 'Unbudgeted - Swap Available'
                                            },
                                            {
                                                requestType: 'Unbudgeted - Swap Unavailable'
                                            }
                                        ],
                                        valueExpr: 'requestType',
                                        displayExpr: 'requestType',
                                    },
                                    validationRules: [{
                                        type: "required"
                                    }]
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
                            onInitNewRow: function (e) {},
                            onToolbarPreparing: function (e) {
                                e.toolbarOptions.items.unshift({
                                    location: "after",
                                    widget: "dxButton",
                                    options: {
                                        hint: "Refresh Data",
                                        icon: "refresh",
                                        onClick: function () {
                                            dataGrid1.refresh();
                                            dataGrid1a.refresh();
                                            dataGrid1b.refresh();
                                        }
                                    }
                                });
                            },
                            onEditorPreparing: function (e) {
                                if ((e.dataField == "depthead_id") && e.parentType == "dataRow") {
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
                            onRowUpdating: function (e) {

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

                        // space antara dua datagrid
                        $("<div style='height: 20px;'>").appendTo(container);

                        var secondGrid = $("<div id='secondGrid'>").dxDataGrid({
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
                                    caption: 'Date Of Document',
                                    dataField: "dateOfDocument",
                                    dataType: "date",
                                    editorOptions: {
                                        readOnly: (mode == 'approval') ? true : false
                                    },
                                },
                                {
                                    caption: 'Contractor',
                                    dataField: 'countersigningParty',
                                    editorType: 'dxTextArea',
                                    editorOptions: {
                                        height: 50,
                                    },
                                },
                                {
                                    caption: 'Business Type',
                                    dataField: 'businessType',
                                    editorOptions: {
                                        readOnly: true
                                    },
                                },
                                {
                                    caption: 'Title Of Document',
                                    dataField: 'titleOfDocument',
                                    editorType: 'dxTextArea',
                                    editorOptions: {
                                        height: 50,
                                        readOnly: false
                                    },
                                },
                                {
                                    caption: 'RFC Number',
                                    dataField: 'rfcNumber',
                                    lookup: {
                                        dataSource: listOption('/list-rfc', 'id', 'RFCNo'),
                                        valueExpr: 'id',
                                        displayExpr: function (item) {
                                            return item ? item.RFCNo + "(" + item.RateType + ")" + "/" + item.SKNo : "";
                                        }
                                    },
                                },
                                {
                                    caption: 'Contract Number',
                                    dataField: 'contractNumber',
                                    editorType: 'dxTextArea',
                                    height: 50,
                                    format: null
                                },
                                {
                                    dataField: 'sk',
                                    caption: 'SK / Non SK',
                                    editorType: 'dxSelectBox',
                                    lookup: {
                                        dataSource: [{
                                            sk: 'SK'
                                        }, {
                                            sk: 'Non SK'
                                        }],
                                        valueExpr: 'sk',
                                        displayExpr: 'sk',
                                    },
                                    setCellValue: function (rowData, value) {
                                        rowData.sk = value;
                                        if (value === "SK") {
                                            rowData.financialAmount = "0";
                                            rowData.skNumber = ""; // wajib isi manual
                                        } else if (value === "Non SK") {
                                            rowData.skNumber = "-";
                                            rowData.financialAmount = ""; // wajib isi manual
                                        }
                                    },
                                    editorOptions: {
                                        readOnly: (mode === 'approval')
                                    },
                                    // validationRules: [{ type: "required" }]
                                },
                                {
                                    dataField: 'skNumber',
                                    caption: 'Nomor SK',
                                    editorType: 'dxTextBox',
                                    editorOptions: {
                                        placeholder: 'Isi jika memilih SK',
                                        readOnly: (mode === 'approval')
                                    },
                                    validationRules: [{
                                        type: 'custom',
                                        validationCallback: function (e) {
                                            return e.data.sk !== 'SK' || !!e.value;
                                        },
                                        message: 'Nomor SK wajib diisi jika memilih SK.'
                                    }]
                                },
                                {
                                    dataField: 'financialAmount',
                                    caption: 'Financial Amount',
                                    editorType: 'dxNumberBox',
                                    editorOptions: {
                                        placeholder: 'Isi jika memilih Non SK',
                                        readOnly: (mode === 'approval')
                                    },
                                    showSpinButtons: true,
                                    format: {
                                        type: 'currency',
                                        precision: 0,
                                        currency: 'IDR'
                                    },
                                    validationRules: [{
                                        type: 'custom',
                                        validationCallback: function (e) {
                                            return e.data.sk !== 'Non SK' || !!e.value;
                                        },
                                        message: 'Financial Amount wajib diisi jika memilih Non SK.'
                                    }]
                                }

                            ],
                            export: {
                                enabled: false,
                                fileName: modname,
                                excelFilterEnabled: true,
                                allowExportSelectedData: true
                            },
                            onInitialized: function (e) {
                                dataGrid1a = e.component;
                            },
                            onContentReady: function (e) {
                                moveEditColumnToLeft(e.component);
                            },
                            onInitNewRow: function (e) {},
                            onEditorPreparing: function (e) {
                                if ((e.dataField == "rfcNumber") && e.parentType == "dataRow") {
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
                                                columns: ["RFCNo", "RateType", "SKNo"],
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
                            onRowUpdating: function (e) {

                            },
                            onCellPrepared: function (e) {
                                if (e.rowType == "data" && (e.column.index >= 0 && e.column.index < 7)) {
                                    if (e.value === "" || e.value === null || e.value === undefined || /^\s*$/.test(e.value)) {
                                        e.cellElement.css({
                                            "backgroundColor": "#ffe6e6",
                                            "border": "0.5px solid #f56e6e"
                                        })
                                    }
                                }
                            },
                            onDataErrorOccurred: function (e) {
                                console.log("Terjadi kesalahan saat memuat data (1.1):", e.error.message);
                                dataGrid1a.refresh();
                            }
                        }).appendTo(container)
                        $("<div style='height: 20px;'>").appendTo(container);

                        var thirdGrid = $("<div id='thirdGrid'>").dxDataGrid({
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
                                caption: 'Purpose',
                                dataField: 'purpose',
                                editorType: 'dxTextArea',
                                editorOptions: {
                                    autoResizeEnabled: true,
                                    minHeight: 90,
                                    maxHeight: 200,
                                    placeholder: 'Purpose...'
                                },
                                validationRules: [{
                                    type: "required",
                                    message: "Harap isi Purpose"
                                }]
                            }],
                            export: {
                                enabled: false,
                                fileName: modname,
                                excelFilterEnabled: true,
                                allowExportSelectedData: true
                            },
                            onInitialized: function (e) {
                                dataGrid1b = e.component;
                            },
                            onContentReady: function (e) {
                                moveEditColumnToLeft(e.component);
                            },
                            onInitNewRow: function (e) {},
                            onRowUpdating: function (e) {

                            },
                            onCellPrepared: function (e) {
                                if (e.rowType == "data" && (e.column.index >= 0 && e.column.index < 3)) {
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
                                console.log("Terjadi kesalahan saat memuat data (1.1):", e.error.message);

                                // Memuat ulang DataGrid
                                dataGrid1b.refresh();
                            }
                        }).appendTo(container)

                        return container;
                    } else if (data.ID == 2) {
                        var supporting = $("<div id='formattachment'>").dxDataGrid({
                            dataSource: storewithmodule('attachmentrequest', modelclass, reqid),
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
                                mode: "popup",
                                allowAdding: ((isMine == 1 && mode == 'view') ? true : (isMine == 1) && mode == 'edit' || mode == 'add') ? true : (admin == 1 ? true : false),
                                allowUpdating: ((isMine == 1 && mode == 'view') ? true : (isMine == 1) && mode == 'edit' || mode == 'add') ? true : (admin == 1 ? true : false),
                                allowDeleting: ((isMine == 1 && mode == 'view') ? true : (isMine == 1) && mode == 'edit' || mode == 'add') ? true : (admin == 1 ? true : false),
                            },
                            paging: {
                                enabled: true,
                                pageSize: 10
                            },
                            columns: [{
                                    caption: 'Attachment',
                                    dataField: "path",
                                    allowFiltering: false,
                                    allowSorting: false,
                                    cellTemplate: cellTemplate,
                                    editCellTemplate: editCellTemplate,
                                    validationRules: [{
                                        type: "required"
                                    }]
                                },
                                {
                                    dataField: "remarks",
                                    lookup: {
                                        dataSource: ['Surat Perjanjian', 'RFC', 'Supporting Document'],
                                        searchEnabled: false
                                    },
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
                            onInitialized: function (e) {
                                dataGridAttachment = e.component;
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
                                            dataGridAttachment.refresh();
                                        }
                                    }
                                })
                            },
                            onDataErrorOccurred: function (e) {
                                // Menampilkan pesan kesalahan
                                console.log("Terjadi kesalahan saat memuat data (2):", e.error.message);

                                // Memuat ulang DataGrid
                                dataGridAttachment.refresh();
                            }
                        })

                        return supporting;
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
    console.log('submit')
    var btnSubmit = $('#btn-submit');
    var valapprovalAction = $('input[name="approvalaction"]:checked').val(); // mengambil nilai dari radio button
    var valremarks = $('#remarks').val();

    if (mode == 'approval' && valapprovalAction == 3) {
        var fieldsToCheckGrid = [
            // { field: 'objective', name: 'Objective' },
            // { field: 'ranking', name: 'Category' },
            // { field: 'status_jdi', name: 'Status JDI' },
        ]
    }

    sendRequest(apiurl + "/submissioncheckfields/" + reqid + "/LEGAL", "POST", {
        fieldsToCheckGrid
    }).then(function (response) {
        if (response.status !== 'error') {

            btnSubmit.prop('disabled', true);

            var actionForm = (mode == 'approval') ? 'approval' : 'submission';

            if (mode == 'approval') {

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

            confirmAndSendSubmission(reqid, modelclass, actionForm, valapprovalAction, valApprovalType, valremarks);

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


function cellTemplate(container, options) {
    container.append('<a href="public/upload/' + options.value + '" target="_blank"><img src="public/assets/images/showfile.png" height="50" width="70"></a>');
}

function editCellTemplate(cellElement, cellInfo) {
    let buttonElement = document.createElement("div");
    buttonElement.classList.add("retryButton");
    let retryButton = $(buttonElement).dxButton({
        text: "Retry",
        visible: false,
        onClick: function () {
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
        accept: ".pptx,.ppt,.docx,.doc,.pdf,.xls,.xlsx,.csv,.png,.jpg,.jpeg,.zip",
        uploadMode: "instantly",
        name: "myFile",
        uploadUrl: apiurl + "/upload-berkas/" + modname,
        onValueChanged: function (e) {
            let reader = new FileReader();
            reader.onload = function (args) {
                imageElement.setAttribute('src', args.target.result);
            }
            reader.readAsDataURL(e.value[0]); // convert to base64 string
        },
        onUploaded: function (e) {

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
        onUploadError: function (e) {
            $path = "";
            DevExpress.ui.notify(e.request.response, "error");
        }
    }).dxFileUploader("instance");
    cellElement.append(fileUploaderElement);
    cellElement.append(buttonElement);

}