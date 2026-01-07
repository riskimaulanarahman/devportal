<!-- resources/views/components/panduan-modal.blade.php -->

{{-- <button id="downloadBtn" class="btn btn-danger" data-bs-toggle="modal" data-bs-target=".bs-modal-panduan">
    <i class="fa fa-download"></i> Download Panduan <i class="fa fa-download"></i>
</button> --}}

<div class="modal fade bs-modal-panduan" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mySmallModalLabel">Panduan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/Guesthouse & Mess Booking System.pdf', '_blank')">Guesthouse & Mess Booking System</button></li>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/HRSC Panduan.pdf', '_blank')">HR Service Care</button></li>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/IT Active Directory Panduan.pdf', '_blank')">IT - Active Directory</button></li>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/JDI Online.pdf', '_blank')">JDI Online</button></li>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/Legal Checklist.pdf', '_blank')">Legal Checklist</button></li>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/Material Request.pdf', '_blank')">Material Request</button></li>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/Mom Online.pdf', '_blank')">MoM Online</button></li>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/SKYMAP System.pdf', '_blank')">SKYMAP</button></li>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/DevPortal Panduan.pdf', '_blank')">Project Management & Ticket Request</button></li>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/SPKL.pdf', '_blank')">SPKL</button></li>
                    <li class="mb-1"><button class="btn btn-primary" onclick="window.open('{{ env('APP_URL') }}/public/upload/panduan/WPHC.pdf', '_blank')">WPHC</button></li>
                </ul>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->