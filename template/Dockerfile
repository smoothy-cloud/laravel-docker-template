#####################################
# BUILD ASSETS
#####################################

{% if variable.build_assets %}
FROM node:alpine as asset-stage
WORKDIR /app
{% if variable.package_manager == "npm" %}
COPY code-repository/package*.json /app/
RUN npm install --silent
{% elseif variable.package_manager == "yarn" %}
COPY code-repository/package*.json code-repository/yarn.* /app/
RUN yarn install --silent
{% endif %}
COPY code-repository/. /app/
{% for command in variable.build_assets_script|trim|split('\n') %}
RUN {{ command }}
{% endfor %}
{% endif %}

#####################################
# CREATE PHP CONTAINER
#####################################

{% if variable.php_version == "7.4" %}
FROM smoothy/php-7.4-fpm:1.0.0
{% endif %}

{% if variable.system_dependencies|length > 0 %}
RUN apt-get -qq update \
 && apt-get -qq install \
    {% for dependency in variable.system_dependencies|list %}
        {{ dependency }} \
    {% endfor %}
    > /dev/null \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
{% endif %}

{% if variable.run_scheduler %}
COPY run_scheduler /etc/cron.d/run_scheduler
RUN crontab /etc/cron.d/run_scheduler
{% endif %}

COPY code-repository/. /var/www/html
{% if variable.build_assets %}
COPY --from=asset-stage /app/public /var/www/html/public
{% endif %}

RUN chown -R www-data:smoothy /var/www/html \
 && chmod -R 775 storage \
 && chmod -R 775 bootstrap/cache

{% for private_composer_registry in variable.private_composer_registries|list %}
ARG PRIVATE_COMPOSER_REGISTRY_URL_{{ loop.index0 }}
ARG PRIVATE_COMPOSER_REGISTRY_USERNAME_{{ loop.index0 }}
ARG PRIVATE_COMPOSER_REGISTRY_PASSWORD_{{ loop.index0 }}
RUN composer config $PRIVATE_COMPOSER_REGISTRY_URL_{{ loop.index0 }} $PRIVATE_COMPOSER_REGISTRY_USERNAME_{{ loop.index0 }} $PRIVATE_COMPOSER_REGISTRY_PASSWORD_{{ loop.index0 }}
{% endfor %}

RUN composer install --quiet --ignore-platform-reqs --no-interaction --prefer-dist

COPY run.sh /entrypoints/run.sh
