<button class="btn btn-info" id="logHistoryButton">Log Report</button>

<!-- Modal Bootstrap -->
<div class="modal fade" id="logHistoryModal" tabindex="-1" aria-labelledby="logHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logHistoryModalLabel">Log Report</h5>
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

<!-- Event Handler untuk Tombol -->
<script>
    document.getElementById('logHistoryButton').addEventListener('click', function() {
        var logHistoryModal = new bootstrap.Modal(document.getElementById('logHistoryModal'));
        logHistoryModal.show();
        dataGridlog.refresh();
    });

    

</script>