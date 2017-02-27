FROM php:5.6-apache

RUN curl -sL https://deb.nodesource.com/setup_7.x | bash -
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libmcrypt-dev \
        mcrypt \
        mysql-client \
        zlib1g-dev libicu-dev \
        nodejs \
    && docker-php-ext-install -j$(nproc) iconv mcrypt mbstring exif zip opcache pdo_mysql pcntl \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get autoremove -y $BUILD_PKGS \
    && apt-get clean -y \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

#Configure xdebug.
RUN sed -i '1 a xdebug.remote_autostart=true' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN sed -i '1 a xdebug.remote_connect_back=1 ' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN sed -i '1 a xdebug.remote_enable=1' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN sed -i '1 a xdebug.max_nesting_level = 1200' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY ssh_keys/* /root/.ssh/
RUN chmod 600 /root/.ssh/*

#Configure Apache and Php
COPY vhost.conf /etc/apache2/sites-available/symfony.conf
COPY php.ini /usr/local/etc/php/
RUN a2ensite symfony
RUN a2enmod headers expires rewrite
RUN echo Europe/Paris > /etc/timezone && dpkg-reconfigure --frontend noninteractive tzdata

RUN usermod -u 1000 www-data

#Fix permissions and umask
RUN chgrp -R www-data /var/www
RUN chmod -R g+rwx /var/www
RUN umask 0007
RUN rm -rf /var/www/html/app/cache /var/www/html/app/logs && mkdir -p /var/www/html/app/cache /var/www/html/app/logs
RUN setfacl -R -m u:"www-data":rwX -m u:`whoami`:rwX /var/www/html/app/cache /var/www/html/app/logs
RUN setfacl -dR -m u:"www-data":rwX -m u:`whoami`:rwX /var/www/html/app/cache /var/www/html/app/logs

# Install project binary dependencies
RUN curl -L https://github.com/jgm/pandoc/releases/download/1.19.2.1/pandoc-1.19.2.1-1-amd64.deb > pandoc.deb && dpkg --install pandoc.deb && rm -f pandoc.deb
RUN mkdir /opt/jdk \
    && cd /opt \
    &&  curl -j -k -L -H "Cookie: oraclelicense=accept-securebackup-cookie" http://download.oracle.com/otn-pub/java/jdk/8u5-b13/jdk-8u5-linux-x64.tar.gz > jdk-8u5-linux-x64.tar.gz \
    && tar -zxf jdk-8u5-linux-x64.tar.gz -C /opt/jdk \
    && update-alternatives --install /usr/bin/java java /opt/jdk/jdk1.8.0_05/bin/java 100 \
    && update-alternatives --install /usr/bin/javac javac /opt/jdk/jdk1.8.0_05/bin/javac 100

RUN npm install -g bower

WORKDIR /var/www/html
