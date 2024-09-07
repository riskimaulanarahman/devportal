@extends('layouts.master')
@section('title') @lang('translation.Dashboards') @endsection
@section('content')
@section('pagetitle') CVAF @endsection

<div class="row">
    <div class="container mt-5">
        <h2>Penilaian Karyawan Berdasarkan Core Value</h2>
        <form id="evaluationForm">
            @foreach($coreValues as $coreValue)
                <h4>{{ $coreValue->name }}</h4>
                @foreach($coreValue->questions as $question)
                    <div class="form-group">
                        <label>{{ $question->question }}</label>
                        <select class="form-control" name="answers[{{ $coreValue->id }}][{{ $question->id }}]">
                            <option value="Low">Low</option>
                            <option value="Mid">Mid</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                @endforeach
            @endforeach
            <input type="hidden" name="employee_id" value="1"> <!-- Ganti dengan ID karyawan yang sesuai -->
            <button type="button" class="btn btn-primary mt-5" data-toggle="modal" data-target="#evaluationModal">Submit</button>
        </form>
    </div>
    
    <!-- Modal -->
    <div class="modal fade" id="evaluationModal" tabindex="-1" role="dialog" aria-labelledby="evaluationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="evaluationModalLabel">Hasil Penilaian</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Hasil penilaian akan ditampilkan di sini -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('script')
<script>
    $(document).ready(function() {
        $('#evaluationModal').on('show.bs.modal', function (event) {
            var formData = $('#evaluationForm').serialize();
            $.post('/calculate-score', formData, function(data) {
                var modalBody = $('#modalBody');
                modalBody.empty();
                data.forEach(function(score) {
                    modalBody.append('<p>Core Value ' + score.core_value_id + ': ' + score.scaled_score + '</p>');
                });
            });
        });
    });
</script>
@endsection
