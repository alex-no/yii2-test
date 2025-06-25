FROM alex0no/yii2-apache:latest

RUN docker-php-ext-install pcntl

# Specify the default working directory
WORKDIR /var/www

