<?php

/*********************************************************************************************/
/**********************************************************************************************

                                SMTP connection test script.
                                         Run file

***********************************************************************************************

                        Author : François-Xavier Lepoutre - Neolane
                              Last modification date: 18/10/11

**********************************************************************************************/
/*********************************************************************************************/


///////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////// DO NOT MODIFY ANYTHING IN THIS FILE. ////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////// FUNCTIONS //////////////////////////////////////////


// Returns a string containing the response of the socket.
function getResponse(&$socket) {
	$result = null;
	//echo "\n\n******************** Server ********************\r\n";
	while ($buf = socket_read($socket, 2048)) {
		//echo $buf;
		$result .= $buf;
	}
	//echo "************************************************\r\n";
	return $result;
}

// Returns the number of bytes written if the it was possible to write the string on the socket, otherwise returns false.
function writeRequest(&$socket, $sToWrite) {
	//echo "\n\n******************** Client ********************\r\n";
	//echo $sToWrite;
	return socket_write($socket, $sToWrite);
	//echo "************************************************\r\n";
}

// Returns an array, of which each element is a string representing an IP present in the input string.
function find_ip($string2test) {
	$resultIpArray = null;
	$ipArray = null;
    $ipregex = "/(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)/";
	if (preg_match_all($ipregex, $string2test,$ipArray) > 0) {
		return $ipArray[0];
	} else return false ;
}

// Returns an array with the IP addresses of the local server.
function getServerAddresses() {
	$serverAddresses = null;
	$regexIPAddressLine = "/IPv4 Address|Adresse IP|IP Address/";
    if(stristr(PHP_OS, 'WIN')) {
        exec('ipconfig /all', $catch);
        foreach($catch as $line) {
			if( preg_match($regexIPAddressLine, $line) ) {
				foreach(find_ip($line) as $ip) {
					$serverAddresses[] = $ip;
				}
			}
        }
    } else {
		echo("Error: Non Windows operating systems not supported.\n");
        //$ifconfig = shell_exec('/sbin/ifconfig eth0');
        //preg_match('/addr:([\d\.]+)/', $ifconfig, $match);
        //return $match[1];
    }
	return $serverAddresses;
}

// Returns a string containing the IP seen in a connexion between a source and a destination.
function getIp($src, $dst, $timeout) {
	$port = 25;
	$localHostname = "whatsmyip.test";
	$result = null;
	// Create a new socket
	$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("\r\n\r\nFatal error - Could not create socket.\r\n");
	// Configure a timeout for read
	socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, $timeout);
	// Bind to local IP address
	socket_bind($sock, $src) or die("\r\n\r\nFatal error - Could not bind to socket.\r\nCheck IP address ".$src." is configured on this computer.\r\n");
	// Connect to destination IP address
	if (socket_connect($sock, $dst, $port)) {
		// Listen to server's HELO
		$call1 = getResponse($sock);
		// If return status is 220.
		if ( substr($call1,0,3) == "220" ) {
			// Write client HELO
			if (writeRequest($sock, "EHLO ".$localHostname." \r\n")) {
				// Listen to server's answer
				$call2 = getResponse($sock);
				if ( substr($call2,0,3) == "250" ) {
					// Get the IP, but only the last one (Hotmail provides 2 IPs)
					$result = end(find_ip($call2));
				} else {
					$result = "Server response (status 250) to client EHLO was expected from ".$dst.":".$port.", didn't arrive.";
				}
				// Write disconnection
				writeRequest($sock, "QUIT" . "\r\n");
			} else {
				$result = "Could not write EHLO on socket.";
			}
		} else {
			$result = "Server HELO (status 220) was expected from ".$dst.":".$port.", didn't arrive.";
		}
	} else {
		$result = "Could not open connection to ".$dst.":".$port." with this IP.";
	}
	// Close socket
	socket_close($sock);
	return $result;
}

//////////////////////////////////////// MAIN PROGRAM /////////////////////////////////////////

// Reduces reporting to avoid Warning errors.
error_reporting(E_ALL ^ E_WARNING);

// Includes configuration parameters.
include("config.php");

// The SO_RCVTIMEO implementation of PHP asks for milliseconds instead of seconds in the "sec" field of the array, the "usec" field is useless, but needs to be set here.
$timeoutSMTP *= 1000;
$timeout = array(
	"sec"	=>	$timeoutSMTP, 
	"usec" 	=>	0
);

// Use the addresses of the oprion or get the addresses automatically.
if (is_null($ipToTest[0])) {
	$ipList = getServerAddresses();
} else {
	$ipList = $ipToTest;
}

// Print column headers.
echo "---------------------------------------\r\n";
echo " Local IP\t\t External IP\r\n";
echo "---------------------------------------\r\n";

// For each address found on the server, get the public IP address seen.
foreach($ipList as $sourceIP) {
	echo $sourceIP."\t==>\t";
	echo getIp($sourceIP, $mx, $timeout);
	echo "\r\n";
}

?>
