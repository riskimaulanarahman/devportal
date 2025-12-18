{{-- <button class="btn btn-info" id="logHistoryButton">Log Report</button> --}}
{{-- <button class="btn btn-primary" id="logIssue">Log Issue</button> --}}

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

<!-- Tombol Trigger -->
{{-- <button class="btn btn-primary" id="logIssue">Log Issue</button> --}}

<!-- Modal Bootstrap for Viewing Log Issue -->
<div class="modal fade" id="logIssueModal" tabindex="-1" aria-labelledby="logIssueModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="logIssueModalLabel">Log Issue Summary</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <!-- Ringkasan Status -->
        <div class="row mb-3">
          <div class="col-md-6">
            <div class="card text-white bg-success" id="filterCardAktif" style="cursor: pointer;">
              <div class="card-body">
                <h6 class="card-title">WPHC Aktif</h6>
                <p class="card-text fs-4" id="wphcActiveCount">0</p>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card text-white bg-secondary" id="filterCardNonAktif" style="cursor: pointer;">
              <div class="card-body">
                <h6 class="card-title">WPHC Non-Aktif</h6>
                <p class="card-text fs-4" id="wphcInactiveCount">0</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Detail Per Log Issue -->
        <div class="accordion" id="wphcDetailsAccordion"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- Event Handler untuk Tombol -->
{{-- <script>
    document.getElementById('logHistoryButton').addEventListener('click', function() {
        var logHistoryModal = new bootstrap.Modal(document.getElementById('logHistoryModal'));
        logHistoryModal.show();
        dataGridlog.refresh();
    });
</script> --}}