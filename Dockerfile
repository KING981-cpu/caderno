FROM php:7.4-apache

# Instala extensões necessárias para o MySQL/MariaDB
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# ADICIONE ESTA LINHA AQUI:
# Resolve o aviso "Could not reliably determine the server's fully qualified domain name"
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Define o diretório de trabalho e copia os arquivos
COPY . /var/www/html/

# Dá permissão para a pasta
RUN chown -R www-data:www-data /var/www/html