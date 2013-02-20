<?php

class SocketIOGateway {

    private $serverHostName;
    private $socketIOURL;
    private $serverPort = 80;
    private $session;

    public function __construct($socketIOUrl) {
        $this->socketIOURL = $socketIOUrl.'/socket.io/1';
        $this->extractUrlParams();
        $this->handshake();
        $this->connect();
    }

    private function handshake() {
        $ch = curl_init($this->socketIOURL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!$this->checkSslPeer)
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $res = curl_exec($ch);

        if ($res === false) {
            throw new \Exception(curl_error($ch));
        }

        $session = explode(':', $res);
        $this->session['sid'] = $session[0];
        $this->session['heartbeat_timeout'] = $session[1];
        $this->session['connection_timeout'] = $session[2];
        $this->session['supported_transports'] = array_flip(explode(',', $session[3]));

        if (!isset($this->session['supported_transports']['websocket']))
            throw new \Exception('This socket.io server do not support websocket protocol. Terminating connection...');

        return true;
    }

    private function extractUrlParams() {
        $url = parse_url($this->socketIOURL);
        $this->serverHostName = $url['host'];
        $this->serverPort = isset($url['port']) ? $url['port'] : null;

        if (array_key_exists('scheme', $url) && $url['scheme'] == 'https') {
            $this->serverHostName = 'ssl://'.$url['host'];
            if (!$this->serverPort) {
                $this->serverPort = 443;
            }
        }

        return true;
    }

    private function connect() {
        $this->fd = fsockopen($this->serverHostName, $this->serverPort, $errno, $errstr);

        if (!$this->fd) {
            throw new \Exception('fsockopen returned: '.$errstr);
        }

        require('utils/GatewayHelper.php');
        $messageToSocketIO = GatewayHelper::generateMessage($this->session['sid'],$this->serverHostName);
        fwrite($this->fd, $messageToSocketIO);

        $responseFromSocketIO = fgets($this->fd);

        if ($responseFromSocketIO === false)
            throw new \Exception('Socket.io did not respond properly. Aborting...');

        if ($responseHTTPType = substr($responseFromSocketIO, 0, 12) != 'HTTP/1.1 101')
            throw new \Exception('Unexpected Response. Expected HTTP/1.1 101 got '.$responseHTTPType.'. Aborting...');

        while(true) {
            $responseFromSocketIO = trim(fgets($this->fd));
            if ($responseFromSocketIO === '') break;
        }

        if ($this->read) {
            if ($this->read() != '1::') {
                throw new \Exception('Socket.io did not send connect response. Aborting...');
            } else {
                $this->stdout('info', 'Server report us as connected !');
            }
        }

        $this->heartbeatStamp = time();
    }

    public static function send($hostURL, $type, $id = null, $endpoint = null, $message = null) {

        $socketIOGateway = new SocketIOGateway($hostURL);
        require('Payload.php');
        $respond = TRUE;

        $raw_message = $type.':'.$id.':'.$endpoint.':'.$message;
        $payload = new Payload();
        $payload->setOpcode(Payload::OPCODE_TEXT)
            ->setMask(true)
            ->setPayload($raw_message)
        ;
        $encoded = $payload->encodePayload();
        fwrite($socketIOGateway->fd, $encoded);
        usleep(100000);

        fclose($socketIOGateway->fd);
        return $respond;
    }

    public static function sendMessageToRoom($hostURL, $roomName, $message) {
        $dataArgs = json_encode(array('roomName'=>$roomName, 'message'=>$message));
        $respond = SocketIOGateway::send($hostURL,5,null,null,json_encode(array('name' => 'php-message', 'args' => $dataArgs)));
        return $respond;
    }
}

?>