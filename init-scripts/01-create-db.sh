#!/bin/bash
set -e

# Create a postgres database for the user set in the .env file.
# Everything works fine without this, but this prevents a FATAL
# error from spamming the logs
psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
    CREATE DATABASE "$POSTGRES_USER";
    GRANT ALL PRIVILEGES ON DATABASE "$POSTGRES_USER" TO "$POSTGRES_USER";
EOSQL