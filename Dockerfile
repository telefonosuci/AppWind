# Dockerfile
FROM nimmis/apache-php5

MAINTAINER Suci <telefonosuci@gmail.com>

COPY 000-default.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
EXPOSE 443