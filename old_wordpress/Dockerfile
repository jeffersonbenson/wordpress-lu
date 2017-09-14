FROM php:5.6.31-apache

MAINTAINER Jeff Benson <jbenson14@liberty.edu>

EXPOSE 80

#RUN mkdir /var/www && \
	#mkdir /var/www/html/

WORKDIR /var/www/html

RUN docker-php-ext-install mysql

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
	chmod +x wp-cli.phar && \
	mv wp-cli.phar /usr/local/bin/wp

RUN wp core download --path=/var/www/html/ --locale=en_US --version=latest --force --allow-root && \
	wp config create --path=/var/www/html/ --dbname=mysql --dbuser=root --dbpass=example --dbhost=localhost --skip-salts --skip-check --force --allow-root && \
	wp core install --path=/var/www/html/ --url=localhost --debug --path=/var/www/html --title=MYAWESOMESITE --admin_user=admin --admin_email=test@testing.com --admin_password=admin --allow-root && \
	wp plugin install --activate --path=/var/www/html/ advanced-custom-fields --allow-root && \
	wp plugin install --activate --path=/var/www/html/ tinymce-advanced --allow-root && \
	wp plugin install --activate--path=/var/www/html/ wordpress-seo --allow-root
