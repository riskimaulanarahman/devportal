<button class="btn btn-info" id="logHistoryButton">Log History</button>
<button class="btn btn-primary" id="changeDeptHead">Change Department Head</button>

<!-- Modal Bootstrap -->
<div class="modal fade" id="logHistoryModal" tabindex="-1" aria-labelledby="logHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logHistoryModalLabel">Log History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <div class="card-body" style="padding: 10px; !important">
                        <div id="loghistory" style="height: 500px;"></div>
                    </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Bootstrap for Change Department Head -->
<div class="modal fade" id="changeDeptHeadModal" tabindex="-1" aria-labelledby="changeDeptHeadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeDeptHeadModalLabel">Change Department Head</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="mb-3">
                        <label for="fromInput" class="form-label">From</label>
                        <select class="form-control" id="fromInput">
                            <option value="">Select current department head</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="toInput" class="form-label">To</label>
                        <select class="form-control" id="toInput">
                            <option value="">Select new department head</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <strong style="color: red">!! Ensure all modified entries are thoroughly reviewed for correctness before pressing the submit button to prevent any system errors or data discrepancies. !!</strong>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" id="btnChangeDeptHead" class="btn btn-primary">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Event Handler untuk Tombol -->
<script>
    document.getElementById('logHistoryButton').addEventListener('click', function() {
        var logHistoryModal = new bootstrap.Modal(document.getElementById('logHistoryModal'));
        logHistoryModal.show();
        dataGridlog.refresh();
    });

    

</script>