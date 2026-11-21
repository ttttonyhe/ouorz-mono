#!/usr/bin/env bash
set -euo pipefail

IMAGE_TAG="ouorz-wordpress"

docker build --tag="${IMAGE_TAG}" .
