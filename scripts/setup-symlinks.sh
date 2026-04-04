#!/bin/bash

# Setup script for creating persistent symlinks for Asterisk recordings

set -e

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
STORAGE_PATH="$PROJECT_ROOT/storage/app/public"
MONITOR_LINK="$STORAGE_PATH/monitor_1"
ASTERISK_MONITOR="/var/spool/asterisk/monitor"

echo "🔗 Setting up Asterisk recording symlinks..."

# Check if Asterisk monitor directory exists
if [ ! -d "$ASTERISK_MONITOR" ]; then
    echo "❌ Asterisk monitor directory not found at: $ASTERISK_MONITOR"
    exit 1
fi

# Remove existing symlink if it exists
if [ -L "$MONITOR_LINK" ]; then
    echo "  → Removing existing symlink: $MONITOR_LINK"
    rm -f "$MONITOR_LINK"
elif [ -d "$MONITOR_LINK" ]; then
    echo "  → Removing existing directory: $MONITOR_LINK"
    rm -rf "$MONITOR_LINK"
fi

# Create new symlink
echo "  → Creating symlink: $MONITOR_LINK → $ASTERISK_MONITOR"
ln -s "$ASTERISK_MONITOR" "$MONITOR_LINK"

# Create public/storage symlink if needed
echo "  → Ensuring public storage symlink exists..."
cd "$PROJECT_ROOT" && php artisan storage:link --no-interaction 2>&1 | grep -v "INFO"

echo "✅ Symlinks setup completed successfully!"
echo ""
echo "Created:"
echo "  $MONITOR_LINK → $ASTERISK_MONITOR"
echo "  public/storage → storage/app/public"
