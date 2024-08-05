<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
            
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 5px;
        }

        p {
            margin-bottom: 20px;
        }

        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .assignment {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 10px !important;
        }

        .remarks {
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            color: red;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #999;
        }

        hr {
            color: #999;
        }

        /* CSS untuk tabel responsif */
        .table-responsive {
            width: 100%;
            font-size: 10px !important;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 0.5em;
            text-align: left;
            border: 1px solid #ccc;
            /* word-wrap: break-word; */
        }

        th {
            background-color: #f5f5f5;
        }

        .page-break { page-break-after: always; }

    </style>
</head>
<body>
    <div class="container">
        @if (!isset($mailData['pdf']))
            <p>Dear, <b>{{ isset($mailData['all']) && $mailData['all'] == 1 ? 'All' : $mailData['fullname'] }}</b></p>
            @if ($mailData['action_id'] == 1)
                <p>You have received a New Submission from <b>{{ $mailData['creator'] }}</b></p>
            @endif
            <div class="message">
                {!! $mailData['message'] !!}
            </div>
        @else
            <h2>MoM Online <span style="font-size: 8px">{{ date('Y-m-d h:i') }}</span></h2>
        @endif
        <div class="table-responsive">
            <table>
                <tbody>
                    <tr>
                        <th>Code No</th>
                        <td>{{ $code }}</td>
                    </tr>
                    <tr>
                        <th>Subject Meeting</th>
                        <td>{{ $mailData['submission']->subjectMeeting }}</td>
                    </tr>
                    <tr>
                        <th>Date Meeting</th>
                        <td>{{ $mailData['submission']->date }}</td>
                    </tr>
                    <tr>
                        <th>Lead By</th>
                        <td>{{ $mailData['submission']->chairman }}</td>
                    </tr>
                    <tr>
                        <th>Note Taker By</th>
                        {{-- @if (!isset($mailData['pdf']))
                            <td>{{ $mailData['submission']->fullname }}</td>
                        @else --}}
                            <td>{{ $mailData['fullname'] }}</td>
                        {{-- @endif --}}
                    </tr>
                    <tr>
                        <th>Venue</th>
                        <td>{{ $mailData['submission']->venue }}</td>
                    </tr>
                    <tr>
                        <th>isZoom</th>
                        <td>{{ ($mailData['submission']->isZoom == 1) ? 'Yes' : 'No' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if ($final == 1 )
        @if (!isset($mailData['pdf']))
            <div>
        @else
            <div class="assignment">
        @endif
            <h4>Participant :</h4>
            <p>
                @foreach ($assignment as $index => $assign)
                    {{ $assign->FullName }}@if (!$loop->last), @endif
                @endforeach
            </p>
        </div>

        @foreach ($detailmomtask->groupBy('category') as $category => $groupedTasks)
            <h4>Category : {{ $category }}</h4>
            <div class="table-responsive">
                <table class="page-break">
                    <thead>
                        <tr>
                            <th style="text-align:center" rowspan="2">Description</th>
                            <th style="text-align:center" rowspan="2">Section</th>
                            <th style="text-align:center" rowspan="2">Status</th>
                            <th style="text-align:center" rowspan="2">Deadline Date</th>
                            <th style="text-align:center" rowspan="2">Aging</th>
                            <th style="text-align:center" rowspan="2">Time Category</th>
                            <th style="text-align:center" rowspan="2">Handled By</th>
                            <th colspan="3" style="text-align:center">Last Update</th>
                        </tr>
                        <tr>
                            <th style="text-align:center">Description</th>
                            <th style="text-align:center">Date</th>
                            <th style="text-align:center">Updated By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $processedNames = [];
                            $uniqueTasks = $groupedTasks->unique(function ($task) {
                                return $task->description . $task->section . $task->status . $task->deadline_date;
                            });
                            $today = \Carbon\Carbon::now();

                        @endphp
                        @foreach ($uniqueTasks as $task)
                            @php
                                $deadlineDate = \Carbon\Carbon::parse($task->deadline_date);
                                $isUrgent = $deadlineDate->lte($today->copy()->addDays(7));

                                // Mengambil nama-nama yang menangani tugas yang sama
                                $handledByList = $groupedTasks->filter(function ($t) use ($task) {
                                    return $t->description == $task->description && $t->section == $task->section && $t->status == $task->status && $t->deadline_date == $task->deadline_date;
                                })->map(function ($t) {
                                    return ['FullName' => explode(' ', trim($t->FullName))[0], 'Content' => $t->content];
                                })->unique(function ($item) {
                                    return $item['FullName'] . $item['Content'];
                                });
                                $handledBy = $handledByList->map(function ($item) {
                                    return $item['FullName'] . ' (' . $item['Content'] . ')';
                                })->implode(', ');

                                // Mengambil data update
                                $updateDescription = $task->UpdateDescription ?? 'N/A';  // Gunakan null coalescing operator untuk default value
                                $updateDate = $task->UpdateDate ? \Carbon\Carbon::parse($task->UpdateDate)->format('Y-m-d') : 'N/A';
                                $updateName = $task->UpdateName ?? 'N/A';

                                // Menentukan apakah baris harus dihighlight berdasarkan ID
                                if(!isset($mailData['pdf'])) {
                                    $highlightRow = $task->id == $mailData['highlightedTaskId'];
                                } else {
                                    $highlightRow = null;
                                }
                                    
                            @endphp
                            <tr style="background-color: {{ $highlightRow ? '#ffffcc' : 'inherit' }};">
                                <td>{{ $task->description }}</td>
                                <td>{{ $task->section }}</td>
                                <td>{{ $task->status }}</td>
                                <td style="color: {{ $isUrgent ? 'red' : 'inherit' }}; background-color: {{ $isUrgent ? '#F6F6F6' : 'inherit' }};">{{ $task->deadline_date }}</td>
                                <td>{{ round($task->agings,1) }}</td>
                                <td>{{ $task->time_categorys }}</td>
                                <td style="vertical-align: middle">
                                    @if (!isset($mailData['pdf']))
                                        <ul>
                                            @foreach ($handledByList as $item)
                                                <li>{{ $item['FullName'] }} ({{ $item['Content'] }})</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        {{ $handledBy }}
                                    @endif
                                </td>
                                <td>{{ $updateDescription }}</td>
                                <td>{{ $updateDate }}</td>
                                <td>{{ $updateName }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
        @endif
        @if (!empty($mailData['remarks']))
        <hr>
            <div class="remarks">
                Remarks : {{ ucfirst($mailData['remarks']) }}
            </div>
        @endif
        {{-- @if (!isset($mailData['pdf'])) --}}
            <hr>
            <p class="footer">Go To DevPortal Click <a href="{{ env('APP_URL') }}">Here</a></p>
            <p class="footer">If you require any further information, please feel free to get in touch with us.</p>
            <p class="footer">Thank you for your interest in our products/services.</p>
            <p class="footer">Best regards,<br>System Development</p>
        {{-- @endif --}}
    </div>
</body>
</html>