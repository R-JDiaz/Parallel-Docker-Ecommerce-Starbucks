# Starbucks E-Commerce Application

A Docker-based e-commerce application for Starbucks with Master-Slave MySQL replication.

## Prerequisites

- Docker
- Docker Compose
- Bash shell (for running setup scripts)

## Project Structure

```
├── Dockerfile              # PHP Apache container configuration
├── docker-compose.yml     # Docker services orchestration
├── setup-repl.sh          # Master-Slave replication setup script
├── master.cnf             # MySQL Master configuration
├── slave.cnf              # MySQL Slave configuration
├── apache/                # Apache configuration
│   └── default.conf
├── app/                   # Application code
│   └── starbucks-ecommerce/
│       ├── Code/          # Backend & Frontend code
│       ├── Category/      # Product images by category
│       └── Volt/          # Additional templates
└── commands.txt           # Useful Docker commands
```

## Quick Start

### 1. Build and Start Containers

```bash
# Build and start all services
docker-compose up -d

# Verify containers are running
docker ps
```

### 2. Set Up MySQL Replication

After containers are running, execute the replication setup script:

```bash
# Make the script executable
chmod +x setup-repl.sh

# Run the replication setup
./setup-repl.sh
```

This script will:
- Create a replication user on the master
- Configure the slave to replicate from the master
- Create the `softeng` database on both servers

### 3. Access the Application

- **Web Application**: http://localhost:8000
- **MySQL Master**: localhost:3306
- **MySQL Slave**: localhost:3307

## Database Credentials

| Service     | Username | Password | Database |
|-------------|----------|----------|----------|
| MySQL Root  | root     | root     | softeng  |
| Replicator  | replicator | replica_pass | - |

## MySQL Replication Details

- **Master**: `mysql_master` (port 3306)
- **Slave**: `mysql_slave` (port 3307)
- **Replication User**: `replicator` / `replica_pass`

### Verify Replication Status

```bash
# Check master status
docker exec -it mysql_master mysql -uroot -p -e "SHOW MASTER STATUS\G"

# Check slave status
docker exec -it mysql_slave mysql -uroot -p -e "SHOW SLAVE STATUS\G"
```

## Useful Docker Commands

```bash
# View logs
docker-compose logs -f

# View logs for specific service
docker-compose logs -f web
docker-compose logs -f mysql_master
docker-compose logs -f mysql_slave

# Stop all containers
docker-compose down

# Stop and remove volumes (WARNING: deletes all data)
docker-compose down -v

# Rebuild containers
docker-compose up -d --build

# Access MySQL Master
docker exec -it mysql_master mysql -uroot -p

# Access MySQL Slave
docker exec -it mysql_slave mysql -uroot -p
```

## Application Architecture

### Services

1. **Web (PHP + Apache)**: Serves the e-commerce application
2. **MySQL Master**: Primary database handling writes
3. **MySQL Slave**: Read replica for load balancing

### Technology Stack

- **Backend**: PHP 8.3
- **Web Server**: Apache
- **Database**: MySQL 8.0 with replication
- **Frontend**: HTML, JavaScript, CSS

## Troubleshooting

### Replication Not Working

1. Check if containers are running:
   ```bash
   docker ps
   ```

2. Check replication status:
   ```bash
   docker exec -it mysql_slave mysql -uroot -p -e "SHOW SLAVE STATUS\G"
   ```

3. Check master logs:
   ```bash
   docker-compose logs mysql_master
   ```

### Container Won't Start

1. Check for port conflicts:
   ```bash
   netstat -tulpn | grep -E '3306|3307|8000'
   ```

2. Check Docker logs:
   ```bash
   docker-compose logs
   ```

### Database Connection Issues

1. Ensure MySQL containers are fully started (wait ~30 seconds)
2. Verify credentials in application configuration
3. Check that the `softeng` database exists

## Development

### Accessing the Application Files

The application code is mounted from the `app/` directory:
- Backend: `app/starbucks-ecommerce/Code/backend/`
- Frontend: `app/starbucks-ecommerce/Code/frontend/`
- Database: `app/starbucks-ecommerce/Code/database/`

Changes to these files will be reflected immediately in the running application.

### Rebuilding After Changes

```bash
docker-compose up -d --build web
```

## License

This project is for educational purposes.

