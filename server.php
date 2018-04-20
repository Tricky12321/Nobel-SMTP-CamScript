#!/usr/local/bin/php -q
<?php
error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

$address = '0.0.0.0';
$port = 25;
PrintMessage("Started Server\n",LOG_INFO);
if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
    PrintMessage("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n", LOG_ERR);
}

if (socket_bind($sock, $address, $port) === false) {
    PrintMessage("socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n", LOG_ERR);
}

if (socket_listen($sock, 5) === false) {
    PrintMessage("socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n", LOG_ERR);
}

do {
    if (($msgsock = socket_accept($sock)) === false) {
        PrintMessage("socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n", LOG_ERR);
        break;
    }
    $pid = pcntl_fork();
    if ($pid == -1) {
        die('could not fork');
    } else if ($pid) {
        // we are the parent
    } else {
        // we are the child
        $ip_adr = "";
        if(socket_getpeername($msgsock , $address , $port))
        {
            $ip_adr = $address;
            PrintMessage("Connection from $address:$port\n",LOG_INFO);
        }
        $buf;
        socket_send($msgsock,"220",100,0);
        $buf = socket_read($msgsock, 5000, PHP_NORMAL_READ);
        socket_send($msgsock,"250",100,0);
        $buf = socket_read($msgsock, 5000, PHP_NORMAL_READ);
        socket_send($msgsock,"250",100,0);
        $buf = socket_read($msgsock, 5000, PHP_NORMAL_READ);
        socket_send($msgsock,"250",100,0);
        $buf = socket_read($msgsock, 5000, PHP_NORMAL_READ);
        socket_send($msgsock,"354",100,0);

        do {
            $buf = socket_read($msgsock, 5000, PHP_NORMAL_READ);
        } while ($buf != ".");
        socket_send($msgsock,"250",100,0);
        $buf = socket_read($msgsock, 5000, PHP_NORMAL_READ);
        if ($buf == "QUIT") {
            socket_close($msgsock);
        } else {
            socket_close($msgsock);
        }
        exec("nohup /usr/bin/php parser.php '".escapeshellarg($ip_adr)."' &> /dev/null &");
    }


} while ($pid && true);
socket_close($sock);

function PrintMessage($message, $priority) {
    $time = date("Y-m-d H:i:s",time());
    echo "[".$time."] ".$message;
    syslog($priority,$message);
}
?>