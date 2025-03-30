#!/bin/bash

set -e

read -p "What is the project's name? " -r PROJECT_NAME
if [ -z "$PROJECT_NAME" ]
then
  PROJECT_NAME="djangodocker"
fi

echo "===== UPDATING PROJECT NAME ====="
git ls-files | xargs sed -i "s/djangodocker/${PROJECT_NAME}/g"
echo "Done!"

echo "===== UPDATING ENVIRONMENT ====="
cp .env.example .env
sed -i "s/djangodocker/${PROJECT_NAME}/g" ./.env

# SET DATABASE USERNAME
read -p "Enter a username for the database: " -r DATABASE_USERNAME
if [ -z "$DATABASE_USERNAME" ]
then
  DATABASE_USERNAME="admin"
fi

# SET DATABASE PASSWORD
read -p "Enter a password for the database: " -r DATABASE_PASSWORD
if [ -z "$DATABASE_PASSWORD" ]
then
  DATABASE_PASSWORD=$(tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 15)
fi

# WRITE VARIABLES TO .ENV FILE
SECRET_KEY=$(tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 50)
{
  echo "DATABASE_HOST=${PROJECT_NAME}-db"
  echo "DATABASE_NAME=${PROJECT_NAME}"
  echo "DATABASE_USERNAME=${DATABASE_USERNAME}"
  echo "DATABASE_PASSWORD=${DATABASE_PASSWORD}"
  echo "SECRET_KEY=${SECRET_KEY}"
  echo "DJANGO_SECRET_KEY=${SECRET_KEY}"
} >> .env

# RENAME PROJECT DIRECTORY
if [ "$PROJECT_NAME" != "djangodocker" ]; then
  mv djangodocker "$PROJECT_NAME"
fi

echo "===== STARTING DOCKER ====="
docker compose up -d --build

echo "===== MIGRATING DATABASE ====="
docker exec -ti "${PROJECT_NAME}-api" ./manage.py migrate

echo "===== CREATING SUPERUSER ====="
docker exec -ti "${PROJECT_NAME}-api" ./manage.py createsuperuser

echo "Success! Go to http://localhost:8000 to see API documentation."

git remote remove origin
