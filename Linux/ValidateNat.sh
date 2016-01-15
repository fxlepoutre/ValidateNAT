#!/bin/bash
# Bash script using netcat to check outbound NAT rules.
ip a list | grep inet | grep "scope global" | cut -d " " -f 6 | cut -d "/" -f1 | while read line ;
do
echo "#######################################################################################"
echo "### Opening connection to Google MX server with IP " $line " ..."
(sleep 1
echo EHLO jobsite.co.uk
sleep 1
echo QUIT) | nc -v -s $line gmail-smtp-in.l.google.com 25
sleep 1;
done;