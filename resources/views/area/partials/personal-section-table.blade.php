@php($dataset = ($rows ?? collect())->filter(function ($row) {
    if (is_array($row)) {
        return count(array_filter($row, fn ($value) => $value !== null && $value !== '')) > 0;
    }
    return !empty($row);
}))
<div class="card mb-3">
    <div class="card-header">{{ $title }}</div>
    <div class="card-body">
        @if($dataset->count())
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            @foreach($headers as $header)
                                <th>{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dataset as $row)
                            <tr>
                                @foreach($row as $value)
                                    <td>{!! $value !== null && $value !== '' ? $value : '—' !!}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <span class="text-muted">Tidak ada data</span>
        @endif
    </div>
</div>
