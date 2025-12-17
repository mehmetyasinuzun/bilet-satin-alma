# Makefile for Bilet Satın Alma Platformu
# Cross-platform Docker commands

.PHONY: help build up down restart logs shell clean test health backup restore

# Default target
.DEFAULT_GOAL := help

# Variables
CONTAINER_NAME = bilet-satin-alma
COMPOSE_FILE = docker-compose.yml
DB_PATH = ./data/database.sqlite

# Colors for output
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
RESET  := $(shell tput -Txterm sgr0)

## help: Show this help message
help:
	@echo '$(GREEN)Bilet Satın Alma Platformu - Docker Commands$(RESET)'
	@echo ''
	@echo 'Usage:'
	@echo '  make $(YELLOW)<target>$(RESET)'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} { \
		if (/^[a-zA-Z_-]+:.*?##.*$$/) {printf "  $(YELLOW)%-15s$(RESET) %s\n", $$1, $$2} \
		else if (/^## .*$$/) {printf "  $(GREEN)%-15s$(RESET) %s\n", "", substr($$1,4)} \
		}' $(MAKEFILE_LIST)

## build: Build Docker image
build:
	@echo "$(GREEN)Building Docker image...$(RESET)"
	docker-compose build

## up: Start containers (with build)
up:
	@echo "$(GREEN)Starting containers...$(RESET)"
	docker-compose up -d --build
	@echo "$(GREEN)✓ Application is running at http://localhost:8080$(RESET)"

## start: Start containers (without build)
start:
	@echo "$(GREEN)Starting containers...$(RESET)"
	docker-compose up -d
	@echo "$(GREEN)✓ Application is running at http://localhost:8080$(RESET)"

## down: Stop and remove containers
down:
	@echo "$(YELLOW)Stopping containers...$(RESET)"
	docker-compose down

## restart: Restart containers
restart:
	@echo "$(YELLOW)Restarting containers...$(RESET)"
	docker-compose restart
	@echo "$(GREEN)✓ Containers restarted$(RESET)"

## stop: Stop containers (keep them)
stop:
	@echo "$(YELLOW)Stopping containers...$(RESET)"
	docker-compose stop

## logs: Show container logs (follow)
logs:
	docker-compose logs -f

## logs-tail: Show last 100 lines of logs
logs-tail:
	docker-compose logs --tail=100

## shell: Open bash shell in container
shell:
	@echo "$(GREEN)Opening shell in container...$(RESET)"
	docker exec -it $(CONTAINER_NAME) bash

## ps: Show container status
ps:
	docker-compose ps

## health: Check container health
health:
	@echo "$(GREEN)Checking container health...$(RESET)"
	@docker inspect --format='{{.State.Health.Status}}' $(CONTAINER_NAME) 2>/dev/null || echo "Container not running"

## stats: Show container resource usage
stats:
	docker stats $(CONTAINER_NAME) --no-stream

## clean: Remove containers, volumes, and images
clean:
	@echo "$(YELLOW)Cleaning up...$(RESET)"
	docker-compose down -v
	@echo "$(GREEN)✓ Cleanup complete$(RESET)"

## clean-all: Remove everything including database
clean-all: clean
	@echo "$(YELLOW)Removing database...$(RESET)"
	rm -rf $(DB_PATH)
	@echo "$(GREEN)✓ Database removed$(RESET)"

## reset-db: Reset database (delete and recreate)
reset-db:
	@echo "$(YELLOW)Resetting database...$(RESET)"
	rm -rf $(DB_PATH)
	docker-compose restart
	@echo "$(GREEN)✓ Database reset complete$(RESET)"

## backup: Backup database
backup:
	@echo "$(GREEN)Backing up database...$(RESET)"
	@mkdir -p backups
	docker exec $(CONTAINER_NAME) sqlite3 /var/www/html/data/database.sqlite .dump > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "$(GREEN)✓ Backup created in backups/$(RESET)"

## restore: Restore database from latest backup
restore:
	@echo "$(YELLOW)Restoring database...$(RESET)"
	@LATEST=$$(ls -t backups/*.sql | head -1); \
	if [ -z "$$LATEST" ]; then \
		echo "$(RED)No backup files found!$(RESET)"; \
		exit 1; \
	fi; \
	docker exec -i $(CONTAINER_NAME) sqlite3 /var/www/html/data/database.sqlite < $$LATEST
	@echo "$(GREEN)✓ Database restored$(RESET)"

## test: Run basic tests
test:
	@echo "$(GREEN)Running basic tests...$(RESET)"
	@curl -s -o /dev/null -w "HTTP Status: %{http_code}\n" http://localhost:8080 || echo "Container not running"

## open: Open application in browser
open:
	@echo "$(GREEN)Opening application...$(RESET)"
	@command -v xdg-open > /dev/null && xdg-open http://localhost:8080 || open http://localhost:8080 || start http://localhost:8080

## install: Complete installation (build and start)
install: build up
	@echo ""
	@echo "$(GREEN)╔════════════════════════════════════════════════════════╗$(RESET)"
	@echo "$(GREEN)║  ✓ Installation Complete!                             ║$(RESET)"
	@echo "$(GREEN)║                                                        ║$(RESET)"
	@echo "$(GREEN)║  URL: http://localhost:8080                           ║$(RESET)"
	@echo "$(GREEN)║                                                        ║$(RESET)"
	@echo "$(GREEN)║  Test Accounts:                                       ║$(RESET)"
	@echo "$(GREEN)║  • Admin: admin@admin.com / admin123                  ║$(RESET)"
	@echo "$(GREEN)║  • User:  user@test.com / 123456                      ║$(RESET)"
	@echo "$(GREEN)╚════════════════════════════════════════════════════════╝$(RESET)"
