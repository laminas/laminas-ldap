#!/bin/bash

set -e

function log {
  local -r level="$1"
  local -r message="$2"
  >&2 echo -e "[${level}] ${message}"
}

function log_info {
  local -r message="$1"
  log "INFO" "$message"
}

function log_error {
  local -r message="$1"
  log "ERROR" "$message"
}

function assert_is_installed {
  local -r name="$1"

  if [[ ! $(command -v "${name}") ]]; then
    log_error "The executable '$name' is required by this script but is not installed or not in the system's PATH."
    exit 1
  fi
}

assert_is_installed openssl

cd "$(dirname "$0")"
mkdir -p certs

ORG='/O=Laminas/OU=CI test asset'
CA_DN="/CN=Root CA${ORG}"
CA_CERT="certs/root-ca"

SERVER_DN="/CN=example.com${ORG}"
SERVER_CERT="certs/server"

CLIENT_DN="/CN=admin${ORG}"
CLIENT_CERT="certs/client"

log_info "Generating CA certificate with \"${CA_DN}\""
openssl req -x509 \
  -newkey rsa:2048 \
  -sha256 \
  -nodes \
  -days 3650 \
  -extensions v3_ca \
  -subj "${CA_DN}" \
  -keyout "${CA_CERT}.key" \
  -out "${CA_CERT}.crt"

log_info "Created CA certificate in ${CA_CERT}.crt"
openssl x509 -in "${CA_CERT}.crt" -nameopt multiline -noout

echo

# ---

log_info "Generating server certificate with \"${SERVER_DN}\""
openssl genrsa -out "${SERVER_CERT}.key" 2048

log_info "Signing server certificate"
openssl req \
  -new \
  -sha256 \
  -key "${SERVER_CERT}.key" \
  -subj "${SERVER_DN}" \
  -out "${SERVER_CERT}.csr"

openssl x509 -req \
  -CA "${CA_CERT}.crt" \
  -CAkey "${CA_CERT}.key" \
  -days 3650 \
  -in "${SERVER_CERT}.csr" \
  -out "${SERVER_CERT}.crt"

rm -rf "${SERVER_CERT}.csr"
log_info "Created server certificate at ${SERVER_CERT}.crt and ${SERVER_CERT}.key"
openssl x509 -in "${SERVER_CERT}.crt" -text -nameopt multiline -noout

echo

# ---

log_info "Generating client certificate with \"${CLIENT_DN}\""
openssl genrsa -out "${CLIENT_CERT}.key" 2048

log_info "Signing client certificate"
openssl req \
  -new \
  -sha256 \
  -key "${CLIENT_CERT}.key" \
  -subj "${CLIENT_DN}" \
  -out "${CLIENT_CERT}.csr"
openssl x509 -req \
  -CA "${CA_CERT}.crt" \
  -CAkey "${CA_CERT}.key" \
  -days 3650 \
  -in "${CLIENT_CERT}.csr" \
  -out "${CLIENT_CERT}.crt"

rm -rf "${CLIENT_CERT}.csr"

log_info "Created client certificate at ${CLIENT_CERT}.crt and ${CLIENT_CERT}.key"
openssl x509 -in "${CLIENT_CERT}.crt" -text -nameopt multiline -noout

echo

# ---

log_info "Pregenerate dhparam"
openssl dhparam -out certs/dhparam.pem 2048
