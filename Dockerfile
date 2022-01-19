FROM ubuntu:20.04
ARG user="lemon"
ARG password="lemonpass"

ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update \
	&& apt-get install -y openjdk-8-jdk \
	git pandoc scala xsltproc vim libxml2-dev \
	curl wget flex bison python3-lxml python-lxml \
	make automake autoconf libtool xz-utils dh-autoreconf gtk-doc-tools \
	apache2 net-tools mysql-server mysql-client \
	php libapache2-mod-php php-mysql php-curl php-gd php-json php-zip \
	php-dev libmcrypt-dev libc-dev pkg-config raptor2-utils

WORKDIR /lemon-model.net
COPY 4s-load.sh /lemon-model.net/
COPY build-site.sh /lemon-model.net/
COPY fw /lemon-model.net/fw
COPY htaccess /lemon-model.net/
COPY resources /lemon-model.net/resources
COPY src /lemon-model.net/src
COPY validator /lemon-model.net/validator

RUN unlink /etc/apache2/sites-enabled/000-default.conf 
COPY 000-lemon.conf /etc/apache2/sites-enabled/

ARG MARIADB_MYSQL_SOCKET_DIRECTORY='/var/run/mysqld'
RUN mkdir -p $MARIADB_MYSQL_SOCKET_DIRECTORY && \
    chown root:mysql $MARIADB_MYSQL_SOCKET_DIRECTORY && \
    chmod 774 $MARIADB_MYSQL_SOCKET_DIRECTORY

WORKDIR /lemon-model.net
RUN chmod +x ./build-site.sh
COPY mysql-init.sql .
COPY db.ini .

RUN mysqld_safe --init-file=/lemon-model.net/mysql-init.sql & (sleep 2 && mysqladmin shutdown)
RUN mysqld_safe & (sleep 2 && echo "create database lemondb" | mysql && mysqladmin shutdown)
RUN mysqld_safe & (sleep 2 && echo "show databases;" | mysql && mysqladmin shutdown)
RUN mysqld_safe & (sleep 2 && echo "select user,host from mysql.user;" | mysql && mysqladmin shutdown)
RUN mysqld_safe & (sleep 2 && echo "CREATE USER '$user'@'localhost'; SET PASSWORD FOR '$user'@'localhost' = '$password'; GRANT ALL PRIVILEGES ON lemondb.* TO 'lemon'@'localhost';" | mysql && mysqladmin shutdown)

ENV LD_LIBRARY_PATH=/usr/local/lib 
RUN mysqld_safe & (sleep 2 && ./build-site.sh pages && mysqladmin shutdown)
RUN mysqld_safe & (sleep 2 && ./build-site.sh validator && mysqladmin shutdown)
RUN mysqld_safe & (sleep 2 && ./build-site.sh pwn && mysqladmin shutdown)
RUN mysqld_safe & (sleep 2 && ./build-site.sh dbpedia_en && mysqladmin shutdown)
RUN mysqld_safe & (sleep 2 && ./build-site.sh wiktionary && mysqladmin shutdown)
RUN mysqld_safe & (sleep 2 && ./build-site.sh uby && mysqladmin shutdown)

RUN ( cd /etc/apache2/mods-enabled && ln -s ../mods-available/rewrite.load . )
ENV APACHE_CONFDIR=/etc/apache2
ENV APACHE_ENVVARS=/etc/apache2/envvars
ENV APACHE_RUN_DIR=/var/run/apache2
ENV APACHE_LOCK_DIR=/var/lock/apache

CMD mysqld_safe & apachectl -D FOREGROUND
