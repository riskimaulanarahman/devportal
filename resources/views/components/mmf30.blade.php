<button class="btn btn-info" id="HistoryButton">History Approval</button>

<!-- Modal Bootstrap -->
<div class="modal fade" id="HistoryModal" tabindex="-1" aria-labelledby="HistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-with-margin">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="HistoryModalLabel">History Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <div class="card-body" style="padding: 10px; !important">
                        <div id="historyMMF30" style="height: 500px;"></div>
                    </div>
            </div>
        </div>
    </div>
</div>

<!-- Event Handler untuk Tombol -->
<script>
    document.getElementById('HistoryButton').addEventListener('click', function() {
        var HistoryModal = new bootstrap.Modal(document.getElementById('HistoryModal'));
        HistoryModal.show();
        // dataGridhistory.refresh();
    });
</script>