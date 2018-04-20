<?php

$myfile = fopen("log.txt", "a+");
fwrite($myfile, $argv[1]."\n");
fclose($myfile);
