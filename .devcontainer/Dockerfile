FROM mcr.microsoft.com/devcontainers/php:3-8.3-trixie

RUN apt-get update \
	&& export DEBIAN_FRONTEND=noninteractive \
	&& apt-get install -y --no-install-recommends default-mysql-client \
	&& curl -fsSL -o /usr/local/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
	&& chmod +x /usr/local/bin/wp \
	&& apt-get clean \
	&& rm -rf /var/lib/apt/lists/*