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
        <p>Dear All,</p>
        {{-- @if ($mailData['action_id'] == 1) --}}
        <p>
            The following employees are scheduled to enter retirement and reach contract 
            end within the designated period. Please review and take appropriate action 
            regarding contract status and remaining tenure.
        </p>
        <hr>
        <p>
            <i>
                Berikut adalah daftar karyawan yang akan memasuki masa pensiun dan masa akhir 
                kontrak dalam periode yang ditentukan. Mohon untuk meninjau dan 
                melakukan tindak lanjut yang diperlukan terkait status dan sisa masa kontrak.
            </i>
        </p>
    {{-- @endif --}}
        {{-- <div class="message">
            {{ $mailData['message'] }}
        </div> --}}
        <h4>List of Employees Approaching Retirement</h4>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Business Unit</th>
            <th>Employee Name</th>
            <th>Employee Status</th>
            <th>Retirement</th>
            <th>Days Left</th>
        </tr>
    </thead>
        <tbody>
            @foreach($mailData['submissions'] as $s)
                @if($s->contract_status == 'Permanent')
                <tr>
                    <td>{{ $s->bu ?? '-' }}</td>
                    <td>{{ $s->emp_name ?? '-' }}</td>
                    <td>{{ $s->contract_status }}</td>
                    <td>{{ $s->retirement_date ?? '-' }}</td>
                    <td>
                        @if (is_numeric($s->dayToExp))
                            @if ($s->dayToExp < 0)
                                <span style="font-weight:bold; color:red;">{{ $s->dayToExp }}</span>
                            @else
                                <span style="font-weight:bold;">{{ $s->dayToExp }}</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <h4>List of Employees Approaching Contract Expiry</h4>
    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>Business Unit</th>
                <th>Employee Name</th>
                <th>Employee Status</th>
                <th>Periode</th>
                {{-- <th>Retirement</th> --}}
                <th>Days Left</th>
                <th>Contract</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mailData['submissions'] as $s)
                @if($s->contract_status == 'Contract')
                <tr>
                    <td>{{ $s->bu ?? '-' }}</td>
                    <td>{{ $s->emp_name ?? '-' }}</td>
                    <td>{{ $s->contract_status }}</td>
                    <td>
                        @if (strtotime($s->startContract) && strtotime($s->endContract))
                            {{ $s->startContract }} s/d {{ $s->endContract }}
                        @else
                            -
                        @endif
                    </td>
                    {{-- <td>{{ $s->retirement_date ?? '-' }}</td> --}}
                    <td>
                        @if (is_numeric($s->dayToExp))
                            @if ($s->dayToExp < 0)
                                <span style="font-weight:bold; color:red;">{{ $s->dayToExp }}</span>
                            @else
                                <span style="font-weight:bold;">{{ $s->dayToExp }}</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $s->sequence ?? '-' }}</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
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
