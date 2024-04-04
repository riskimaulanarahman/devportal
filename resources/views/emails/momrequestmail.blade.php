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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        p {
            margin-bottom: 20px;
        }

        .message, .assignment {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
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

        table {
            width: 70%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.5em;
            text-align: left;
            border: 1px solid #ccc;
        }
        th {
            background-color: #f5f5f5;
        }

    </style>
</head>
<body>
    <div class="container">
        <p>Dear, <b>{{ isset($mailData['all']) && $mailData['all'] == 1 ? 'All' : $mailData['fullname'] }}</b></p>
        @if ($mailData['action_id'] == 1)
            <p>You have received a New Submission from <b>{{ $mailData['creator'] }}</b></p>
        @endif
        <div class="message">
            {{ $mailData['message'] }}
        </div>
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
                    <th>Chairman</th>
                    <td>{{ $mailData['submission']->chairman }}</td>
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
        @if ($final == 1 )
        <div class="assignment">
            <h4>Participant :</h4>
            <ul>
                @foreach ($assignment as $assign)
                    <li>{{ $assign->FullName }}</li>
                @endforeach
            </ul>
        </div>
        @foreach ($detailmomtask->groupBy('category') as $category => $groupedTasks)
            <h4>Category : {{ $category }}</h4>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Section</th>
                        <th>Status</th>
                        <th>Deadline Date</th>
                        <th>Aging</th>
                        <th>Time Category</th>
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
                        @endphp
                        <tr>
                            <td>{{ $task->category }}</td>
                            <td>{{ $task->description }}</td>
                            <td>{{ $task->section }}</td>
                            <td>{{ $task->status }}</td>
                            {{-- <td>{{ $task->deadline_date }}</td> --}}
                            <td style="color: {{ $isUrgent ? 'red' : 'inherit' }};">{{ $task->deadline_date }}</td>
                            <td>{{ round($task->agings,1) }}</td>
                            <td>{{ $task->time_categorys }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p>Handled by:</p>
            <ul>
                @foreach ($groupedTasks as $task)
                    @if ($task->content && !in_array($task->FullName, $processedNames))
                        <li>{{ $task->FullName }}</li>
                        @php
                            $processedNames[] = $task->FullName;
                        @endphp
                    @endif
                @endforeach
            </ul>
        @endforeach
        <hr>
        @endif
        @if (!empty($mailData['remarks']))
            <div class="remarks">
                Remarks : {{ ucfirst($mailData['remarks']) }}
            </div>
        @endif
        <hr>
        <p class="footer">Go To DevPortal Click <a href="{{ env('APP_URL') }}">Here</a></p>
        <p class="footer">If you require any further information, please feel free to get in touch with us.</p>
        <p class="footer">Thank you for your interest in our products/services.</p>
        <p class="footer">Best regards,<br>System Development</p>
    </div>
</body>
</html>
