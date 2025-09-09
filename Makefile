# Government Procurement Dashboard - Makefile
# Commands for database management, data refresh, and daily operations

.PHONY: help install setup clean reset fresh seed test serve status backup restore daily-check

# Default help command
help: ## Show this help message
	@echo "Government Procurement Dashboard - Available Commands:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# Installation & Setup
install: ## Install dependencies and setup application
	@echo "📦 Installing dependencies..."
	composer install --no-interaction --prefer-dist --optimize-autoloader
	npm install
	@echo "✅ Dependencies installed"

setup: install ## Complete application setup
	@echo "🔧 Setting up application..."
	cp .env.example .env 2>/dev/null || true
	php artisan key:generate --no-interaction
	mkdir -p database storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
	chmod -R 775 storage bootstrap/cache
	@echo "✅ Application setup complete"

# Database Management
db-create: ## Create/recreate the database file
	@echo "🗄️  Creating database file..."
	touch database/database.sqlite
	chmod 664 database/database.sqlite
	@echo "✅ Database file created"

migrate: db-create ## Run database migrations
	@echo "🔄 Running migrations..."
	php artisan migrate --no-interaction --force
	@echo "✅ Migrations completed"

migrate-fresh: ## Fresh migration (drops all tables)
	@echo "🔥 Running fresh migrations..."
	php artisan migrate:fresh --no-interaction --force
	@echo "✅ Fresh migrations completed"

# Data Management
seed: ## Seed database with current CSV data
	@echo "🌱 Seeding database..."
	@if [ ! -f "data/data.csv" ]; then \
		echo "❌ Error: data/data.csv not found"; \
		echo "   Place your CSV file at data/data.csv before seeding"; \
		exit 1; \
	fi
	php artisan db:seed --class=ContractSeeder --no-interaction
	@echo "✅ Database seeded successfully"

seed-fresh: migrate-fresh seed ## Fresh migration + seed with new data

wipe-and-reload: ## Complete database wipe and reload with new CSV data
	@echo "🧹 Wiping database and reloading with fresh data..."
	@echo "⚠️  This will DELETE ALL existing data!"
	@read -p "Are you sure? (y/N): " confirm && [ "$$confirm" = "y" ] || exit 1
	@echo "🗑️  Dropping all data..."
	php artisan migrate:fresh --no-interaction --force
	@echo "🔄 Reloading data from CSV..."
	$(MAKE) seed
	@echo "✅ Database wiped and reloaded successfully"

# Quick data refresh (for daily updates)
refresh-data: ## Quick refresh - truncate and reseed (preserves schema)
	@echo "🔄 Refreshing data..."
	@if [ ! -f "data/data.csv" ]; then \
		echo "❌ Error: data/data.csv not found"; \
		exit 1; \
	fi
	php -r "require_once 'vendor/autoload.php'; \$$app = require_once 'bootstrap/app.php'; \$$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap(); App\Models\Contract::truncate();"
	$(MAKE) seed
	@echo "✅ Data refreshed successfully"

# Backup & Restore
backup: ## Create a timestamped backup of the database
	@echo "💾 Creating database backup..."
	@TIMESTAMP=$$(date +%Y%m%d_%H%M%S); \
	mkdir -p backups; \
	cp database/database.sqlite "backups/database_backup_$$TIMESTAMP.sqlite"; \
	echo "✅ Backup created: backups/database_backup_$$TIMESTAMP.sqlite"

restore: ## Restore from latest backup (or specify: make restore BACKUP=filename)
	@echo "🔄 Restoring database from backup..."
	@if [ -n "$(BACKUP)" ]; then \
		if [ -f "backups/$(BACKUP)" ]; then \
			cp "backups/$(BACKUP)" database/database.sqlite; \
			echo "✅ Restored from: backups/$(BACKUP)"; \
		else \
			echo "❌ Backup file not found: backups/$(BACKUP)"; \
			exit 1; \
		fi; \
	else \
		LATEST=$$(ls -t backups/database_backup_*.sqlite 2>/dev/null | head -n1); \
		if [ -n "$$LATEST" ]; then \
			cp "$$LATEST" database/database.sqlite; \
			echo "✅ Restored from latest backup: $$LATEST"; \
		else \
			echo "❌ No backup files found in backups/"; \
			exit 1; \
		fi; \
	fi

list-backups: ## List all available backups
	@echo "📋 Available backups:"
	@ls -la backups/database_backup_*.sqlite 2>/dev/null || echo "No backups found"

# Development & Testing
serve: ## Start the development server
	@echo "🚀 Starting development server..."
	php artisan serve --host=0.0.0.0 --port=8000

test: ## Run application tests
	@echo "🧪 Running tests..."
	php artisan test

format: ## Format PHP code with Laravel Pint
	@echo "✨ Formatting code..."
	vendor/bin/pint --dirty

# Monitoring & Status
status: ## Show application status and statistics
	@echo "📊 Application Status:"
	@echo "===================="
	@echo "Database file: $$([ -f database/database.sqlite ] && echo '✅ Exists' || echo '❌ Missing')"
	@echo "CSV data file: $$([ -f data/data.csv ] && echo '✅ Exists' || echo '❌ Missing')"
	@if [ -f database/database.sqlite ]; then \
		echo "Database size: $$(du -h database/database.sqlite | cut -f1)"; \
		echo ""; \
		echo "📈 Data Statistics:"; \
		php -r " \
			require_once 'vendor/autoload.php'; \
			\$$app = require_once 'bootstrap/app.php'; \
			\$$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap(); \
			\$$count = App\Models\Contract::count(); \
			\$$total = App\Models\Contract::sum('total_contract_value'); \
			\$$vendors = App\Models\Contract::distinct('vendor_name')->count(); \
			echo 'Total Contracts: ' . number_format(\$$count) . PHP_EOL; \
			echo 'Total Value: $' . number_format(\$$total, 2) . PHP_EOL; \
			echo 'Unique Vendors: ' . number_format(\$$vendors) . PHP_EOL; \
		" 2>/dev/null || echo "❌ Unable to fetch statistics"; \
	fi

csv-info: ## Show information about the CSV file
	@echo "📄 CSV File Information:"
	@echo "======================="
	@if [ -f "data/data.csv" ]; then \
		echo "File size: $$(du -h data/data.csv | cut -f1)"; \
		echo "Line count: $$(wc -l < data/data.csv) lines"; \
		echo "Header row:"; \
		head -n1 data/data.csv | cut -c1-100; \
		echo "..."; \
	else \
		echo "❌ CSV file not found at data/data.csv"; \
	fi

# Daily Operations
daily-check: ## Run daily health checks
	@echo "🏥 Daily Health Check"
	@echo "===================="
	$(MAKE) status
	@echo ""
	@echo "🔍 System Health:"
	@echo "PHP Version: $$(php -r 'echo PHP_VERSION;')"
	@echo "Laravel Version: $$(php artisan --version)"
	@echo "Disk usage: $$(df -h . | tail -1 | awk '{print $$5}')"
	@echo ""
	@echo "🕐 Last data refresh: $$(stat -c %y database/database.sqlite 2>/dev/null | cut -d. -f1 || echo 'Unknown')"

daily-backup: backup ## Create daily backup (alias for backup)

daily-refresh: ## Daily data refresh routine (backup + refresh)
	@echo "🌅 Running daily refresh routine..."
	$(MAKE) backup
	$(MAKE) refresh-data
	@echo "✅ Daily refresh completed"

# Cleanup
clean: ## Clean temporary files and caches
	@echo "🧹 Cleaning temporary files..."
	php artisan cache:clear --no-interaction
	php artisan config:clear --no-interaction
	php artisan view:clear --no-interaction
	rm -rf storage/logs/*.log
	@echo "✅ Cleanup completed"

reset: clean migrate-fresh seed ## Complete reset (clean + fresh migration + seed)

# Emergency Commands
emergency-restore: ## Emergency restore from latest backup without confirmation
	@echo "🚨 EMERGENCY RESTORE - Restoring from latest backup..."
	@LATEST=$$(ls -t backups/database_backup_*.sqlite 2>/dev/null | head -n1); \
	if [ -n "$$LATEST" ]; then \
		cp "$$LATEST" database/database.sqlite; \
		echo "✅ Emergency restore completed from: $$LATEST"; \
	else \
		echo "❌ No backup files found - cannot restore"; \
		exit 1; \
	fi

# Validation
validate-csv: ## Validate CSV file format and content
	@echo "✅ Validating CSV file..."
	@if [ ! -f "data/data.csv" ]; then \
		echo "❌ CSV file not found at data/data.csv"; \
		exit 1; \
	fi
	@echo "📊 CSV Statistics:"
	@echo "Lines: $$(wc -l < data/data.csv)"
	@echo "Columns: $$(head -n1 data/data.csv | tr ',' '\n' | wc -l)"
	@echo "File size: $$(du -h data/data.csv | cut -f1)"
	@echo "✅ CSV file appears valid"

# Information
info: ## Show detailed application information
	@echo "ℹ️  Government Procurement Dashboard"
	@echo "==================================="
	@echo "Purpose: Transparent government spending analysis"
	@echo "Data source: data/data.csv"
	@echo "Database: SQLite (database/database.sqlite)"
	@echo "Framework: Laravel $$(php artisan --version | cut -d' ' -f3)"
	@echo ""
	@echo "📁 Project structure:"
	@echo "├── data/data.csv           # Source CSV data"
	@echo "├── database/database.sqlite # SQLite database"
	@echo "├── backups/                # Database backups"
	@echo "└── Makefile               # This command file"
	@echo ""
	@echo "🔧 Most common commands:"
	@echo "make wipe-and-reload       # Replace all data with new CSV"
	@echo "make refresh-data          # Quick daily data update"
	@echo "make daily-refresh         # Backup + refresh routine"
	@echo "make status               # Check current status"