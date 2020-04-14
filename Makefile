install:
	composer install
lint:
	composer run-script phpcs -- --standard=PSR12 src bin
test:
	vendor/phpunit/phpunit/phpunit tests/