<?php

$roomName = $_POST['roomName'];
$message = $_POST['message'];
$hostName = 'http://localhost:8080';

require('PHPSocketIO/SocketIOGateway.php');
$respond = SocketIOGateway::sendMessageToRoom($hostName,$roomName,$message);

if ($respond == FALSE)
    echo "Couldn't send message... terminating!";
else
    echo "Success";

?>