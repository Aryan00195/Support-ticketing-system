<!DOCTYPE html>
<html>
<head>
    <title>New Ticket Created</title>
    <style>
           a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h2>New Ticket Created</h2>
    <p>Hello {{ $ticket->user->name }},</p>
    
    <p>A new ticket has been created with the following details:</p>
    <ul>
        <li><strong>Title:</strong> {{ $ticket->title }}</li>
        <li><strong>Content:</strong> {{ $ticket->content }}</li>
      
    </ul>
    <a class="button" href="{{$ticketUrl}}">View Ticket</a>
    <p>Thank you,</p>
    <p>{{ config('app.name') }} Team</p>
</body>
</html>
