#!/bin/bash

WORKING_DIRECTORY=$2
JOB=$3
PHP_VERSION=$(echo "${JOB}" | jq -r '.php')

apt install -y php8.1-ldap || exit 1

apt install -y slapd ldap-utils || exit 1

./.ci/OpenLDAP_run.sh
./.ci/load_fixtures.sh
