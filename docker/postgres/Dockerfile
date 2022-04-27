FROM postgres:13-alpine


# ENV POSTGRES_DB: app
#          # You should definitely change the password in production
# ENV POSTGRES_PASSWORD: ChangeMe
# ENV POSTGRES_USER: symfony

# RUN echo $POSTGRES_DB



RUN apk add --update curl && \
    rm -rf /var/cache/apk/*


RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony




    