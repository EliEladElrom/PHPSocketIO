<?php

class GatewayHelper
{
    public static function generateMessage($sessionSID,$serverHostName) {
        $messageToSocketIO  = "GET /socket.io/1/websocket/".$sessionSID." HTTP/1.1\r\n";
        $messageToSocketIO .= "Host: ".$serverHostName."\r\n";
        $messageToSocketIO .= "Upgrade: WebSocket\r\n";
        $messageToSocketIO .= "Connection: Upgrade\r\n";
        $messageToSocketIO .= "Sec-WebSocket-Key: ".GatewayHelper::webSocketKey()."\r\n";
        $messageToSocketIO .= "Sec-WebSocket-Version: 13\r\n";
        $messageToSocketIO .= "Origin: *\r\n\r\n";
        return $messageToSocketIO;
    }

    public static function webSocketKey() {
        $length = 16;
        while (@$c++ * 16 < $length)
            @$tempKey .= md5(mt_rand(), true);

        return base64_encode(substr($tempKey, 0, $length));
    }
}

?>