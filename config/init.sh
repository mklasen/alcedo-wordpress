echo "Maybe create database $NAME on $NAME-db"
docker exec -i ${NAME}-db mysql -uroot -pmysql_password <<< "CREATE DATABASE IF NOT EXISTS ${DB_NAME};"