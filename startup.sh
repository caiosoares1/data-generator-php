#!/bin/bash

# Espera o banco de dados ficar disponível
until nc -z -v -w30 $DB_HOST $DB_PORT
do
  echo "Aguardando o banco de dados..."
  sleep 5
done

# Executa migrações (opcional - remova se não quiser migrações automáticas)
php artisan migrate --force

# Inicia o Apache
apache2-foreground