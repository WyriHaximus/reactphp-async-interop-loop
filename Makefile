all: unit
travis: travis-unit

init:
	if [ ! -d vendor ]; then composer install; fi;

unit: init
	./vendor/bin/phpunit --coverage-text --coverage-html covHtml

travis-unit: init
	./vendor/bin/phpunit --coverage-text --coverage-clover ./build/logs/clover.xml --debug

travis-coverage: init
	if [ -f ./build/logs/clover.xml ]; then wget https://scrutinizer-ci.com/ocular.phar && php ocular.phar code-coverage:upload --format=php-clover ./build/logs/clover.xml; fi
