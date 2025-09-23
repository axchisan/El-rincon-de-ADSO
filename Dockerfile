FROM php:8.2-apache

# Instala dependencias para PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql

# Copia el proyecto (opcional, ya que usas volume)
COPY . /var/www/html

# Corrige permisos: Cambia dueño a www-data para todo el sitio
RUN chown -R www-data:www-data /var/www/html

# Configura ServerName para suprimir warning (usa 'localhost' o tu dominio)
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Habilita mod_rewrite si tu app lo necesita (para URLs amigables)
RUN a2enmod rewrite