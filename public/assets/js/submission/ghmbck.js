var modname = 'ghmrequest';
var modelclass = 'Ghm';
var popupmode;
var dataSubmitted = false; // Flag to track if data has been submitted

// Function to serialize employee_id to JSON
function serializeToJSON(employeeIds) {
    return JSON.stringify(employeeIds);
}

// Function to deserialize JSON to employee_id array
function deserializeFromJSON(jsonString) {
    return JSON.parse(jsonString);
}

$(function() {
    function submitFormData() {
        if (dataSubmitted) return; // Prevent duplicate submissions

        dataSubmitted = true;
        const formData = $('#booking-form').serializeArray();
        const employeeIdsField = formData.find(field => field.name === 'employee_id');
        // employeeIdsField.value = serializeToJSON(employeeIdsField.value.split(',').map(Number));

        const guestField = formData.find(field => field.name === 'guest');
        guestField.value = (guestField.value.split(',').map(name => name.trim()));

        const familyField = formData.find(field => field.name === 'family');
        familyField.value = serializeToJSON(familyField.value.split(',').map(name => name.trim()));

        sendRequest(apiurl + "/" + modname, "POST", formData).then(function(response) {
            if (response.status === 'success') {
                alert('Booking created successfully!');
                location.reload(); // Reload page to reflect new booking
            } else {
                alert('Error: ' + response.message);
            }
        }).catch(function(error) {
            alert('Error: ' + error.responseText);
        }).finally(function() {
            dataSubmitted = false; // Reset the flag after submission
        });
    }

$('#booking-form').on('submit', function(event) {
    event.preventDefault();
    submitFormData();
});

$('#location-selector').dxSelectBox({
    dataSource: uniqueLocations,
    displayExpr: function(item) {
        return item || "";
    },
    valueExpr: function(item) {
        return item;
    },
    value: uniqueLocations[0],
    onValueChanged: function(e) {
        const selectedLocation = e.value;
        updateRoomSelector(selectedLocation);
    }
});

function updateRoomSelector(location) {
    const filteredRooms = roomsWithLocations.filter(emp => emp.location === location);
    $('#room-selector').dxSelectBox({
        dataSource: filteredRooms,
        displayExpr: 'text',
        valueExpr: 'id',
        value: null,
        placeholder: 'Select Room',
        onValueChanged: function(e) {
            const selectedRoomId = e.value;
            updateScheduler(location, selectedRoomId);
        }
    });

    updateScheduler(location, null);
}
// Fungsi untuk mengecek apakah dua rentang tanggal beririsan
function isDateOverlap(start1, end1, start2, end2) {
    return (new Date(start1) <= new Date(end2)) && (new Date(start2) <= new Date(end1));
}
// Fungsi untuk memastikan array selalu valid (menghindari null/undefined)
function safeArray(arr) {
    return Array.isArray(arr) ? arr : [];
}
// Fungsi untuk menghitung total tamu pada tanggal yang beririsan dengan booking baru
function getTotalGuestsForDateLocally(scheduler, roomId, startDate, endDate) {
    let appointments = scheduler.getDataSource().items(); // Ambil semua booking yang sudah ada
    let totalGuests = 0;

    appointments.forEach(appointment => {
        if (
            appointment.ghm_room_id === roomId &&
            isDateOverlap(appointment.startDate, appointment.endDate, startDate, endDate)
        ) {
            let guestCount = safeArray(appointment.guest).length;
            let familyCount = safeArray(appointment.family).length;
            let employeeCount = safeArray(appointment.employee_id).length;
            totalGuests += guestCount + familyCount + employeeCount;
        }
    });

    return totalGuests;
}
// Fungsi untuk menghitung jumlah tamu per hari dalam rentang booking
function getTotalGuestsPerDay(scheduler, roomId, startDate, endDate) {
    let appointments = scheduler.getDataSource().items(); // Ambil semua booking yang sudah ada
    let dailyGuestCount = {}; // Objek untuk menyimpan jumlah tamu per tanggal

    appointments.forEach(appointment => {
        if (appointment.ghm_room_id === roomId) {
            let bookingStart = new Date(appointment.startDate);
            let bookingEnd = new Date(appointment.endDate);

            for (let d = new Date(bookingStart); d <= bookingEnd; d.setDate(d.getDate() + 1)) {
                let dateKey = d.toISOString().split("T")[0]; // Format YYYY-MM-DD
                let guestCount = safeArray(appointment.guest).length;
                let familyCount = safeArray(appointment.family).length;
                let employeeCount = safeArray(appointment.employee_id).length;
                let totalGuests = guestCount + familyCount + employeeCount;

                dailyGuestCount[dateKey] = (dailyGuestCount[dateKey] || 0) + totalGuests;
            }
        }
    });

    return dailyGuestCount;
}
// Fungsi validasi booking saat membuka form
function validateBooking(form) {
    let guestCount = safeArray(form.getEditor("guest")?.option("value")).length;
    let familyCount = safeArray(form.getEditor("family")?.option("value")).length;
    let employeeCount = safeArray(form.getEditor("employee_id")?.option("value")).length;
    let totalGuests = guestCount + familyCount + employeeCount;

    let selectedRoom = form.getEditor("ghm_room_id")?.option("value");
    let roomCapacity = roomsWithLocations.find(room => room.id === selectedRoom)?.roomAccupancy || 0;

    let doneButton = $(".dx-popup-bottom .dx-button.dx-popup-done");

    if (totalGuests > roomCapacity) {
        DevExpress.ui.notify("Jumlah tamu melebihi kapasitas kamar!", "error", 2000);
    }
}


function getTotalGuestsForDateLocally(scheduler, roomId, checkDate) {
    let appointments = scheduler.getDataSource().items(); // Ambil semua booking yang ada
    let totalGuests = 0;
    
    console.log(`📆 Mencari booking di kamar ${roomId} untuk tanggal ${checkDate}`);

    appointments.forEach(appointment => {
        let start = new Date(appointment.startDate);
        let end = new Date(appointment.endDate);
        let check = new Date(checkDate);

        console.log(`🕒 Booking Room ID: ${appointment.ghm_room_id}, Start: ${start}, End: ${end}`);

        // Cek apakah checkDate berada dalam rentang startDate - endDate
        if (appointment.ghm_room_id === roomId && check >= start && check <= end) {
            let guestCount = safeArray(appointment.guest).length;
            let familyCount = safeArray(appointment.family).length;
            let employeeCount = safeArray(appointment.employee_id).length;

            console.log(`✔️ Ditemukan booking dalam rentang tanggal: Guest=${guestCount}, Family=${familyCount}, Employee=${employeeCount}`);

            totalGuests += guestCount + familyCount + employeeCount;
        }
    });

    console.log(`✅ Total tamu di kamar ${roomId} pada ${checkDate}: ${totalGuests}`);
    return totalGuests;
}

// Fungsi untuk membandingkan tanggal tanpa memperhitungkan waktu
function isSameDate(date1, date2) {
    let d1 = new Date(date1);
    let d2 = new Date(date2);
    return d1.toDateString() === d2.toDateString();
}

function updateScheduler(location, roomId) {
    let dataSource = roomsWithLocations.filter(emp => emp.location === location);
    if (roomId) {
        dataSource = dataSource.filter(emp => emp.id === roomId);
    }
// Fungsi untuk reload scheduler
function reloadScheduler() {
    setTimeout(() => {
        const schedulerElement = document.querySelector("#scheduler");
        if (schedulerElement) {
            const scheduler = $("#scheduler").dxScheduler("instance");
            if (scheduler) {
                scheduler.getDataSource().reload().done(() => scheduler.repaint());
            } else {
                console.error("Scheduler instance is undefined.");
            }
        } else {
            console.error("Scheduler element not found in DOM.");
        }
    }, 500); // Delay agar DOM diperbarui dulu
}

    console.log('Booking Data:', booking); // Debug log for bookings

    $('.scheduler').dxScheduler({
        timeZone: 'Asia/Makassar',
        dataSource: booking,
        views: ['month'],
        currentView: 'month',
        currentDate: new Date(),
        firstDayOfWeek: 1,
        startDayHour: 10,
        endDayHour: 22,
        colorExpr: "color",
        // firstDayOfWeek: 1,
        showAllDayPanel: false,
        height: 710,
        groups: ['ghm_room_id'],
        resources: [
            {
                fieldExpr: 'ghm_room_id',
                allowMultiple: false,
                dataSource: dataSource,
                label: 'Room Name',
            },
        ],
        editing: {
            allowAdding: true,
            allowUpdating: true,
            allowDeleting: true,
        },
        // console.log(schedulerInstance.option("dataSource"))
        onAppointmentRendered: function(e) {
            // console.log("Rendered Appointment Data:", e.appointmentData); // Debugging Warna
        
            if (e.appointmentData.requestColor) {
                e.appointmentElement.css("background-color", e.appointmentData.requestColor);
                e.appointmentElement.css("color", "#fff"); // Kontras teks agar terlihat jelas
            } else {
                e.appointmentElement.css("background-color", "#6C757D"); // Default abu-abu jika warna tidak ditemukan
            }
        },
        
        appointmentTooltipTemplate: function(model) {
            const booking = model.appointmentData;
            console.log("Booking Data:", booking); // Debugging
        
            const room = roomsWithLocations.find(room => room.id === booking.ghm_room_id);
            const roomAccupancy = room?.roomAccupancy || 0;
        
            // Hitung total orang di booking
            const guestCount = safeArray(booking.guest).length;
            const familyCount = safeArray(booking.family).length;
            const employeeCount = safeArray(booking.employee_id).length;
            const totalPeople = guestCount + familyCount + employeeCount;
        
            // Hitung sisa kapasitas kamar
            const remainingCapacity = roomAccupancy - totalPeople;
        
            // Format tanggal dengan aman
            const formatDate = (date) => {
                if (!date) return "No Date";
                const d = new Date(date);
                return isNaN(d.getTime()) ? "No Date" : d.toISOString().split("T")[0];
            };
        
            // ID unik untuk tombol
            const actionButtonId = `action-btn-${booking.id}`;
            const isCancelable = Number(booking.requestStatus) === 1 || Number(booking.requestStatus) === 2;
            console.log("isCancelable:", isCancelable, "requestStatus:", booking.requestStatus); // Debugging
        
            const buttonLabel = isCancelable ? "Cancel" : "Delete";
            const buttonClass = isCancelable ? "btn-warning" : "btn-danger";
        
            const tooltipHtml = `
                <div>
                    <b>Purpose: ${booking.text || "No Title"}</b><br>
                    ${formatDate(booking.startDate)} - ${formatDate(booking.endDate)}<br>
                    <b>Accupancy:</b> ${roomAccupancy} Person<br>
                    <b>Booked:</b> ${totalPeople} Person<br>
                    <b>Remaining:</b> ${remainingCapacity} Person<br>
                    <b>Created By:</b> ${booking.creator || "No Name"}<br><br>
                    <button id="${actionButtonId}" class="btn ${buttonClass} btn-sm">${buttonLabel}</button>
                </div>
            `;
        
            // Gunakan MutationObserver untuk memastikan tombol tersedia di DOM
            const observer = new MutationObserver((mutations) => {
                const actionButton = document.getElementById(actionButtonId);
                if (actionButton) {
                    actionButton.addEventListener("click", function(event) {
                        event.stopPropagation(); // Mencegah popup scheduler terbuka
                        event.preventDefault();
        
                        Swal.fire({
                            title: isCancelable ? 'Cancel Booking?' : 'Are you sure?',
                            text: isCancelable 
                                ? "Do you really want to cancel this booking?"
                                : "Do you really want to delete this booking?",
                            icon: isCancelable ? 'warning' : 'error',
                            showCancelButton: true,
                            confirmButtonText: isCancelable ? 'Yes, cancel it!' : 'Yes, delete it!',
                            cancelButtonText: 'No, keep it'
                        }).then((result) => {
                            if (!result.isConfirmed) return;
        
                            if (isCancelable) {
                                // Kirim request ke API Update
                                sendRequest(apiurl + "/" + modname + "/" + booking.id, "PATCH", { requestStatus: 0 })
                                    .then(response => {
                                        if (response.status === "success") {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Booking Canceled!',
                                                text: 'Booking has been successfully set to Canceled.',
                                                timer: 2000,
                                                showConfirmButton: false
                                            });
                                            // reloadScheduler(); // Panggil fungsi untuk reload scheduler
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: response.message || "Failed to update booking."
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: error.responseText || "Unknown error."
                                        });
                                    });
                            } else {
                                // Kirim request DELETE untuk menghapus booking
                                sendRequest(apiurl + "/" + modname + "/" + booking.id, "DELETE")
                                    .then(response => {
                                        if (response.status === "success") {
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Deleted!',
                                                text: 'Booking deleted successfully!',
                                                timer: 2000,
                                                showConfirmButton: false
                                            });
                                            // reloadScheduler(); // Panggil fungsi untuk reload scheduler
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: response.message || "Failed to delete booking."
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: error.responseText || "Unknown error."
                                        });
                                    });
                            }
                        });
                    });
                    observer.disconnect(); // Hentikan observer setelah tombol ditemukan
                }
            });
        
            observer.observe(document.body, { childList: true, subtree: true });
        
            return tooltipHtml;
        },
        
        dataCellTemplate: function(cellData, index, container) {
            const { ghm_room_id } = cellData.groups;
            const currentTraining = getCurrentTraining(cellData.startDate.getDate(), ghm_room_id);

            const wrapper = $('<div>')
                .toggleClass(`employee-weekend-${ghm_room_id}`, isWeekEnd(cellData.startDate))
                .appendTo(container)
                .addClass(`employee-${ghm_room_id}`)
                .addClass('dx-template-wrapper');

            wrapper.append($('<div>')
                .text(cellData.text)
                .text(cellData.roomAccupancy)
                .addClass(currentTraining)
                .addClass('day-cell'));
        },
        resourceCellTemplate: function(cellData) {
            // console.log(`Room: ${cellData.text}, Status: ${cellData.data.requestStatus}, Request Color: ${cellData.data.requestColor}, Room Color: ${cellData.data.roomColor}`);
            
            const name = $('<div>')
                .addClass('name')
                .append($('<h2>').text(cellData.text));
        
            const roomAccupancy = $('<div>')
                .addClass('roomAccupancy')
                .html(`Bed: ${cellData.data.roomAccupancy}`);
            // Tentukan warna berdasarkan jumlah bed
            let bgColor;
            if (cellData.data.roomAccupancy == 4) {
                bgColor = "#B0BEC5"; // Hijau untuk kamar dengan banyak bed
            } else if (cellData.data.roomAccupancy == 3) {
                bgColor = "#90A4AE"; // Oranye untuk kamar dengan kapasitas sedang            
            } else if (cellData.data.roomAccupancy == 2) {
                bgColor = "#A5D6A7"; // Oranye untuk kamar dengan kapasitas sedang
            } else {
                bgColor = "#FFCCBC"; // Merah untuk kamar dengan kapasitas sedikit
            }
            
        
            const combinedColumn = $('<div>')
            .addClass('combined-column')
            .append(name, roomAccupancy)
            .css({
                backgroundColor: bgColor,
                padding: '10px',
                borderRadius: '5px',
                color: '#fff',
                textAlign: 'center'
            });

            return combinedColumn;
        },
        onCellPrepared: function(e) {
            // **Sembunyikan kolom 'code' jika null**
            if (e.rowType == "data" && e.column.dataField === "code") {
                const isCodeVisible = e.data.code !== null;
                $("#formdata").dxDataGrid('columnOption', 'code', 'visible', isCodeVisible);
            }
        
            // **Tandai sel kosong dengan warna merah muda**
            if (e.rowType == "data" && (e.column.index > 0 && e.column.index < 6)) {
                if (!e.value || /^\s*$/.test(e.value)) {
                    e.cellElement.css({
                        "backgroundColor": "#ffe6e6",
                        "border": "0.5px solid #f56e6e"
                    });
                }
            }
        
            // **Tandai baris dengan `isParent === 1`**
            if (e.rowType == "data" && e.data.isParent === 1) {
                e.cellElement.css('background', 'rgba(128, 128, 0, 0.1)');
            }
        },
        onAppointmentFormOpening: function(e) {
            const form = e.form;
            const appointmentData = e.appointmentData;
            // const isNewAppointment = !appointmentData.id;

            console.log('Appointment Data:', appointmentData); // Debug log

            // if (appointmentData.employee_id && typeof appointmentData.employee_id === 'string') {
            //     appointmentData.employee_id = deserializeFromJSON(appointmentData.employee_id);
            // }
            // if (appointmentData.guest && typeof appointmentData.guest === 'string') {
            //     // console.log(appointmentData.guest)
            //     appointmentData.guest = deserializeFromJSON(appointmentData.guest);
            //     // console.log(appointmentData.guest)
            // } else if (!appointmentData.guest) {
            //     appointmentData.guest = []; // Inisialisasi dengan string kosong jika nilai `guest` adalah `null` atau `undefined`
            // }
            // if (appointmentData.family && typeof appointmentData.family === 'string') {
            //     // console.log(appointmentData.family)
            //     appointmentData.family = deserializeFromJSON(appointmentData.family);
            //     // console.log(appointmentData.family)
            // } else if (!appointmentData.family) {
            //     appointmentData.family = []; // Inisialisasi dengan string kosong jika nilai `family` adalah `null` atau `undefined`
            // }

            function validateBooking() {
                let guestCount = (form.getEditor("guest")?.option("value") || []).length;
                let familyCount = (form.getEditor("family")?.option("value") || []).length;
                let employeeCount = (form.getEditor("employee_id")?.option("value") || []).length;

                let totalGuests = guestCount + familyCount + employeeCount;
                console.log("total guest",totalGuests);
                let selectedRoom = form.getEditor("ghm_room_id")?.option("value");
                // let roomAccupancy = room?.roomAccupancy || 0;
                let roomCapacity = roomsWithLocations.find(room => room.id === selectedRoom)?.roomAccupancy || 0;
                console.log("total Kaps",roomCapacity);
        
                let doneButton = $(".dx-popup-bottom .dx-button.dx-popup-done");
        
                if (totalGuests > roomCapacity) {
                    // doneButton.addClass("dx-state-disabled");
                    DevExpress.ui.notify("Jumlah tamu melebihi kapasitas kamar!", "error", 2000);
                // } else { 
                    // doneButton.removeClass("dx-state-disabled");
                }
            }
            
            form.option('items', [                
                {
                    itemType: 'group',
                    colCount: 1,
                    caption: 'Interests',
                    items: [
                        {
                            label: { text: 'Code' },
                            // editorType: 'dxTextBox',
                            dataField: 'code',
                            editorOptions: {
                                readOnly: true,
                                value: appointmentData.code || ''
                            }
                        },
                        {
                            label: { text: 'Purpose' },
                            editorType: 'dxTextBox',
                            dataField: 'text',
                            editorOptions: {
                                value: appointmentData.text || ''
                            }
                        },
                        {
                            label: { text: 'Details' },
                            editorType: 'dxTextArea',
                            dataField: 'description',
                            editorOptions: {
                                value: appointmentData.description || ''
                            }
                        }                           
                    ]
                },
                {
                    itemType: 'group',
                    caption: 'Room & Date',
                    items: [
                        {
                            label: { text: 'Room' },
                            editorType: 'dxSelectBox',
                            dataField: 'ghm_room_id',
                            editorOptions: {
                                dataSource: roomsWithLocations,
                                displayExpr: 'text',
                                valueExpr: 'id',
                                value: appointmentData.ghm_room_id || null,
                                onValueChanged: validateBooking
                            }
                        },
                        {
                            label: { text: 'Start Date' },
                            editorType: 'dxDateBox',
                            dataField: 'startDate',
                            editorOptions: {
                                type: 'datetime',
                                value: appointmentData.startDate,
                                displayFormat: 'yyyy-MM-dd HH:mm:ss',
                                dateSerializationFormat: 'yyyy-MM-ddTHH:mm:ssZ'
                            }
                        },
                        {
                            label: { text: 'End Date' },
                            editorType: 'dxDateBox',
                            dataField: 'endDate',
                            editorOptions: {
                                type: 'datetime',
                                value: appointmentData.endDate,
                                displayFormat: 'yyyy-MM-dd HH:mm:ss',
                                dateSerializationFormat: 'yyyy-MM-ddTHH:mm:ssZ'
                            }
                        },
                                                
                    ]
                },
                {
                    itemType: 'group',
                    colSpan: 2,
                    caption: 'Guest Type',
                    items: [
                        {                                
                            title: 'Employee',
                            label: { text: 'Employee' },
                            editorType: 'dxTagBox',
                            dataField: 'employee_id',
                            editorOptions: {                                                
                                dataSource: emplo,
                                displayExpr: function(item) {
                                    if (!item) return "";
                                    const department = departments.find(dept => dept.id === item.department_id);
                                    return `${item.FullName} | ${item.SAPID} | ${department ? department.DepartmentName : "Failed"}`;                                    
                                },
                                valueExpr: 'id',
                                value: Array.isArray (appointmentData.employee_id) ? appointmentData.employee_id : [],
                                showSelectionControls: true,
                                applyValueMode: 'useButtons',
                                searchEnabled: true,
                                onValueChanged: validateBooking
                            }                            
                        },
                        {                                        
                            title: 'Guest',
                            editorType: 'dxTagBox',
                            dataField: 'guest',
                            editorOptions: {
                                dataSource: [],
                                value: Array.isArray(appointmentData.guest) ? appointmentData.guest : [],
                                acceptCustomValue: true,
                                searchEnabled: true,
                                showSelectionControls: true,
                                applyValueMode: 'useButtons',
                                onCustomItemCreating: function(args) {
                                    let newValue = args.text;
                                    let guests = form.option('formData').guest || [];
                                    if (!guests.includes(newValue)) {
                                        guests.push(newValue);
                                        args.customItem = newValue;
                                    } else {
                                        args.customItem = null;
                                    }
                                    // appointmentData.guest = guests;
                                    // let newFormData = { ...form.option('fromData'), guest: newGuestList } ;
                                    // form.option('formData', newFormData);
                                    form.updateData('guest', guests);
                                    validateBooking();
                                    // form.repaint();
                                }
                            }
                        },                            
                        {
                            title: 'Family',
                            editorType: 'dxTagBox',
                            dataField: 'family',
                            editorOptions: {
                                dataSource: [],
                                value: Array.isArray(appointmentData.family) ? appointmentData.family : [],
                                acceptCustomValue: true,
                                searchEnabled: true,
                                showSelectionControls: true,
                                applyValueMode: 'useButtons',
                                onCustomItemCreating: function(args) {
                                    let newValue = args.text;
                                    let familys = form.option('formData').family || [];
                                    if (!familys.includes(newValue)) {
                                        familys.push(newValue);
                                        args.customItem = newValue;
                                    } else {
                                        args.customItem = null;
                                    }
                                    // appointmentData.family = familys;
                                    // let newFormData = { ...form.option('fromData'), guest: newGuestList } ;
                                    // form.option('formData', newFormData);
                                    form.updateData('family', familys);
                                    // form.repaint();
                                    validateBooking();
                                }
                            }
                        } 
                    ]
                }                                       
            ]);

            setTimeout(validateBooking,100);
        },
        // Event saat user ingin menambahkan booking baru
        onAppointmentAdding: function(e) {
        const appointmentData = e.appointmentData;
        let scheduler = e.component;

        let guestCount = safeArray(appointmentData.guest).length;
        let familyCount = safeArray(appointmentData.family).length;
        let employeeCount = safeArray(appointmentData.employee_id).length;
        let totalNewGuests = guestCount + familyCount + employeeCount;

        let selectedRoom = appointmentData.ghm_room_id;
        let roomCapacity = roomsWithLocations.find(room => room.id === selectedRoom)?.roomAccupancy || 0;

        // Hitung total tamu per hari dalam rentang booking baru
        let dailyGuestCount = getTotalGuestsPerDay(scheduler, selectedRoom, appointmentData.startDate, appointmentData.endDate);

        // Cek apakah ada hari di mana jumlah tamu melebihi kapasitas kamar
        let bookingStart = new Date(appointmentData.startDate);
        let bookingEnd = new Date(appointmentData.endDate);

        for (let d = new Date(bookingStart); d <= bookingEnd; d.setDate(d.getDate() + 1)) {
            let dateKey = d.toISOString().split("T")[0]; // Format YYYY-MM-DD
            let totalGuestsAfterAdding = (dailyGuestCount[dateKey] || 0) + totalNewGuests;

            if (totalGuestsAfterAdding > roomCapacity) {
                e.cancel = true; // Batalkan booking
                DevExpress.ui.notify(`Kapasitas penuh pada ${dateKey}! (${dailyGuestCount[dateKey] || 0}/${roomCapacity})`, "error", 3000);
                return;
            }
        }

        // Serialize array sebelum dikirim
        // appointmentData.guest = JSON.stringify(appointmentData.guest);
        // appointmentData.family = JSON.stringify(appointmentData.family);

        // Kirim data booking ke server-tambahkan metode btn-req-cancel gunakan dari modul lain
        Swal.fire({
            title: 'What do you want to do?',
            text: 'Choose an option for this booking',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Submit Now',
            cancelButtonText: 'Save as Draft',
            reverseButtons: true
        }).then((result) => {
            console.log("Swal result:", result); // Log the result object
            let requestStatus = result.isConfirmed ? 1 : 0; // Menentukan status request
            let actionText = result.isConfirmed ? 'submitted' : 'saved as draft';
        
            console.log("Request Status:", requestStatus);
        
            // Jalankan POST pertama
            return sendRequest(apiurl + "/" + modname, "POST", {
                requestStatus: requestStatus,
                text: appointmentData.text,
                description: appointmentData.description,
                startDate: appointmentData.startDate,
                endDate: appointmentData.endDate,
                ghm_room_id: appointmentData.ghm_room_id,
                employee_id: appointmentData.employee_id,
                guest: appointmentData.guest,
                family: appointmentData.family
            }).then((response) => {
                console.log("Response from first request:", response);
                
                // Ambil reqid dari response pertama
                let reqid = response.data.id;
                
                // Jika pengguna memilih "Submit Now", jalankan POST kedua
                if (result.isConfirmed) {
                    return sendRequest(apiurl + "/submissionrequest/" + reqid + "/" + modelclass, "POST", {
                        requestStatus: 1,
                        action: actionForm,
                        approvalAction: isNaN(parseInt(valapprovalAction)) ? 1 : parseInt(valapprovalAction),
                        approvalType: valApprovalType,
                    });
                } else {
                    return null; // Tidak ada request kedua jika memilih "Save as Draft"
                }
            }).then((response) => {
                // Jika POST kedua dilakukan dan sukses
                if (response && response.status === 'success') {
                    e.component._dataSource.reload();
                }
        
                // Tampilkan pesan sukses setelah semua proses selesai
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: `Booking has been ${actionText}.`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }).catch((error) => {
                // Tangani error untuk POST pertama atau kedua
                console.error("Error occurred:", error);
                Swal.fire({ icon: 'error', title: 'Error', text: error.responseText || 'An error occurred' });
            });
        });      
    },
    onAppointmentUpdating: function(e) {
        const appointmentData = e.newData;
    
        // Format tanggal untuk database
        const formatDateForDB = (date) => {
            const d = new Date(date);
            return `${d.getFullYear()}-${(d.getMonth() + 1).toString().padStart(2, '0')}-${d.getDate().toString().padStart(2, '0')} ${d.getHours().toString().padStart(2, '0')}:${d.getMinutes().toString().padStart(2, '0')}:${d.getSeconds().toString().padStart(2, '0')}`;
        };
    
        appointmentData.startDate = formatDateForDB(appointmentData.startDate);
        appointmentData.endDate = formatDateForDB(appointmentData.endDate);
        appointmentData.id = e.oldData.id; // Pastikan id disertakan
    
        // Serialisasi array (jika ada)
        // appointmentData.guest = Array.isArray(appointmentData.guest) ? JSON.stringify(appointmentData.guest) : null;
        // appointmentData.family = Array.isArray(appointmentData.family) ? JSON.stringify(appointmentData.family) : null;
    
        console.log('Data yang akan dikirim:', appointmentData);
    
        // Logika untuk status tiket dan konfirmasi
        var newTicketStatus = e.newData.ticketStatus;
        var newConfirmationStatus = e.newData.confirmationStatus;
    
        if (newTicketStatus === "Completed") {
            if (!confirm("Are you sure you want to mark this ticket as completed?")) {
                e.cancel = true;
            } else {
                e.newData.confirmationStatus = 'Waiting';
                e.component.columnOption("ticketStatus", "allowEditing", false);
            }
        }
    
        if (newConfirmationStatus === "Reworked") {
            if (!confirm("Are you sure you want to mark this confirmation status as reworked?")) {
                e.cancel = true;
            } else {
                e.newData.ticketStatus = 'On Queue';
                e.component.columnOption("confirmationStatus", "allowEditing", false);
                e.component.columnOption("confirmationRemarks", "allowEditing", false);
            }
        }
    
        if (newConfirmationStatus === "Completed") {
            if (!confirm("Are you sure you want to mark this confirmation status as completed?")) {
                e.cancel = true;
            } else {
                e.component.columnOption("confirmationStatus", "allowEditing", false);
                e.component.columnOption("confirmationRemarks", "allowEditing", false);
            }
        }
    
        // Konfirmasi dengan SweetAlert
        Swal.fire({
            title: 'What do you want to do?',
            text: 'Choose an option for this booking',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Submit Now',
            cancelButtonText: 'Save as Draft',
            reverseButtons: true
        }).then((result) => {
            let actionText = result.isConfirmed ? 'submitted' : 'saved as draft';
    
            sendRequest(apiurl + "/" + modname + "/" + appointmentData.id, "PUT", {
                text: appointmentData.text,
                description: appointmentData.description,
                startDate: appointmentData.startDate,
                endDate: appointmentData.endDate,
                ghm_room_id: appointmentData.ghm_room_id,
                employee_id: appointmentData.employee_id,
                guest: appointmentData.guest,
                family: appointmentData.family,
                id: appointmentData.id
            }).then(function(response) {
                if (response.status === 'success') {
                    e.component._dataSource.reload();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: `Booking has been ${actionText}.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message });
                }
            }).catch(function(error) {
                Swal.fire({ icon: 'error', title: 'Error', text: error.responseText });
            });
        });
    }
    });
}

function isWeekEnd(date) {
    const day = date.getDay();
    return day === 0 || day === 6;
}

function getCurrentTraining(date, ghm_room_id) {
    const result = (date + ghm_room_id) % 3;
    const currentTraining = `training-background-${result}`;
    return currentTraining;
}
updateRoomSelector(uniqueLocations[0]);
    // Add click event handler for add button
    $('#btnadd').on('click', function() {
        sendRequest(apiurl + "/" + modname, "POST", { requestStatus: 0 }).then(function(response) {
            const reqid = response.data.id;
            console.log(reqid);
            const mode = 'add';
            const options = { "data": { "isMine": 1 } };
            popup.option({
                contentTemplate: () => popupContentTemplate(reqid, mode, options),
            });
            popup.show();
        });
    });
});
// jalankan ini kawan
function btnreqsubmit(reqid, mode) {
    var btnSubmit = $('#btn-submit');
    btnSubmit.prop('disabled', true);
    var actionForm = (mode == 'approval') ? 'approval' : 'submission';
    
    var valapprovalAction = $('input[name="approvalaction"]:checked').val() || null;

    if (mode == 'approval' && !valapprovalAction) {
        alert('Please select approval action.');
        btnSubmit.prop('disabled', false);
        return false;
    }

    var valApprovalType = (valapprovalAction == 3) ? 'Approved' :
                          (valapprovalAction == 2) ? 'Reworked' :
                          (valapprovalAction == 4) ? 'Rejected' : '';

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
            showLoadingScreen();

            if (typeof apiurl === "undefined" || typeof modelclass === "undefined") {
                alert("API URL atau modelclass tidak tersedia!");
                btnSubmit.prop('disabled', false);
                hideLoadingScreen();
                return;
            }

            sendRequest(apiurl + "/" + modname, "POST", {
                requestStatus: requestStatus,
                text: appointmentData.text,
                description: appointmentData.description,
                startDate: appointmentData.startDate,
                endDate: appointmentData.endDate,
                ghm_room_id: appointmentData.ghm_room_id,
                employee_id: appointmentData.employee_id,
                guest: appointmentData.guest,
                family: appointmentData.family
            })
            sendRequest(apiurl + "/submissionrequest/" + reqid + "/" + modelclass, "POST", {
                requestStatus: 1,
                action: actionForm,
                approvalAction: parseInt(valapprovalAction) || 1,
                approvalType: valApprovalType,
            }).then(function(response) {
                btnSubmit.prop('disabled', false);
                hideLoadingScreen();
                
                if (response.status === 'error') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'An error occurred.',
                    });
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved',
                        text: 'The submission has been submitted.',
                    });
                    popup.hide();
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


// =====================================
    public function dashboard()
{
    try {
        // $employeeid = $this->getEmployeeID()->id;
        $user = auth()->user();
        $userId = $user->id;
        $employeeId = $this->getEmployeeID()->id;
        $isAdmin = $user->isAdmin ?? false;
        $moduleId = $this->getModuleId($this->modulename); // Pastikan ini sudah didefinisikan

        // Subquery untuk isPendingOnMe
        $subquery = "(SELECT TOP 1 
            CASE WHEN a.user_id = '" . $userId . "' 
            THEN 1 ELSE 0 END 
            FROM tbl_approverListReq l
            LEFT JOIN tbl_approver a ON l.approver_id = a.id
            LEFT JOIN tbl_approvaltype r ON a.approvaltype_id = r.id 
            WHERE l.ApprovalAction = '1' 
            AND l.req_id = request_ghm.id 
            AND l.module_id = '" . $moduleId . "' 
            AND request_ghm.requestStatus = '1'
            ORDER BY a.sequence)";

        // Query utama
        $dataquery = Ghm::query();

        // Join dengan tbl_assignment untuk non-admin
        if (!$isAdmin) {
            $dataquery->leftJoin('tbl_assignment', function ($join) use ($userId, $moduleId) {
                $join->on('request_ghm.id', '=', 'tbl_assignment.req_id')
                    ->where('tbl_assignment.module_id', $moduleId);
            });
        }

        // Fetch data
        $requests = $dataquery
            ->selectRaw("
                request_ghm.id,
                codes.code, 
                request_ghm.user_id,
                request_ghm.description,
                request_ghm_room.roomName,
                request_ghm_room.bu,
                request_ghm_room.sector,
                request_ghm.ghm_room_id,            
                request_ghm.text,
                request_ghm.description,
                request_ghm.requestStatus,
                request_ghm.completeddate,
                request_ghm.ticketStatus,
                request_ghm.confirmationStatus,
                request_ghm.confirmationRemarks,
                request_ghm.startDate,
                request_ghm.endDate,
                request_ghm.created_at,
                request_ghm.updated_at,
                (SELECT STRING_AGG(emp.fullname, ', ')
                FROM OPENJSON(request_ghm.employee_id) 
                WITH (employee_id INT '$')
                LEFT JOIN employee.tbl_employee AS emp
                ON emp.id = employee_id
                ) AS employee_fullname,
                (SELECT STRING_AGG(value, ', ') FROM OPENJSON(request_ghm.guest)) AS guest,
                (SELECT STRING_AGG(value, ', ') FROM OPENJSON(request_ghm.family)) AS family,
                request_ghm_room.location_id, 
                employee.tbl_location.Location, 
                CASE WHEN request_ghm.user_id = '" . $userId . "' THEN 1 ELSE 0 END AS isMine,
                " . $subquery . " AS isPendingOnMe
            ")
            ->leftJoin('codes', 'request_ghm.code_id', '=', 'codes.id')
            ->leftJoin('request_ghm_room', 'request_ghm.ghm_room_id', '=', 'request_ghm_room.id')
            ->leftJoin('employee.tbl_location', 'request_ghm_room.location_id', '=', 'employee.tbl_location.id')
            ->with(['user', 'approverlist'])
            ->where(function ($query) use ($subquery, $userId, $isAdmin, $employeeId, $moduleId) {
                $query->whereRaw($subquery . " = 1")
                    ->orWhere(function ($query) use ($userId, $isAdmin, $employeeId, $moduleId) {
                        if ($isAdmin) {
                            $query->where("request_ghm.user_id", "!=", $userId)
                                ->whereIn("request_ghm.requestStatus", [1, 3, 4]);
                        } else {
                            $query->where("tbl_assignment.employee_id", $employeeId)
                                ->whereIn("request_ghm.requestStatus", [3]);
                        }
                    })
                    ->orWhere("request_ghm.user_id", $userId);
            })
            ->orderBy(DB::raw($subquery), 'DESC')
            ->orderByRaw("CASE WHEN request_ghm.user_id = '" . $userId . "' THEN 0 ELSE 1 END, request_ghm.created_at DESC")
            ->get();

        // Data tambahan untuk view
        $rooms = Ghm_room::all();
        $locations = Location::all();
        $employees = Employee::with('Department')->get();
        $departments = Department::all();

        // Mapping employees
        $emploMapped = $employees->map(function ($emp) {
            return [
                'id' => $emp->id,
                'FullName' => $emp->FullName,
                'SAPID' => $emp->SAPID,
                'department_id' => $emp->department_id,
            ];
        });

        // Mapping departments
        $departmentsMapped = $departments->map(function ($dept) {
            return [
                'id' => $dept->id,
                'DepartmentName' => $dept->DepartmentName,
            ];
        });
        $statusColors = [
            0 => '#6C757D', // Que (Abu)6C757D-ECEFF1
            1 => '#007BFF', // Completed (Biru)007BFF-81D4FA
            2 => '#FFC107', // Pending (Kuning)FFC107-FFF59D
            3 => '#28A745', // Approved (Hijau)28A745-C8E6C9
            4 => '#DC3545', // Rejected (Merah)DC3545-FFCDD2
        ];
        $totalPeopleData = DB::select("
        SELECT 
            request_ghm.id,
            COALESCE(SUM(EmployeeCount), 0) AS totalEmployee,
            COALESCE(SUM(GuestCount), 0) AS totalGuest,
            COALESCE(SUM(FamilyCount), 0) AS totalFamily,
            COALESCE(SUM(EmployeeCount + GuestCount + FamilyCount), 0) AS totalAll
        FROM 
            [request_ghm]
        CROSS APPLY (SELECT COUNT(*) AS EmployeeCount FROM OPENJSON(employee_id)) AS EmpData
        CROSS APPLY (SELECT COUNT(*) AS GuestCount FROM OPENJSON(guest)) AS GuestData
        CROSS APPLY (SELECT COUNT(*) AS FamilyCount FROM OPENJSON(family)) AS FamilyData
        GROUP BY id
        ");
        
        // Konversi hasil query ke associative array dengan ID sebagai key
        $totalPeopleArray = collect($totalPeopleData)->mapWithKeys(function ($item) {
            return [$item->id => $item->totalAll];
        });
        // Konversi hasil query ke associative array dengan ID sebagai key
        $totalPeopleArray = collect($totalPeopleData)->mapWithKeys(function ($item) {
            return [$item->id => $item->totalAll];
        });
        // Mapping booking data dengan warna sesuai requestStatus
        $booking = $requests->map(function ($request) use ($rooms, $locations, $statusColors, $totalPeopleArray) {
            $room = $rooms->firstWhere('id', $request->ghm_room_id);
            $location = $room ? $locations->firstWhere('id', $room->location_id) : null;       
            $totalPeople = $totalPeopleArray[$request->id] ?? 0; 
            return [
                'id' => $request->id,
                'text' => $request->text ?? '',
                'guest' => $request->guest ?? 0,
                'family' => $request->family ?? 0,
                'employee_id' => $request->employee_id ?? null,
                'ticketstatus' => $request->ticketStatus ?? null,
                'completeddate' => $request->completeddate ?? null,
                'confirmationStatus' => $request->confirmationStatus ?? null,
                'description' => $request->description ?? '',
                'requestStatus' => $request->requestStatus ?? 0,
                'startDate' => optional($request->startDate)->toIso8601String(),
                'endDate' => optional($request->endDate)->toIso8601String(),
                'code' => optional($request->code)->code ?? null,
                'creator' => optional($request->user)->fullname ?? null,
                'ghm_room_id' => $request->ghm_room_id,
                'roomName' => $room->roomName ?? null,
                'location' => $location->Location ?? null,
                'isMine' => $request->isMine ?? 0,
                'isPendingOnMe' => $request->isPendingOnMe ?? 0,
                'requestColor' => isset($statusColors[$request->requestStatus]) ? $statusColors[$request->requestStatus] : '#6C757D', // Default warna abu-abu
                'totalPeople' => $totalPeople
            ];
        });
        
        // Mapping room data dengan warna terpisah dari requestStatus
        $roomsWithLocations = $rooms->map(function ($room) use ($locations, $booking) {
            $location = $locations->firstWhere('id', $room->location_id);
        
            // Cari semua request yang sesuai dengan room_id, lalu ambil yang terbaru
            $requests = $booking->where('ghm_room_id', $room->id);
            $latestRequest = $requests->sortByDesc('startDate')->first(); // Ambil request terbaru
            
            return [
                'text' => $room->roomName,
                'id' => $room->id,
                'requestStatus' => optional($latestRequest)['requestStatus'] ?? 0,
                'roomAccupancy' => $room->roomAccupancy ?? 0,
                'location' => $location ? $location->Location : null,
                'roomColor' => '#F0F0F0', // Warna default untuk room, tidak dipengaruhi requestStatus
            ];
        });
        $uniqueLocations = $roomsWithLocations->pluck('location')->unique()->values();
        dd($booking); 

        // Return view dengan data
        
        return view('dashboard.ghm_booking', [
            'booking' => $booking,
            'roomsWithLocations' => $roomsWithLocations,
            'uniqueLocations' => $uniqueLocations,
            'emplo' => $emploMapped,
            'departments' => $departmentsMapped,
        ]);

    } catch (\Exception $e) {
        // Handle error
        return redirect()->back()->with('error', $e->getMessage());
    }
}
public function dashboard()
{
    $user = auth()->user();
    if (!$user) {
        return redirect()->route('login');
    }

    $userId = $user->id;
    $isAdmin = $user->isAdmin ?? false;
    $employeeId = $user->employee_id ?? null;

    $requests = Ghm::query()
        ->where(function ($query) use ($userId, $isAdmin, $employeeId) {
            if ($isAdmin) {
                $query->where("request_ghm.user_id", "!=", $userId)
                    ->whereIn("request_ghm.requestStatus", [0, 1, 3, 4]);
            } else {
                $query->where("tbl_assignment.employee_id", $employeeId)
                    ->whereIn("request_ghm.requestStatus", [3]);
            }
        })
        ->orWhere("request_ghm.user_id", $userId)
        ->with(['User', 'code', 'ghm_room'])
        ->get();

    $rooms = Ghm_room::all();
    $locations = Location::all();
    $employees = Employee::with('Department')->get();
    $departments = Department::all();

    $totalPeopleData = DB::table('request_ghm')
        ->selectRaw('id, COALESCE(SUM(employee_count + guest_count + family_count), 0) as totalAll')
        ->groupBy('id')
        ->get();

    $totalPeopleArray = $totalPeopleData->mapWithKeys(function ($item) {
        return [$item->id => $item->totalAll];
    });

    // Handle case when there are no bookings
    if ($requests->isEmpty()) {
        $booking = [];
    } else {
        $booking = $requests->map(function ($request) use ($rooms, $locations, $totalPeopleArray) {
            $room = $rooms->firstWhere('id', $request->ghm_room_id);
            $location = $room ? $locations->firstWhere('id', $room->location_id) : null;
            $totalPeople = $totalPeopleArray[$request->id] ?? 0;

            return [
                'id' => $request->id,
                'text' => $request->text ?? '',
                'guest' => $request->guest ?? 0,
                'family' => $request->family ?? 0,
                'employee_id' => $request->employee_id ?? null,
                'ticketstatus' => $request->ticketStatus ?? null,
                'completeddate' => $request->completeddate ?? null,
                'confirmationStatus' => $request->confirmationStatus ?? null,
                'description' => $request->description ?? '',
                'requestStatus' => $request->requestStatus ?? 0,
                'startDate' => optional($request->startDate)->toIso8601String(),
                'endDate' => optional($request->endDate)->toIso8601String(),
                'code' => optional($request->code)->code ?? null,
                'creator' => optional($request->User)->fullname ?? null,
                'ghm_room_id' => $request->ghm_room_id,
                'roomName' => optional($room)->roomName ?? null,
                'location' => optional($location)->Location ?? null,
                'totalPeople' => $totalPeople,
            ];
        });
    }

    $roomsWithLocations = $rooms->map(function ($room) use ($locations) {
        $location = $locations->firstWhere('id', optional($room)->location_id);
        return [
            'text' => optional($room)->roomName ?? 'N/A',
            'id' => optional($room)->id ?? null,
            'roomAccupancy' => optional($room)->roomAccupancy ?? 0,
            'location' => optional($location)->Location ?? 'N/A',
            'color' => '#' . substr(md5(optional($room)->roomName ?? 'default'), 0, 6),
        ];
    });

    $uniqueLocations = $roomsWithLocations->pluck('location')->unique()->values();

    return view('dashboard.ghm_booking', [
        'booking' => $booking,
        'roomsWithLocations' => $roomsWithLocations,
        'uniqueLocations' => $uniqueLocations,
        'emplo' => $employees,
        'departments' => $departments,
    ]);
}
// =====================================
<?php

namespace App\Http\Controllers\Submission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\Submission\Ghm;
use App\Models\Ghm_room;
use App\Models\location;
use App\Models\code;
use App\Models\ApproverListReq;
use App\Models\ApproverListHistory;
use App\Models\Approvaluser;
use App\Models\Module;
use App\Models\User;
use App\Models\Employee;
use App\Models\Assignmentto;
use DB;
use Illuminate\Support\Facades\Log;
use App\Mail\SubmissionMail;
use App\Models\Department;

class GhmRequestController extends Controller
{
    public $model;
    public $modulename;
    public $module;

    public function __construct()
    {
        $this->model = new Ghm();
        $this->modulename = 'Ghm';
        $this->module = new Module();
    }
    public function dashboard()
    {
        $userId = auth()->user()->id;
        $requests = Ghm::where('requestStatus', 3)
        ->orWhere('user_id', $userId)
        ->with('User')
        ->get();
        $rooms = Ghm_room::all();
        $locations = Location::all();
        $code = code::all();
        $emplo= Employee::with('Department')->get();
        $departments = Department::all();

        $emplomapped = $emplo->map(function($emp) {
            return [
                'id' => $emp->id,
                'FullName' => $emp->FullName,
                'SAPID' => $emp->SAPID,
                'department_id' => $emp->department_id,
            ];
        });

        $departmentsMapped = $departments->map(function ($dept) {
            return [
                'id' => $dept->id,
                'DepartmentName' => $dept->DepartmentName
            ];
        });

        $totalPeopleData = DB::select("
        SELECT 
            request_ghm.id,
            COALESCE(SUM(EmployeeCount), 0) AS totalEmployee,
            COALESCE(SUM(GuestCount), 0) AS totalGuest,
            COALESCE(SUM(FamilyCount), 0) AS totalFamily,
            COALESCE(SUM(EmployeeCount + GuestCount + FamilyCount), 0) AS totalAll
        FROM 
            request_ghm
        CROSS APPLY (SELECT COUNT(*) AS EmployeeCount FROM OPENJSON(employee_id)) AS EmpData
        CROSS APPLY (SELECT COUNT(*) AS GuestCount FROM OPENJSON(guest)) AS GuestData
        CROSS APPLY (SELECT COUNT(*) AS FamilyCount FROM OPENJSON(family)) AS FamilyData
        GROUP BY id
        ");
        
        // Konversi hasil query ke associative array dengan ID sebagai key
        $totalPeopleArray = collect($totalPeopleData)->mapWithKeys(function ($item) {
            return [$item->id => $item->totalAll];
        });

        // Mapping booking
        $booking = $requests->map(function ($request) use ($rooms, $locations, $totalPeopleArray) {
            $room = $rooms->firstWhere('id', $request->ghm_room_id);
            $location = $room ? $locations->firstWhere('id', $room->location_id) : null;

        // Ambil totalPeople berdasarkan ID request
        $totalPeople = $totalPeopleArray[$request->id] ?? 0;
            return [                
                'id' => $request->id,
                'text' => $request->text,
                'guest' => $request->guest,
                'family' => $request->family,
                'employee_id' =>$request->employee_id,
                'ticketstatus'=> $request->ticketStatus,
                'completeddate' => $request->completeddate,
                'confirmationStatus' =>$request->confirmationStatus,
                'description' => $request->description,
                'requestStatus' => $request->requestStatus,
                'startDate' => $request->startDate ? $request->startDate->toIso8601String() : null,
                'endDate' => $request->endDate ? $request->endDate->toIso8601String() : null,
                'code' => $request->code ? $request->code->code : null,
                'creator' => $request->User ? $request->User->fullname : null,
                'ghm_room_id' => $request->ghm_room_id,
                'roomName' => $room ? $room->roomName : null,
                'location' => $location ? $location->Location : null,
                'totalPeople' => $totalPeople
            ];
        });
        
        $roomsWithLocations = $rooms->map(function ($room) use ($locations) {
            $location = $locations->firstWhere('id', $room->location_id);
            return [
                'text' => $room->roomName,
                'id' => $room->id,
                'roomAccupancy' => $room->roomAccupancy,
                'location' => $location ? $location->Location : null,
                'color' => '#'.substr(md5($room->roomName), 0, 6) // Generate color based on room name hash
            ];
        });

        // Getting unique locations
        $uniqueLocations = $roomsWithLocations->pluck('location')->unique()->values();

        return view('dashboard.ghm_booking', [
            'booking' => $booking,
            'roomsWithLocations' => $roomsWithLocations,
            'uniqueLocations' => $uniqueLocations,
            'emplo' => $emplomapped,
            'departments' =>$departmentsMapped,
        ]);
        // return response()->json([
        //     'booking' => $booking,
        //     'roomsWithLocations' => $roomsWithLocations,
        //     'uniqueLocations' => $uniqueLocations,
        //     'emplo' => $emplo
        // ]);
        // dd($booking);

    }
    public function userstore(Request $request)
    {
        try {
            // Ambil semua data dari request
            $requestData = $request->all();
            // Tambahkan user_id ke dalam data request
            $requestData['user_id'] = $this->getAuth()->id;
            $requestData['requestStatus'] = 0;            
            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);
            // Simpan id dari data baru
            $req_id = $newData->id;
            // dd($req_id)
            // $this->createApprover($this->modulename, $req_id, null, null);
            $requests = Ghm::all();
            $rooms = Ghm_room::all();
            $locations = Location::all();
            
            $booking = $requests->map(function ($request) use ($rooms, $locations) {
                $room = $rooms->firstWhere('id', $request->ghm_room_id);
                $location = $room ? $locations->firstWhere('id', $room->location_id) : null;
                return [
                    'name' => $request->name,
                    'description' => $request->description,
                    'requestStatus' => $request->requestStatus,
                    'startDate' => $request->startDate ? $request->startDate->toIso8601String() : null,
                    'endDate' => $request->endDate ? $request->endDate->toIso8601String() : null,
                    'ghm_room_id' => $request->ghm_room_id,
                    'roomName' => $room ? $room->roomName : null,
                    'location' => $location ? $location->Location : null
                ];
            });
            $rooms = Ghm_room::all();
            $roomsWithLocations = $rooms->map(function ($room) use ($locations) {
                $location = $locations->firstWhere('id', $room->location_id);
                return [
                    'text' => $room->roomName,
                    'id' => $room->id,
                    'location' => $location ? $location->Location : null,
                    'color' => '#'.substr(md5($room->roomName), 0, 6) // Generate color based on room name hash
                ];
            });            
            $uniqueLocations = $roomsWithLocations->pluck('location')->unique()->values();
            return view('dashboard.ghm_booking', [
                'booking' => $booking,
                'roomsWithLocations' => $roomsWithLocations,
                'uniqueLocations' => $uniqueLocations
            ]);
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function index(Request $request)
    {
        try {
            
            $id = $request->id;
            $user_id = $this->getAuth()->id;
            $employeeid = $this->getEmployeeID()->id;
            $module_id = $this->getModuleId($this->modulename);
            $isAdmin = $this->getAuth()->isAdmin;
            $requestData = $request->all();

            $dataquery = $this->model->query();

            // $userId = $user_id;
            // $moduleId = $module_id;
            $subquery = "(select TOP 1 
                CASE WHEN a.user_id='".$user_id."' 
                then 1 else 0 end 
                from tbl_approverListReq l
                left join tbl_approver a on l.approver_id=a.id
                left join tbl_approvaltype r on a.approvaltype_id = r.id 
                where l.ApprovalAction='1' 
                and l.req_id = request_ghm.id and l.module_id = '".$module_id."' 
                and request_ghm.requestStatus='1'
                order by a.sequence)";

            if(!$isAdmin) {
                $dataquery->leftJoin('tbl_assignment',function($join) use ( $user_id, $module_id){
                    $join->on('request_ghm.id','=','tbl_assignment.req_id')
                        ->where("request_ghm.user_id", "!=", $user_id)
                        ->where('tbl_assignment.module_id',$module_id);
                });
            }

            $data = $dataquery
                ->selectRaw("request_ghm.id,
                codes.code, 
                request_ghm.user_id,
                request_ghm.description,
                request_ghm_room.roomName,
                request_ghm_room.bu,
                request_ghm_room.sector,
                request_ghm.ghm_room_id,            
                request_ghm.text,
                request_ghm.description,
                request_ghm.requestStatus,
                request_ghm.completeddate,
                request_ghm.ticketStatus,
                request_ghm.confirmationStatus,
                request_ghm.confirmationRemarks,
                request_ghm.startDate,
                request_ghm.endDate,
                request_ghm.created_at,
                request_ghm.updated_at,
                (SELECT STRING_AGG(emp.fullname, ', ')
                FROM OPENJSON(request_ghm.employee_id) 
                WITH (employee_id INT '$')
                LEFT JOIN employee.tbl_employee AS emp
                ON emp.id = employee_id
                ) AS employee_fullname,
                (SELECT STRING_AGG(value, ', ') FROM OPENJSON(request_ghm.guest)) AS guest,
                (SELECT STRING_AGG(value, ', ') FROM OPENJSON(request_ghm.family)) AS family,
                request_ghm_room.location_id, 
                employee.tbl_location.Location, 

              

                    CASE WHEN request_ghm.user_id='".$user_id."' then 1 else 0 end as isMine,
                    ".$subquery." as isPendingOnMe
                ")
                ->leftJoin('codes', 'request_ghm.code_id', '=', 'codes.id')
                ->leftJoin('request_ghm_room', 'request_ghm.ghm_room_id', '=', 'request_ghm_room.id')
                ->leftJoin('employee.tbl_location', 'request_ghm_room.location_id', '=', 'employee.tbl_location.id')
                ->with(['user', 'approverlist'])
                ->where(function ($query) use ($subquery, $user_id, $isAdmin, $employeeid, $module_id) {
                    $query->whereRaw($subquery . " = 1")
                        ->orWhere(function ($query) use ($user_id, $isAdmin, $employeeid, $module_id) {
                            if ($isAdmin) {
                                $query->where("request_ghm.user_id", "!=", $user_id)
                                    ->whereIn("request_ghm.requestStatus", [0,1,3,4]);
                            }
                             else {
                                $query->where("tbl_assignment.employee_id",$employeeid)
                                    ->whereIn("request_ghm.requestStatus", [3]);
                            }
                        })
                        ->orWhere("request_ghm.user_id", $user_id);
                })
                ->orderBy(DB::raw($subquery), 'DESC')
                ->orderByRaw("CASE WHEN request_ghm.user_id = '".$user_id."' THEN 0 ELSE 1 END, request_ghm.created_at desc")
                ->get();

            return response()->json([
                'status' => "show",
                'message' => $this->getMessage()['show'],
                'data' => $data,
            ])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $dataquery = $this->model->query();
            

            $data = $dataquery
                ->selectRaw("request_ghm.id,
            codes.code, 
            request_ghm.user_id,
            request_ghm.description,
            request_ghm.ghm_room_id,   
            request_ghm_room.bu,
            request_ghm_room.sector,          
            request_ghm.text,
            request_ghm.description,
            request_ghm.requestStatus,
            request_ghm.completeddate,
            request_ghm.ticketStatus,
            request_ghm.confirmationStatus,
            request_ghm.confirmationRemarks,
            request_ghm.startDate,
            request_ghm.endDate,
            request_ghm.created_at,
            request_ghm.updated_at,
            (SELECT STRING_AGG(emp.fullname, ', ')
                FROM OPENJSON(request_ghm.employee_id) 
                WITH (employee_id INT '$')
                LEFT JOIN employee.tbl_employee AS emp
                ON emp.id = employee_id
                ) AS employee_fullname,
            (SELECT STRING_AGG(value, ', ') FROM OPENJSON(request_ghm.guest)) AS guest,
            (SELECT STRING_AGG(value, ', ') FROM OPENJSON(request_ghm.family)) AS family,
            request_ghm_room.location_id, 
            employee.tbl_location.Location               
            ")
            ->leftJoin('codes', 'request_ghm.code_id', '=', 'codes.id')
            ->leftJoin('request_ghm_room', 'request_ghm.ghm_room_id', '=', 'request_ghm_room.id')
            ->leftJoin('employee.tbl_location', 'request_ghm_room.location_id', '=', 'employee.tbl_location.id')
            ->where('request_ghm.id',$id)
            ->first();

            if($data->code_id == null) {
                $data->code_id = $this->generateCode($this->modulename);
                $data->save();
            }
            // dd($data);

            return response()->json(['status' => "show", "message" => $this->getMessage()['show'] , 
            'data' => $data])->setEncodingOptions(JSON_NUMERIC_CHECK);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            // Ambil semua data dari request
            $requestData = $request->all();

            // Tambahkan user_id ke dalam data request
            $requestData['user_id'] = $this->getAuth()->id;
            // $requestData['requestStatus'] = 0;
            $requestData['code_id'] = $this->generateCode($this->modulename);

            // Buat data baru pada tabel utama
            $newData = $this->model->create($requestData);

            // Simpan id dari data baru
            $req_id = $newData->id;           
           

            // $this->createApprover($this->modulename, $req_id, null, null);
            
            return response()->json([
                "status" => "success",
                "message" => $this->getMessage()['store'],
                "data" => $newData
            ]);

        } catch (\Exception $e) {

            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }    

    public function update(Request $request, $id)
{
    try {
        // Validasi ID
        $id = intval($id);
        if ($id <= 0) {
            return response()->json(["status" => "error", "message" => "Invalid ID"]);
        }

        // Mengambil semua data dari request
        $module_id = $this->getModuleId($this->modulename);
        $requestData = $request->all();

        // Pastikan ticketStatus memiliki nilai default
        $requestData['ticketStatus'] = $request->input('ticketStatus', 'On Queue');
        $requestData['confirmationStatus'] = $request->input('confirmationStatus', null);

        // Tambahkan 1 hari ke tanggal yang relevan
        // $this->addOneDayToDate($requestData);

        $data = $this->model->findOrFail($id);

        if ($data->ticketStatus === null) {
            $requestData['ticketStatus'] = 'On Queue';
            $requestData['confirmationStatus'] = null;
        } else if ($data->ticketStatus === 'On Queue' || $data->ticketStatus === 'Immediately') {
            $requestData['confirmationStatus'] = 'Waiting';
        } else if ($data->ticketStatus === 'Completed') {
            if ($requestData['confirmationStatus'] === null || $requestData['confirmationStatus'] === 'Waiting') {
                $requestData['confirmationStatus'] = 'Waiting';
                $requestData['ticketStatus'] = $data->ticketStatus;
            } else if ($requestData['confirmationStatus'] === 'Reworked') {
                $requestData['ticketStatus'] = 'On Queue';
            }
        }

        // Start save history perubahan
        $fields = [
            'ticketStatus' => $requestData['ticketStatus'],
            'confirmationStatus' => ($data->ticketStatus === 'Completed' && $requestData['confirmationStatus'] !== 'Waiting')
                ? $requestData['confirmationStatus'] . ' - ' . $request->input('confirmationRemarks', '') 
                : null,
        ];

        foreach ($fields as $key => $value) {
            if ($value) {
                $this->approverAction($this->modulename, $id, $key, 1, $value, null);
            }
        }

        // Update data di database
        $data->update($requestData);

        // Generate notifikasi
        $notificationMessage = $this->generateNotificationMessage(
            $data,
            $this->modulename,
            $id,
            $requestData['ticketStatus'],
            $requestData['confirmationStatus']
        );

        // Jika confirmationStatus bukan 'Waiting', kosongkan confirmationRemarks
        if ($requestData['confirmationStatus'] !== 'Waiting') {
            $data->update(['confirmationRemarks' => null]);
        }

        // Mengembalikan response JSON
        return response()->json([
            'status' => "success",
            'message' => $this->getMessage()['update']
        ]);

    } catch (\Exception $e) {
        return response()->json(["status" => "error", "message" => $e->getMessage()]);
    }
}

    private function generateNotificationMessage($data, $modulename, $id, $ticketStatus, $confirmationStatus) {
        $locModel = "App\Models\Submission\\".$modulename;
        $model = new $locModel;
        $tableName = $model->getTableName();
        $module_id = $this->getModuleId($modulename);

        $getSubmissionData = DB::table($tableName)->where('id', $id)->first();
        $getCreator = User::findOrFail($getSubmissionData->user_id); //  get creator
        $assignmentdata = Assignmentto::leftJoin('employee.tbl_employee','tbl_assignment.employee_id','=','employee.tbl_employee.id')
                        ->leftJoin('users','employee.tbl_employee.LoginName','=','users.username')
                        ->select('employee.tbl_employee.*','users.email')
                        ->where('req_id',$getSubmissionData->id)
                        ->where('module_id',$module_id)
                        ->get();

        if ($ticketStatus === 'Completed') {
            $mailData = [
                "id" => 5, //notif status
                "action_id" => 0,
                "submission" => $getSubmissionData,
                "email" => $getCreator->email,
                "fullname" => $getCreator->fullname,
                "message" => $this->mailMessage()['ghmTicketCompleted'],
            ]; // send to creator
            Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$modulename,0));
        }
        if ($confirmationStatus === 'Completed') {
            foreach ($assignmentdata as $getPIC){
                $mailData = [
                    "id" => 5, //notif status
                    "action_id" => 0,
                    "submission" => $getSubmissionData,
                    "email" => $getPIC->email,
                    "fullname" => $getPIC->FullName,
                    "message" => $this->mailMessage()['ghmConfirmStatusCompleted'],
                ]; // send to PIC
                Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$modulename,0));
            }
        }
        if ($confirmationStatus === 'Reworked') {
            foreach ($assignmentdata as $getPIC){
                $mailData = [
                    "id" => 5, //notif status
                    "action_id" => 0,
                    "submission" => $getSubmissionData,
                    "email" => $getPIC->email,
                    "fullname" => $getPIC->FullName,
                    "message" => $this->mailMessage()['ghmConfirmStatusReworked'],
                ]; // send to PIC
                Mail::to($mailData['email'])->send(new SubmissionMail($mailData,$modulename,0));
            }
        }

    }

    public function destroy($id)
    {
        try {
            $module = $this->module->select('id', 'module')->where('module', $this->modulename)->first();
            $user_id = $this->getAuth()->id;
            if ($module) {
                DB::transaction(function () use ($id, $module, $user_id) {
                    ApproverListReq::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->delete();
                    ApproverListHistory::where('req_id', $id)
                        ->where('module_id', $module->id)
                        ->delete();
                    $data = $this->model->where('id',$id)->where('requestStatus',0)->where('user_id',$user_id)->first();
                    if ($data) {
                        $data->delete();
                    } else {
                        throw new \Exception($this->getMessage()['errordestroysubmission']);
                    }
                });
                return  response()->json(["status" => "success", "message" => $this->getMessage()['destroy']]);
            } else {
                return  response()->json(["status" => "error", "message" => $this->getMessage()['modulenotfound']]);
            }
        } catch (\Exception $e) {
            return response()->json(["status" => "error", "message" => $e->getMessage()]);
        }
    }
}

onAppointmentFormOpening: function(e) {
    const form = e.form;
    const appointmentData = e.appointmentData;

    console.log('Appointment Data:', appointmentData);

    if (appointmentData.employee_id && typeof appointmentData.employee_id === 'string') {
        appointmentData.employee_id = deserializeFromJSON(appointmentData.employee_id);
    }
    if (appointmentData.guest && typeof appointmentData.guest === 'string') {
        appointmentData.guest = deserializeFromJSON(appointmentData.guest);
    } else if (!appointmentData.guest) {
        appointmentData.guest = [];
    }
    if (appointmentData.family && typeof appointmentData.family === 'string') {
        appointmentData.family = deserializeFromJSON(appointmentData.family);
    } else if (!appointmentData.family) {
        appointmentData.family = [];
    }

    function validateBooking() {
        let guestCount = (form.getEditor("guest")?.option("value") || []).length;
        let familyCount = (form.getEditor("family")?.option("value") || []).length;

        let employeeValue = form.getEditor("employee_id")?.option("value");
        let employeeCount = Array.isArray(employeeValue) ? employeeValue.length : 0;

        let totalGuests = guestCount + familyCount + employeeCount;
        console.log("Total Guests:", totalGuests);

        let selectedRoom = form.getEditor("ghm_room_id")?.option("value");
        let room = roomsWithLocations.find(room => room.id === selectedRoom);
        let roomCapacity = room ? room.roomAccupancy : 0;

        console.log("Room Capacity:", roomCapacity);

        let doneButton = $(".dx-popup-bottom .dx-button.dx-popup-done");

        if (totalGuests > roomCapacity) {
            DevExpress.ui.notify("Jumlah tamu melebihi kapasitas kamar!", "error", 2000);
            doneButton.addClass("dx-state-disabled");
        } else {
            doneButton.removeClass("dx-state-disabled");
        }
    }

    validateBooking();
}