FROM wordpress:php7.4-apache
WORKDIR /usr/src/wordpress
RUN set -eux; \
	find /etc/apache2 -name '*.conf' -type f -exec sed -ri -e "s!/var/www/html!$PWD!g" -e "s!Directory /var/www/!Directory $PWD!g" '{}' +; \
	cp -s wp-config-docker.php wp-config.php
COPY ./wp-content/themes/. ./wp-content/themes/
COPY ./wp-content/plugins/. ./wp-content/plugins/
COPY ./wp-content/uploads/. ./wp-content/uploads/
COPY ./wp-content/object-cache.php ./wp-content/object-cache.php
COPY custom.ini $PHP_INI_DIR/conf.d/
