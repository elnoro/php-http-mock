VERSION 0.6
FROM php:8.1-alpine
WORKDIR /sdk

composer: # this step is not really necessary for this use-case, as the project runs perfectly well in composer:2.4
    FROM composer:2.4
    RUN cp /usr/bin/composer composer
    SAVE ARTIFACT composer

deps:
    ENV COMPOSER_ALLOW_SUPERUSER=1
    COPY +composer/composer /usr/bin/composer
    COPY composer.json composer.lock ./ # caching dependencies - if it composer.* is not changed, no need to pull deps
    RUN composer validate --strict
    RUN composer install
    COPY --dir src tests ./ # need to copy dirs separately, see best practices https://docs.earthly.dev/best-practices
    COPY server.php *.xml ./ # copying to level files, excluding .git and other redundant files
    SAVE ARTIFACT vendor AS LOCAL vendor # saved only if you run it explicitly

cs:
    FROM +deps
    RUN composer checkcs

static:
    FROM +deps
    RUN composer stat

test:
    FROM +deps
    RUN composer cov
    SAVE ARTIFACT coverage AS LOCAL build/coverage
    SAVE ARTIFACT coverage.xml AS LOCAL build/coverage.xml

ci:
    BUILD +test
    BUILD +static
    BUILD +cs
