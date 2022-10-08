VERSION 0.6
FROM composer:2.4
WORKDIR /sdk

deps:
    COPY composer.json composer.lock ./ # caching dependencies - if it composer.* is not changed, no need to pull deps
    RUN composer install
    COPY --dir src tests ./ # need to copy dirs separately, see best practices https://docs.earthly.dev/best-practices
    COPY server.php *.xml ./ # copying to level files, excluding .git and other redundant files

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

ci:
    BUILD +test
    BUILD +static
    BUILD +cs
