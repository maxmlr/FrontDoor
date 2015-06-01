#!/bin/sh
#
# Token can be obtained from afraid.org -> update URL
#

# set your afraid.org update token
token="YOUR_TOKEN_HERE";

#@deprecared (ifconfig)
#ipv6="$(/sbin/ifconfig | grep inet6 | grep Global | tail -n1 | awk '{split($3,a,"/"); print a[1]}')"

# get ipv6
ipv6="$(/sbin/ip -6 addr show dev eth0 | sed -e's/^.*inet6 \(2001[^ ]*\)\/.*global.*dynamic.*$/\1/;t;d')"

# get ipv4
# TODO

echo "current ipv6: $ipv6" >> /var/log/dyndns.log
wget -O - "http://freedns.afraid.org/dynamic/update.php?$token=&address=$ipv6" >> /var/log/dyndns.log 2>&1
