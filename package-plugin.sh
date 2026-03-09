#!/usr/bin/env bash

set -euo pipefail

PLUGIN_DIR="wp-markdown-for-agents"
OUTPUT_DIR="dist"
OUTPUT_FILE="$OUTPUT_DIR/${PLUGIN_DIR}.zip"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
STAGING_DIR="$(mktemp -d)"

cleanup() {
  rm -rf "$STAGING_DIR"
}

trap cleanup EXIT

mkdir -p "$SCRIPT_DIR/$OUTPUT_DIR"
rm -f "$SCRIPT_DIR/$OUTPUT_FILE"

mkdir -p "$STAGING_DIR/$PLUGIN_DIR"

cp -R \
  "$SCRIPT_DIR/assets" \
  "$SCRIPT_DIR/includes" \
  "$SCRIPT_DIR/LICENSE" \
  "$SCRIPT_DIR/readme.txt" \
  "$SCRIPT_DIR/wp-markdown-for-agents.php" \
  "$STAGING_DIR/$PLUGIN_DIR/"

( cd "$STAGING_DIR" && zip -r "$SCRIPT_DIR/$OUTPUT_FILE" "$PLUGIN_DIR" )

echo "Created $SCRIPT_DIR/$OUTPUT_FILE"