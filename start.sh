#!/bin/bash

# HACKEX - Quick Start Script
# This script starts both the web server and queue worker

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${GREEN}🚀 Starting HACKEX...${NC}"
echo ""

# Change to app directory
cd "$(dirname "$0")/hackex-app"

# Cleanup function to kill processes on exit
cleanup() {
    echo -e "\n${YELLOW}🛑 Shutting down HACKEX...${NC}"
    
    # Kill web server
    pkill -f "php.*localhost:8000" 2>/dev/null || true
    pkill -f "artisan serve" 2>/dev/null || true
    
    # Kill queue worker
    pkill -f "queue:work" 2>/dev/null || true
    
    echo -e "${GREEN}✅ Shutdown complete${NC}"
    exit 0
}

# Set trap to call cleanup on script exit
trap cleanup EXIT INT TERM

# Kill any existing processes first
echo -e "${BLUE}🧹 Cleaning up old processes...${NC}"
pkill -f "php.*localhost:8000" 2>/dev/null || true
pkill -f "artisan serve" 2>/dev/null || true
pkill -f "queue:work" 2>/dev/null || true
sleep 1

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found!"
    echo "Please run: cp .env.example .env"
    exit 1
fi

# Check if database exists
if [ ! -f database/database.sqlite ]; then
    echo "📦 Creating database..."
    touch database/database.sqlite
    php artisan migrate --force
fi

echo "✅ Configuration verified"
echo ""

# Start web server in background with explicit upload limits
echo "🌐 Starting web server on http://localhost:8000..."
echo "   📦 Upload limit: 50MB"
echo "   💾 Memory limit: 256MB"
php -d upload_max_filesize=50M \
    -d post_max_size=60M \
    -d memory_limit=256M \
    -d max_execution_time=300 \
    -d max_input_time=300 \
    -S localhost:8000 \
    -t public \
    server.php > /dev/null 2>&1 &
WEB_PID=$!

# Wait a moment for server to start
sleep 2

# Start queue worker
echo "⚙️  Starting queue worker..."
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ HACKEX is now running!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🌐 Web Interface: http://localhost:8000"
echo "📊 Queue Worker: Running (processing scans)"
echo ""
echo "Press Ctrl+C to stop both services"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Function to cleanup on exit
cleanup() {
    echo ""
    echo "🛑 Stopping HACKEX..."
    kill $WEB_PID 2>/dev/null
    # Also kill any lingering PHP servers
    pkill -f "php.*localhost:8000" 2>/dev/null
    echo "✅ Services stopped"
    exit 0
}

# Trap Ctrl+C
trap cleanup INT TERM

# Start queue worker (this will run in foreground)
php artisan queue:work --tries=3
