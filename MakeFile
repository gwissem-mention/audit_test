help:
	@echo "Please use \'make <target>' where <target> is one of"
	@echo "  install           to make a Composer/bower install"
	@echo "  update            to make a Composer/bower update"
	@echo "  clean             to remove and warmup cache"
	@echo "  cldroit           to remove and warmup cache with sudo"
	@echo "  install-prod      to publish the website on production"
	@echo "  maj-prod          to pull, remove and warmup cache, publish the website on prod and update the schema of database"


install:
	composer install
	bower install
	php app/console c:cl
	php app/console a:d
	php app/console assets:install --symlink --relative

update:
	composer update
	bower update
	php app/console c:cl
	php app/console a:d
	php app/console assets:install --symlink --relative

clean:
	rm -rf app/cache/*
	rm -rf app/logs/*
	php app/console cache:clear
	php app/console a:d
	php app/console assets:install --symlink --relative

cldroit:
	rm -rf app/cache/*;
	rm -rf app/logs/*;
	php app/console cache:clear;
	sudo chmod -R 777 app/cache;
	sudo chmod -R 777 app/logs;
	php app/console a:d;
	php app/console assets:install --symlink --relative;

install-prod:
	rm -rf app/cache/*
	rm -rf app/logs/*
	php app/console cache:clear --env=prod
	php app/console a:d --env=prod
	php app/console assets:install --symlink --relative --env=prod

#Cas particulier pour maj facilement la preprod/prod HN
maj-prod:
	git pull;
	sudo rm -rf app/cache/*;
	sudo rm -rf app/logs/*;
	sudo chmod -R 777 app/cache app/logs;
	php -d memory_limit=-1 app/console c:cl --env=prod;
	sudo chmod -R 777 app/cache app/logs;
	php -d memory_limit=-1 app/console a:i --env=prod;
	sudo chmod -R 777 app/cache app/logs;
	php -d memory_limit=-1 app/console a:d --env=prod;
	sudo chmod -R 777 app/cache app/logs;
	sudo php -d memory_limit=-1 app/console d:s:u --dump-sql;
	sudo php -d memory_limit=-1 app/console d:s:u --force
