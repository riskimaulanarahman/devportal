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
        <p>Dear, <b>{{ $mailData['fullname'] }}</b></p>
        @if ($mailData['action_id'] == 1)
            <p>You have received a New Submission from <b>{{ $mailData['creator'] }}</b></p>
        @endif
        <div class="message">
            {{ $mailData['message'] }}
        </div>
        {{-- Tabel Master --}}
        <table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; width: 100%;">
            <tbody>
                <tr><th style="text-align:left;">Code No</th><td>{{ $mailData['submission']->code_id }}</td></tr>
                <tr><th style="text-align:left;">BU</th><td>{{ $mailData['submission']->bu }}</td></tr>
                <tr><th style="text-align:left;">Department</th><td>{{ $mailData['submission']->department_name }}</td></tr>
                <tr><th style="text-align:left;">Work Date</th><td>{{ $mailData['submission']->work_date }}</td></tr>
                <tr><th style="text-align:left;">Target</th><td>{{ $mailData['submission']->remarks }}</td></tr>
            </tbody>
        </table>

        {{-- Tabel Detail --}}
        @if(!empty($mailData['submission']->details) && count($mailData['submission']->details) > 0)
            <br>
            <table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr style="background-color:#f2f2f2;">
                        <th>Employee Name</th>
                        <th>Estimate Overtime Hours</th>
                        <th>Target</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mailData['submission']->details as $detail)
                        <tr>
                            <td>{{ $detail->emp_name }}</td>
                            <td>{{ $detail->EstimateOvertimeHours }}</td>
                            <td>{{ $detail->target }}</td>
                            {{-- <td>{{ $detail->remarks }}</td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
