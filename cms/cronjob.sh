#!/bin/sh

# Link to website
SITE="http://www.themages.net"

# Usually "secret" cronjob key
CRONJOB="g0394gj0394kg"

# Silently tingling website
wget -q -O /dev/null --no-check-certificate -t 1 $SITE/admin/run/$CRONJOB