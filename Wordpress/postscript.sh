#!/bin/sh
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
chmod +x wp-cli.phar && \
mv wp-cli.phar /usr/local/bin/wp && \

wp plugin install --activate advanced-custom-fields --path=/var/www/html --allow-root && \
wp plugin install --activate tinymce-advanced --path=/var/www/html --allow-root && \
wp plugin install --activate wordpress-seo --path=/var/www/html --allow-root && \

wp plugin list --allow-root
