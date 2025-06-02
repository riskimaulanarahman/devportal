<input type="hidden" name="appenv" id="appenv" value="{{ env('APP_ENV') }}">
<input type="hidden" name="empid" id="empid" value="{{ ($employee) ? $employee->id : null }}">
<input type="hidden" name="usersid" id="usersid" value="{{ Auth::user()->id }}">
<input type="hidden" name="isadmin" id="isadmin" value="{{ Auth::user()->isAdmin }}">
<input type="hidden" name="isusername" id="isusername" value="{{ Auth::user()->username }}">
<input type="hidden" name="isdeveloper" id="isdeveloper" value="{{ ($developer) ? true : null }}">
<script>
    appenv = $('#appenv').val();
    admin = $('#isadmin').val();
    developer = $('#isdeveloper').val();
    valusername = $('#isusername').val();
    usersid = parseInt($('#usersid').val());
    empid = parseInt($('#empid').val());

    baseurl = window.location.origin+'/devportal';
    apiurl = window.location.origin+'/devportal/api';

    const layoutModeInput = $("input[name=layout-mode]:radio");
    const layoutWidthInput = $("input[name=layout-width]:radio");
    const layoutPositionInput = $("input[name=layout-position]:radio");
    const sidebarSizeInput = $("input[name=sidebar-size]:radio");
    const sidebarColorInput = $("input[name=sidebar-color]:radio");

    const sendThemeSettings = () => {
        const layoutMode = layoutModeInput.filter(":checked").val();
        const layoutWidth = layoutWidthInput.filter(":checked").val();
        const layoutPosition = layoutPositionInput.filter(":checked").val();
        const sidebarSize = sidebarSizeInput.filter(":checked").val();
        const sidebarColor = sidebarColorInput.filter(":checked").val();

        sendRequest(`${apiurl}/theme/${usersid}`, "PUT", {
            layout_mode: layoutMode,
            layout_width: layoutWidth,
            layout_position: layoutPosition,
            sidebar_size: sidebarSize,
            sidebar_color: sidebarColor,
        });
    };

    layoutModeInput.add(layoutWidthInput)
    .add(layoutPositionInput)
    .add(sidebarSizeInput)
    .add(sidebarColorInput)
    .click(sendThemeSettings);

    // end change theme

    // button event popup view in datagrid
    function btnreqcancel() {
        popup.hide()
    }
    // end button event

    // change profile picture
    function uploadButton() {
        changephotopopup.show();

    }

    const changephotopopup = $("#upload-popup").dxPopup({
        showTitle: true,
        title: "Upload Picture",
        width: "400px",
        height: "300px",
        visible: false,
        hideOnOutsideClick: true,
        toolbarItems: [{
            widget: 'dxButton',
            toolbar: 'bottom',
            location: 'after',
            options: {
                text: 'Close',
                onClick() {
                    changephotopopup.hide();
                },
            },
        }],
    }).dxPopup('instance');;

    function submitButton() {

        var fileName = $('#picture-input')[0].files[0];

        const formData = new FormData();
              formData.append('picture', fileName);
        sendRequestalt(apiurl + "/update-profilepicture", "POST", formData).then(function(response){

            if(response.status !== 'error') {
                changephotopopup.hide();
                window.location.reload();
            }

        });
    }
    // end change profile picture

    // loading screen
    function showLoadingScreen() {
        // Dynamically create the loading screen if it doesn't exist
        if (!$('#loading-screen').length) {
            $('body').append('<div id="loading-screen" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5); z-index:9999; text-align:center; color:white; font-size:24px; line-height:100vh;">Loading...</div>');
        }
        $('#loading-screen').fadeIn();
    }

    function hideLoadingScreen() {
        $('#loading-screen').fadeOut();
    }

    function getStartAndEndDateOfMonth() {
        // Get today's date
        var today = new Date();

        // Get year and month
        var year = today.getFullYear();
        var month = today.getMonth(); // Note: months are 0-based in JavaScript

        // Start of the month
        var startOfMonth = new Date(year, month, 1);

        // End of the month
        var endOfMonth = new Date(year, month + 1, 0); // 0 means last day of the previous month

        // Format dates to Y-m-d
        var formatDate = function(date) {
            var y = date.getFullYear();
            var m = String(date.getMonth() + 1).padStart(2, '0'); // Pad with zero if needed
            var d = String(date.getDate()).padStart(2, '0'); // Pad with zero if needed
            return `${y}-${m}-${d}`;
        };

        return {
            today: formatDate(today),
            startOfMonth: formatDate(startOfMonth),
            endOfMonth: formatDate(endOfMonth)
        };
    }

    function formatDate(date) { // format date y-m-d
        const d = new Date(date);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    const jsFiles = {
        //admin
        '/module': 'admin/module.js',
        '/useraccess': 'admin/useraccess.js',
        '/sidemenu': 'admin/sidemenu.js',
        '/icons': 'admin/icons.js',
        '/sequence': 'admin/sequence.js',
        '/user': 'admin/user.js',
        '/company': 'admin/company.js',
        '/department': 'admin/department.js',
        '/grade': 'admin/grade.js',
        '/level': 'admin/level.js',
        '/location': 'admin/location.js',
        '/position': 'admin/position.js',
        '/ethnic': 'admin/ethnic.js',
        '/religion': 'admin/religion.js',
        '/nationality': 'admin/nationality.js',
        '/approvaltype': 'admin/approvaltype.js',
        '/approvaluser': 'admin/approvaluser.js',
        '/developer': 'admin/developer.js',
        '/uavasset': 'admin/uavasset.js',
        '/categoryhrsc': 'admin/category_hrsc.js',
        '/ghm_admin': 'admin/ghm_admin.js',
        '/categoryform': 'admin/category_form.js',
        '/exchange_rate': 'submission/Financial/exchangeRate.js',
        //module
        '/headcounts': 'module/headcounts.js',
        '/employeedata': 'module/employeedata.js',
        '/ecatalog': 'module/ecatalog.js',
        '/purchasing_user': 'module/purchasinguser.js',
        //submission
        '/travel_request': 'submission/travel_request.js',
        '/project_request': 'submission/project_request.js',
        '/project_report': 'submission/project_report.js',
        '/ticket_request': 'submission/ticket_request.js',
        '/uavmission_request': 'submission/uavmission_request.js',
        '/hrsc_request': 'submission/hrsc_request.js',
        '/hrsc_report': 'submission/hrsc_report.js',
        '/mom_request': 'submission/mom_request.js',
        '/jdi_request': 'submission/jdi_request.js',
        '/jdi_report': 'submission/jdi_report.js',
        //submission/IT
        '/ad_request': 'submission/IT/ad_request.js',
        //submission/MMF
        '/mmf_28_request': 'submission/MMF/28_request.js',
        '/mmf_30_request': 'submission/MMF/30_request.js',
        '/mmf_30_report': 'submission/MMF/30_report.js',
        '/material_request': 'submission/MMF/material_request.js',
        //submission/Advance
        '/advance_request': 'submission/Financial/Advance/advance_request.js',
        //submission/HRIS/Hcrf
        '/hcrf_request': 'submission/HRIS/Hcrf/hcrf_request.js',
        //submission/CCM
        '/ccm_request': 'submission/Financial/Ccm/ccm_request.js',
        //submission/GHM
        '/ghm_request': 'submission/ghm_request.js',
        //submission/Capex
        '/request_capex': 'submission/Financial/Capex/capex_request.js',
        //submission/Memo
        // '/memorandum_request': 'submission/ms_request.js',
    }
    
    const pathname = window.location.pathname;
    var result = /[^/]*$/.exec(pathname)[0];
    const scriptPath = jsFiles['/'+result];
    if(scriptPath) {
        $.getScript(`/devportal/public/assets/js/${scriptPath}`);
    }

</script>