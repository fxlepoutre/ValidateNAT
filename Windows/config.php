<?php

/*********************************************************************************************/
/**********************************************************************************************

                                SMTP connection test script.
                                 Configuration parameters

***********************************************************************************************

                        Author: François-Xavier Lepoutre - Neolane
                              Last modification date: 18/10/11

**********************************************************************************************/
/*********************************************************************************************/


// $ipToTest[]: Array containing all the IPs to test. If this variable is null, the program
//    will pickup automatically all the IPs configured on the server and test them all. The IPs
//    identified here need to be locally defined on this server and seen in the result of a
//    "ipconfig" command.
//   Default: null.
//   Other possible values: "192.168.1.1"; (put several lines for several IPs).
$ipToTest[] = null;

// $timeoutSMTP: The timeout value before closing SMTP answers.
//   Default: 0.2.
//   Other possible value: any time in seconds.
$timeoutSMTP = 0.2;

// $mx: The IP address of the remote server to contact, and which answers back the public IP
//    address used to connect to it.
//   Default: "209.85.143.27" (Google MX0).
//   Other possible values: "74.125.39.26" (Google MX1), "65.55.92.152" (Hotmail MX1) have been
//    tested and worked successfuly.
$mx = "74.125.39.26";



?>