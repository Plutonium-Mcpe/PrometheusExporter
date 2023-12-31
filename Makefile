PHP_PATH := $(shell if [ ! -f "$(shell pwd)/bin/php7/bin/php" ]; then echo "$(shell which php)"; else echo "$(shell pwd)/bin/php7/bin/php"; fi;)

PHP = $(PHP_PATH) -dphar.readonly=0
# dir without /
PHP_DIR = $(dir $(PHP_PATH))
PHP_DIR := $(shell echo $(PHP_DIR) | sed 's/\/$$//g')
COMPOSER = ${shell pwd}/dev/composer.phar
PHP_CS_FIXER = ${shell pwd}/vendor/bin/php-cs-fixer

EXTENSION_DIR = $(shell find "$(shell pwd)/bin" -name "*debug-zts*" | tail -n 1)
PLUGIN_SOURCE_FILES = plugin.yml $(shell find src resources -type f) vendor
$(shell mkdir -p dev)

cs: vendor
	$(PHP) $(PHP_CS_FIXER) fix --verbose

cs/diff: vendor
	$(PHP) $(PHP_CS_FIXER) fix --dry-run --diff --ansi

php/install: Makefile
	wget https://github.com/pmmp/PHP-Binaries/releases/download/php-8.2-latest/PHP-Linux-x86_64-PM5.tar.gz
	tar -xzf PHP-Linux-x86_64-PM5.tar.gz
	rm PHP-Linux-x86_64-PM5.tar.gz
	grep -q '^extension_dir' bin/php7/bin/php.ini && sed -i'bak' "s{^extension_dir=.*{extension_dir=\"$(EXTENSION_DIR)\"{" bin/php7/bin/php.ini || echo "extension_dir=\"$(EXTENSION_DIR)\"" >> bin/php7/bin/php.ini

php/version: Makefile
	$(PHP) --version

dev/composer.phar: Makefile
	cd dev && wget -O - https://getcomposer.org/installer | $(PHP)

dev/ConsoleScript.php: Makefile
	wget -O $@ https://github.com/pmmp/DevTools/raw/stable/src/ConsoleScript.php
	touch $@

dev/PrometheusExporter.phar: $(ENGINE_SOURCE_FILES) dev/ConsoleScript.php
	$(PHP) dev/ConsoleScript.php --make plugin.yml,src,resources,vendor --out $@

phpstan: vendor
	PATH=$$PATH:$(PHP_DIR) php vendor/bin/phpstan analyse --memory-limit=2G

vendor: dev/composer.phar
	$(PHP) $(COMPOSER) install

composer/update: dev/composer.phar
	$(PHP) $(COMPOSER) update

composer/install: dev/composer.phar
	$(PHP) $(COMPOSER) install

composer/require: dev/composer.phar
	$(PHP) $(COMPOSER) require $(filter-out $@,$(MAKECMDGOALS))