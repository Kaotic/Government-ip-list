<?php
if (substr(php_sapi_name(), 0, 3) != 'cli'):
    die('This script can only be run from command line!');
endif;

define('CR',"\r\n");

function get_list_ip($ip_addr_cidr){
    $ip_arr = explode("/", $ip_addr_cidr);
    $bin = "";

    for($i=1;$i<=32;$i++) {
        $bin .= $ip_arr[1] >= $i ? '1' : '0';
    }

    $ip_arr[1] = bindec($bin);

    $ip = ip2long($ip_arr[0]);
    $nm = $ip_arr[1];
    $nw = ($ip & $nm);
    $bc = $nw | ~$nm;
    $bc_long = ip2long(long2ip($bc));

    for($zm=1;($nw + $zm)<=($bc_long - 1);$zm++)
    {
        printf(long2ip($nw + $zm).CR);
    }
    return true;
}

class AsyncOperation extends Thread {

    public function __construct($ip) {
        $this->ip = $ip;
    }

    public function run() {
        if ($this->ip) {
            $ip = $this->ip;
            get_list_ip($ip);
        }
    }
}


$stack = array();

$file_handle = fopen("blacklistip.txt", "rb");
while (!feof($file_handle) ) {
    $line_of_text = fgets($file_handle);
    $stack[] = new AsyncOperation($line_of_text);
}
fclose($file_handle);

foreach ( $stack as $t ) {
    $t->start();
}
?>
