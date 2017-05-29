#!/usr/bin/env bash

docker build -t netric-sdk-php-71 -f ./Dockerfile.php71 .
docker run --rm -v "$PWD":/var/www/html --name=netric-sdk-php-71 netric-sdk-php-71