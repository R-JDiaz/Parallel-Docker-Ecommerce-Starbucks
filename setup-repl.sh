#!/bin/bash
# Master-Slave replication setup script

# --- CONFIGURATION ---
MASTER_CONTAINER="mysql_master"
SLAVE_CONTAINER="mysql_slave"
MYSQL_ROOT_PASSWORD="root"
REPL_USER="replicator"
REPL_PASSWORD="replica_pass"
DB_NAME="softeng"

# --- Step 1: Configure master ---
echo "Configuring master..."
docker exec -i $MASTER_CONTAINER mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "
CREATE USER IF NOT EXISTS '$REPL_USER'@'%' IDENTIFIED BY '$REPL_PASSWORD';
GRANT REPLICATION SLAVE ON *.* TO '$REPL_USER'@'%';
FLUSH PRIVILEGES;
FLUSH TABLES WITH READ LOCK;
SHOW MASTER STATUS;
" > master_status.txt

# Extract File and Position from master_status.txt
BINLOG_FILE=$(awk '/mysql-bin/ {print $1}' master_status.txt | head -n1)
BINLOG_POS=$(awk '/mysql-bin/ {print $2}' master_status.txt | head -n1)

echo "Master binlog file: $BINLOG_FILE"
echo "Master binlog position: $BINLOG_POS"

# --- Step 2: Configure slave ---
echo "Configuring slave..."
docker exec -i $SLAVE_CONTAINER mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "
STOP SLAVE;
CHANGE MASTER TO
  MASTER_HOST='$MASTER_CONTAINER',
  MASTER_USER='$REPL_USER',
  MASTER_PASSWORD='$REPL_PASSWORD',
  MASTER_LOG_FILE='$BINLOG_FILE',
  MASTER_LOG_POS=$BINLOG_POS;
START SLAVE;
SHOW SLAVE STATUS\G
"

echo "Replication setup complete!"
