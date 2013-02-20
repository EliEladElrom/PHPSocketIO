<!DOCTYPE HTML>
<html>
<head>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
    <script src="http://localhost:8080/socket.io/socket.io.js"></script>

    <script>
        var roomName = 'room1';

        $(document).ready(function() {

            var socket = io.connect('http://127.0.0.1:8080');

            $("#messageBox").keypress( function(event) {
                if (event.which == '13') {
                    sendMessage();
                    event.preventDefault();
                }
            });

            socket.on('connect', function (data) {
                console.log('join room:: '+roomName);
                socket.emit('join room', roomName );
            });

            socket.on('message', function (data) {
                add_message(data);
            });

            function add_message(message) {
                $("#chatLog").append(message);
                $("#chatLog").append("<BR>");
            }

            function sendMessage()
            {
                var message = $("#messageBox").val();
                $("#messageBox").val('');

                // send directly to socketIO
                // socket.emit('message', r );

                // send to PHP
                $.ajax({
                    type: "POST",
                    data: {roomName:roomName, message:message},
                    url: 'socketAPI.php',
                    success: function (response) {
                        console.log(response);
                    }
                });
            }
        });
    </script>
</head>

<body>
<div id="chat" style="height: 200px; width: 200px; border: 1px solid grey;">
    <div id="chatLog" style="height: 178px; width: 200px; overflow-y: scroll;"></div>
    <input type="text" id="messageBox" style="margin-left: 2px; width: 193px;">
</div>
</body>
</html>