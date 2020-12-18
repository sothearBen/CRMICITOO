SHELL := /bin/bash

tests:
	symfony console doctrine:fixtures:load -n -e test
	symfony php bin/phpunit
.PHONY: tests