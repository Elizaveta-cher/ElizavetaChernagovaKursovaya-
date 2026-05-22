#!/bin/bash
BACKUP_DIR="/backup"
DB_NAME="liza_store"
DB_USER="liza_admin"
DB_PASS="StrongPasswordHere123"

mkdir -p $BACKUP_DIR
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$(date +\%Y\%m\%d).sql.gz
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
