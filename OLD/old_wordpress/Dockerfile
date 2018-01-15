FROM wordpress
#php:5.6.31

MAINTAINER Jeff Benson <jbenson14@liberty.edu>

EXPOSE 80

#RUN mkdir /var/www && \
	#mkdir /var/www/html/

WORKDIR /var/www/html

#RUN docker-php-ext-install mysql
RUN apt update

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
	chmod +x wp-cli.phar && \
	mv wp-cli.phar /usr/local/bin/wp

RUN	echo "mysql-server-5.6 mysql-server/root_password password root" | debconf-set-selections && \
	echo "mysql-server-5.6 mysql-server/root_password_again password root" | debconf-set-selections && \
	apt install -y mysql-server && \
	service mysql start

RUN	wp core download --path=/var/www/html/ --locale=en_US --version=latest --force --allow-root && \
	wp config create --path=/var/www/html/ --dbname=wordpress --dbuser=root --dbpass=example --dbhost=localhost --allow-root --force --skip-check && \
	wp core install --debug --url=localhost --title=MYAWESOMESITE --admin_user=admin --admin_password=example --admin_email=test@testing.com --allow-root

RUN wp plugin install --activate --path=/var/www/html/ advanced-custom-fields --allow-root && \
	wp plugin install --activate --path=/var/www/html/ tinymce-advanced --allow-root && \
	wp plugin install --activate --path=/var/www/html/ wordpress-seo --allow-root && \
	wp plugin list --allow-root
