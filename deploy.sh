#!/bin/bash

SERVER_USER="bin-dev"
SERVER_HOST="192.168.1.8"
SERVER_DIR="/home/bin-dev/web/quiz.bin-dev.site/public_html"
LOCAL_PUB_DIR="public" # The directory containing the public files

npm install
npm run build

ssh $SERVER_USER@$SERVER_HOST "rm -rf $SERVER_DIR/public/*"

scp .env.production $SERVER_USER@$SERVER_HOST:$SERVER_DIR/.env
scp -r $LOCAL_PUB_DIR/* $SERVER_USER@$SERVER_HOST:$SERVER_DIR/public

ssh $SERVER_USER@$SERVER_HOST << EOF
  cd $SERVER_DIR
  git pull
  composer install
  composer deploy
EOF

echo "Deployment completed!"
