#!/bin/bash

WORKING_DIRECTORY=$2
JOB=$3
PHP_VERSION=$(echo "${JOB}" | jq -r '.php')

#apt-get install -y php8.1-ldap || exit 1
apt-get install -y iptables conntrack || exit 1
apt-get install -y slapd ldap-utils || exit 1

sudo ./.ci/config_iptables.sh
./.ci/OpenLDAP_run.sh
./.ci/load_fixtures.sh
