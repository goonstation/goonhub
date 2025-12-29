#!/bin/bash

# This script refreshes collation versions for all user databases
# and specifically for template1 on a PostgreSQL server. It checks for collation mismatches
# after the operation is completed, including within template1.
# Credit to https://gist.github.com/troykelly/616df024050dd50744dde4a9579e152e

# Ensure required environment variables are set for PostgreSQL.
: "${POSTGRES_USER?Environment variable POSTGRES_USER needs to be set}"
: "${POSTGRES_PASSWORD?Environment variable POSTGRES_PASSWORD needs to be set}"

POSTGRES_HOST=localhost
POSTGRES_PORT=5432

# Function to list all user databases and also include 'template1'.
# Outputs only the database names to stdout.
list_databases() {
    # Print an informational message to stderr to avoid contaminating the stdout output.
    >&2 echo "Retrieving list of databases from the PostgreSQL server..."
    # Connect to the 'postgres' system database and retrieve the list of database names.
    # Include 'template1' but exclude 'template0' explicitly.
    psql --host="$POSTGRES_HOST" --port="$POSTGRES_PORT" --username="$POSTGRES_USER" --dbname="postgres" -Atc \
    "SELECT datname FROM pg_database WHERE datistemplate = false OR datname = 'template1';"
}

# Refresh the database collation version.
# Apply refresh selectively to 'template1' and normal user databases.
refresh_collation_version() {
    local db=$1
    if [ "$db" != "template0" ]; then  # Skip template0
        # Print an informational message to stderr.
        >&2 echo "Refreshing collation version for database: $db..."
        # Alter the database to refresh the collation version.
        psql --host="$POSTGRES_HOST" --port="$POSTGRES_PORT" --username="$POSTGRES_USER" --dbname="$db" -c \
        "ALTER DATABASE \"$db\" REFRESH COLLATION VERSION;"
    fi
}

# Check for collation mismatches in all databases after the operations.
check_collation_mismatches() {
    # Print an informational message to stderr.
    >&2 echo "Checking for collation mismatches in all databases..."
    # Loop through each database and check for mismatching collations in table columns.
    while IFS= read -r db; do
        if [ -n "$db" ]; then
            # Print an informational message to stderr.
            >&2 echo "Checking database: $db for collation mismatches..."
            local mismatches=$(psql --host="$POSTGRES_HOST" --port="$POSTGRES_PORT" --username="$POSTGRES_USER" --dbname="$db" -Atc \
            "SELECT 'Mismatch in table ' || table_name || ' column ' || column_name || ' with collation ' || collation_name
             FROM information_schema.columns
             WHERE collation_name IS NOT NULL AND collation_name <> 'default' AND table_schema = 'public'
             EXCEPT
             SELECT 'No mismatch - default collation of ' || datcollate || ' used.'
             FROM pg_database WHERE datname = '$db';"
             )
            if [ -z "$mismatches" ]; then
                # Print an informational message to stderr.
                >&2 echo "No collation mismatches found in database: $db"
            else
                # Print an informational message to stderr.
                >&2 echo "Collation mismatches found in database: $db:"
                >&2 echo "$mismatches"
            fi
        fi
    done
}

# Main script execution starts here.

# Print start message to stderr.
>&2 echo "Starting the collation refresh process for all databases, including template1..."

# Retrieve the list of databases and store the result in a variable.
databases=$(list_databases)

# Check for an empty list of databases.
if [ -z "$databases" ]; then
    >&2 echo "No databases found for collation refresh. Please check connection details to PostgreSQL server."
    exit 1
fi

# Process each database to refresh collation version.
# 'template1' is included for refresh, while 'template0' is skipped.
for db in $databases; do
    refresh_collation_version "$db"
done

# Checking for collation mismatches after collation refresh.
# Pass the list of databases to the check_collation_mismatches function through stdin.
echo "$databases" | check_collation_mismatches

# Print completion message to stderr.
>&2 echo "Collation refresh process completed."
