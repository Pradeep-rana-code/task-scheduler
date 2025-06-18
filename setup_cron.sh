#!/bin/bash

# Resolve absolute path to cron.php
SCRIPT_PATH=$(realpath "$(dirname "$0")/cron.php")

# Get PHP path dynamically
PHP_PATH=$(which php)

# CRON job line
CRON_JOB="0 * * * * $PHP_PATH $SCRIPT_PATH"

# Check if CRON job exists
if crontab -l 2>/dev/null | grep -Fq "$SCRIPT_PATH"; then
    echo "✅ CRON job already exists."
else
    # Add new CRON job
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "✅ CRON job added: $CRON_JOB"
fi
