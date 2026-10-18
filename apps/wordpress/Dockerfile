FROM wordpress:php8.3-apache
WORKDIR /var/www/html

COPY ./wp-content/themes/. /usr/src/wordpress/wp-content/themes/
COPY ./wp-content/plugins/. /usr/src/wordpress/wp-content/plugins/
COPY ./wp-content/mu-plugins/. /usr/src/wordpress/wp-content/mu-plugins/

RUN set -eux; \
	mkdir -p /usr/src/wordpress/wp-content/mu-plugins; \
	cp /usr/src/wordpress/wp-content/plugins/wp-rest-cache/sources/wp-rest-cache.php /usr/src/wordpress/wp-content/mu-plugins/wp-rest-cache.php; \
	ln -s /var/www/persistent-content/uploads /usr/src/wordpress/wp-content/uploads

# Install additional PHP extensions
# See https://make.wordpress.org/hosting/handbook/handbook/server-environment/#php-extensions
RUN set -ex; \
		apt-get update; \
		apt-get install -y --no-install-recommends libgmp-dev; \
		docker-php-ext-install -j "$(nproc)" gmp; \
		rm -rf /var/lib/apt/lists/*
# Set PHP.ini settings
# See https://secure.php.net/manual/en/opcache.installation.php
COPY custom.ini $PHP_INI_DIR/conf.d/
RUN { \
		echo 'opcache.memory_consumption=128'; \
		echo 'opcache.interned_strings_buffer=8'; \
		echo 'opcache.max_accelerated_files=4000'; \
		echo 'opcache.revalidate_freq=2'; \
	} > $PHP_INI_DIR/conf.d/opcache-recommended.ini
# https://wordpress.org/support/article/editing-wp-config-php/#configure-error-logging
RUN { \
# https://www.php.net/manual/en/errorfunc.constants.php
# https://github.com/docker-library/wordpress/issues/420#issuecomment-517839670
		echo 'error_reporting = E_ERROR | E_WARNING | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING | E_RECOVERABLE_ERROR'; \
		echo 'display_errors = Off'; \
		echo 'display_startup_errors = Off'; \
		echo 'log_errors = On'; \
		echo 'error_log = /dev/stderr'; \
		echo 'ignore_repeated_errors = On'; \
		echo 'ignore_repeated_source = Off'; \
		echo 'html_errors = Off'; \
	} > $PHP_INI_DIR/conf.d/error-logging.ini

COPY docker-entrypoint.sh /usr/local/bin/ouorz-entrypoint
ENTRYPOINT ["ouorz-entrypoint"]
CMD ["apache2-foreground"]
