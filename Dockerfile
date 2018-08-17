FROM ulsmith/alpine-apache-php7

COPY ./ /app/public/

RUN mkdir -p /var/cache/rss-bridge/ && \
        chown -R apache:root /app/public /var/cache/rss-bridge/
