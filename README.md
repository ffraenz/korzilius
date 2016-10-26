
# korzilius

This is the brain of the Korzilius app.

## Development

### Install

Make sure you have Virtual Box and [Docker Toolbox](https://www.docker.com/products/docker-toolbox) installed locally.

Run `docker-compose up`.

Look up the ip address of your docker machine:

```
docker-machine ip
```

Add following entry to your local `/etc/hosts` file:

```
DOCKER_MACHINE_IP_HERE korzilius.dev
```

Create `.env` from `.env.docker`.

Run `composer install`.

```
docker exec -i korzilius-web /bin/bash -c "composer install"
```

## Deployment
