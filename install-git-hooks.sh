#!/usr/bin/env bash

set -euo pipefail

git config core.hooksPath .githooks
chmod +x .githooks/pre-commit

echo "Git hooks installed. core.hooksPath -> .githooks"
