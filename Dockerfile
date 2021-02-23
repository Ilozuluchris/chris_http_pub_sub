FROM php:8.0.2
RUN apt-get update -y && apt-get install -y  zip unzip
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
COPY . /app
COPY .env.example /app/.env

RUN composer install
RUN chmod +x start.sh
EXPOSE 8000

ENTRYPOINT ["./start.sh"]