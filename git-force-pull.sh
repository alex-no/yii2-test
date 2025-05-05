#!/bin/bash

# Script: git-force-pull.sh
# Purpose: resets local changes and makes pull from Origin 

BRANCH=$(git rev-parse --abbrev-ref HEAD)

echo "⚠️  All local changes will be deleted!"
read -p "Continue? [y/N] " confirm

if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
    echo "Cancellation."
    exit 1
fi

git fetch origin
git reset --hard origin/$BRANCH

echo "✅ Forced Pull was made from origin/$BRANCH"