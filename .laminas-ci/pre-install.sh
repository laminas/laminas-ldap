#!/bin/bash

WORKING_DIRECTORY=$2
JOB=$3
PHP_VERSION=$(echo "${JOB}" | jq -r '.php')

apt-get install -y iptables conntrack || exit 1
sudo ./.ci/config_iptables.sh

apt-get install -y slapd ldap-utils || exit 1

./.ci/OpenLDAP_run.sh
./.ci/load_fixtures.sh
