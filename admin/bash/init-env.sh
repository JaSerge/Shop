#!/bin/bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

ROOT_DIR="$DIR/../frontend"
BACKEND_DIR="$DIR/../backend"
FRONTEND_DIR="$DIR/.."

cp "$ROOT_DIR/example.env" "$ROOT_DIR/.env"
cp "$BACKEND_DIR/example.env" "$BACKEND_DIR/.env"
cp "$FRONTEND_DIR/example.env" "$FRONTEND_DIR/.env"