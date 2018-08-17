FROM ulsmith/alpine-apache-php7

COPY ./ /app/public/

RUN mkdir -p /var/cache/rss-bridge/ && \
        chown -R apache:root /app/public /var/cache/rss-bridge/ && \
        sed -i -r 's@ErrorLog .*@ErrorLog "/dev/stdout"@i' /etc/apache2/httpd.conf &&\
        echo 'TransferLog "/dev/stdout"' >> /etc/apache2/httpd.conf
