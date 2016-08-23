FROM php:5.6-cli

# Installed shared dependencies

RUN apt-get update && \
    apt-get install -y \
        build-essential \
        wget

# Install composer and its requirements

RUN apt-get install -y \
        git \
        zlib1g-dev \
        unzip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

RUN docker-php-ext-install zip

# Install XDebug (Using instructions from https://xdebug.org/wizard.php)

ENV XDEBUG_URL http://xdebug.org/files/xdebug-2.4.0.tgz

RUN cd /tmp && \
    wget ${XDEBUG_URL} && \
    tar -xvzf xdebug-2.4.0.tgz && \
    cd /tmp/xdebug-2.4.0 && \
    phpize && \
    ./configure && \
    make && \
    cp modules/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20131226

# Enable SSH

RUN apt-get install -y openssh-server

ADD authorized_keys /root/.ssh/authorized_keys

RUN mkdir /var/run/sshd && \
    chmod 700 ~/.ssh && \
    chmod 600 ~/.ssh/authorized_keys

# Install Symfony recommended packages and shortcut

RUN docker-php-ext-install opcache

#RUN apt-get install -y libicu-dev

#RUN docker-php-ext-install intl

ADD sf.sh /usr/bin/sf

RUN chmod u+x /usr/bin/sf

# Bash configuration

ADD .bashrc /root/.bashrc

# Remove installation files

RUN apt-get remove -y \
        build-essential \
        libpq-dev \
        wget && \
    apt-get -y autoremove && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /var/www/csv-objects

CMD /usr/sbin/sshd && \
    tail -f /dev/null
