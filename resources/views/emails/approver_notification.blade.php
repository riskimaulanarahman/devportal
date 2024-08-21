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
        <p>Dear, <b>Developer</b></p>
        <h1>Approver Notification</h1>
        <p>The following approver data has been updated:</p>

        @foreach ($approverData as $data)
            <li>
                <strong>{{ $data['field'] }}:</strong>
                <br>Old Value: {{ $data['old_value'] }}
                <br>New Value: {{ $data['new_value'] }}
            </li>
        @endforeach

        <h4>Approver List :</h4>
        <ul>
            @foreach ($listApprover as $approver)
                <li>{{ $approver->fullname }} | {{ $approver->sistem }} | {{ $approver->module }} | {{ $approver->role }}</li>
            @endforeach
        </ul>
        <hr>
        <p class="footer">Thank you for your interest in our products/services.</p>
        <p class="footer">Best regards,<br>System Development</p>
    </div>
</body>
</html>
