<script>

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
        //module
        '/headcounts': 'module/headcounts.js',
        //submission
        '/travel_request': 'submission/travel_request.js',
        '/project_request': 'submission/project_request.js',
    }
    
    const pathname = window.location.pathname;
    var result = /[^/]*$/.exec(pathname)[0];
    const scriptPath = jsFiles['/'+result];
    if(scriptPath) {
        $.getScript(`/devportal/public/assets/js/${scriptPath}`);
    }

</script>