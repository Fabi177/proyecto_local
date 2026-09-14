# Usamos PHP 8.2 con Apache como imagen base
FROM php:8.2-apache

# Instalamos dependencias de sistema necesarias (Git, Zip, Curl, etc.)
# Incluye curl y gnupg, necesarios para instalar Node.js
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    vim \
    gnupg \
    && rm -rf /var/lib/apt/lists/*

# Instalamos extensiones de PHP necesarias para Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

# -----------------------------------------------------
# --- INSTALACIÓN DE NODE.JS Y NPM (SOLUCIÓN AL ERROR) ---
# Agregar repositorio de NodeSource LTS (Long Term Support)
RUN curl -sL https://deb.nodesource.com/setup_lts.x | bash -

# Instalar Node.js (que incluye NPM)
RUN apt-get install -y nodejs
# -----------------------------------------------------

# Activamos mod_rewrite de Apache para el enrutamiento de Laravel
RUN a2enmod rewrite

# --- Configuración de Apache para Laravel ---
# Copiamos la configuración de Apache (que apunta a la carpeta 'public')
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
# Activamos el Virtual Host copiado
RUN a2ensite 000-default.conf
# -------------------------------------------

# Instalamos Composer (última versión)
# Usamos el constructor de Composer para copiar el ejecutable globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definimos el directorio de trabajo (donde se montará el código de Laravel)
WORKDIR /var/www/html

# Ajustar permisos para evitar problemas al ejecutar comandos (ej. 'php artisan')
RUN chown -R www-data:www-data /var/www/html

# El puerto 80 es el puerto por defecto para Apache
EXPOSE 80

# Comando por defecto para iniciar Apache en primer plano
CMD ["apache2-foreground"]