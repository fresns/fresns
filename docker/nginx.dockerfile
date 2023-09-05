FROM nginx:1.25

ADD ./services/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p /var/www/html