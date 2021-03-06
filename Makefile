SHELL := /bin/bash

tests:
	symfony console doctrine:fixtures:load -n -e test
	symfony php bin/phpunit
panther_tests:
	symfony php bin/phpunit tests/back/panther/
.PHONY: tests