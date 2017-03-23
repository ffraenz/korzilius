
# Korzilius 2

[![MIT license](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE.md)

Internal service used by [FF Friederes](https://friederes.lu/).

## Development

### Install

Make sure you have [Docker](https://www.docker.com/) installed locally.

Run `docker-compose up`.

Add following entry to your local `/etc/hosts` file:

```
127.0.0.1 korzilius.dev
```

Create `.env` from `.env.docker`.

Install dependencies.

```
docker exec -i korzilius-web /bin/bash -c "composer install"
```

To make webhooks reachable by external services, the local development environment needs to be exposed to the web. This can be achieved using SSH port forwarding.

```
ssh -R REMOTE_PORT:DOCKER_MACHINE_IP_HERE:80 REMOTE_HOST
```

## Deployment
