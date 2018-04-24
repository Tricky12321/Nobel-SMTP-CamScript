#!/usr/local/bin/php -q
<?php
set_time_limit(0);
function MailServer()
{
    $address = '0.0.0.0';
    $port = 25;

    /* Allow the script to hang around waiting for connections. */

    /* Turn on implicit output flushing so we see what we're getting
     * as it comes in. */
    ob_implicit_flush();


    PrintMessage("Started Server\n", LOG_INFO);
    if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
        PrintMessage("socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n", LOG_ERR);
    }

    if (socket_bind($sock, $address, $port) === false) {
        PrintMessage("socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n", LOG_ERR);
    }

    if (socket_listen($sock, 5) === false) {
        PrintMessage("socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n", LOG_ERR);
    }
    $pid = "";
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
            PrintMessage("Parent\n");
        } else {
            PrintMessage("Child\n");

            // we are the child
            $ip_adr = "";
            if (socket_getpeername($msgsock, $address, $port)) {
                $ip_adr = $address;
                PrintMessage("Connection from $address:$port\n", LOG_INFO);
            }
            HandleSocket($msgsock, $ip_adr);
            //PrintMessage("*****Starting to get data!\n");
            //exec("nohup /usr/bin/php parser.php '".escapeshellarg($ip_adr)."' &> /dev/null &");
        }


    } while ($pid && true);
    if ($pid) {
        socket_close($sock);
    }
}

function SocketRead($msgsock, $length, $type, $print = true)
{
    $buf = socket_read($msgsock, $length, $type);
    if ($print) {
        //    PrintMessage("GOT[".$got_num++."]:".addcslashes($buf,"\r\n")."\n");
    }
    return $buf;
}

function SocketSend($msgsock, $buf, $len, $flag)
{
    $buf = $buf . "\r\n";
    socket_send($msgsock, $buf, $len, $flag);
    //PrintMessage("SENT[".$sent_num++."]:".addcslashes($buf,"\r\n")."\r\n");
}

function PrintMessage($message, $priority = LOG_INFO)
{
    $time = date("Y-m-d H:i:s", time());
    echo "[" . $time . "] " . $message;
    syslog($priority, $message);
}

function HandleSocket($msgsock, $ip_adr)
{
    SocketSend($msgsock, "220 $ip_adr", 100, 0);
    SocketRead($msgsock, 5000, PHP_NORMAL_READ);
    SocketRead($msgsock, 5000, PHP_NORMAL_READ);
    SocketSend($msgsock, "250", 100, 0);
    SocketRead($msgsock, 5000, PHP_NORMAL_READ);
    SocketRead($msgsock, 5000, PHP_NORMAL_READ);
    SocketSend($msgsock, "250", 100, 0);
    SocketRead($msgsock, 5000, PHP_NORMAL_READ);
    SocketRead($msgsock, 5000, PHP_NORMAL_READ);
    SocketSend($msgsock, "250", 100, 0);
    SocketRead($msgsock, 5000, PHP_NORMAL_READ);
    SocketRead($msgsock, 5000, PHP_NORMAL_READ);
    SocketSend($msgsock, "354 Continue \r\n.\r\n", 1000, 0);
    // Reading Data
    while (SocketRead($msgsock, 5000, PHP_NORMAL_READ, false) != ".\r") {
        SocketRead($msgsock, 5000, PHP_NORMAL_READ, false);
    }
    SocketSend($msgsock, "250 Ok: queued as 12345", 1000, 0);
    SocketRead($msgsock, 5000, PHP_NORMAL_READ);
    SocketRead($msgsock, 5000, PHP_NORMAL_READ);
    SocketSend($msgsock, "221 Bye", 1000, 0);
    socket_close($msgsock);
}

?>