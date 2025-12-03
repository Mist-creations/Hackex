#!/bin/bash

# HACKEX - Real-time Monitor Script
# Monitors logs and services in real-time

cd /Users/mac/Desktop/HackEx/hackex-app

echo "🔍 HACKEX Monitor - Real-time Logs"
echo "=================================="
echo ""

# Check if services are running
echo "📊 Service Status:"
WEB_SERVER=$(ps aux | grep "php.*8000" | grep -v grep | wc -l)
QUEUE_WORKER=$(ps aux | grep "queue:work" | grep -v grep | wc -l)

if [ "$WEB_SERVER" -gt 0 ]; then
    echo "✅ Web Server: RUNNING on http://localhost:8000"
else
    echo "❌ Web Server: NOT RUNNING"
fi

if [ "$QUEUE_WORKER" -gt 0 ]; then
    echo "✅ Queue Worker: RUNNING"
else
    echo "❌ Queue Worker: NOT RUNNING"
fi

echo ""
echo "📝 Watching logs (Ctrl+C to stop)..."
echo "=================================="
echo ""

# Tail logs with color highlighting
tail -f storage/logs/laravel.log | grep --line-buffered -E "(Store method called|ZIP file upload|ZIP file stored|Running static scan|Attempting to extract|ZIP open result|Deleted uploaded|ERROR|FAIL)" --color=always
