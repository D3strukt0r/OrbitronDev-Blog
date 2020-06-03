#!/bin/bash

set -x

if [[ -v DOCKER_PASSWORD && -v DOCKER_USERNAME ]]; then
    echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
fi

REPO_PHP=generation-2-blog-api-php
if [[ "$TRAVIS_BRANCH" == "develop" ]]; then
	docker build --target php --build-arg "ENV=dev" -t "$REPO_PHP" .
else
	docker build --target php --build-arg "ENV=prod" -t "$REPO_PHP" .
fi

REPO_NGINX=generation-2-blog-api-nginx
docker build --target nginx -t "$REPO_NGINX" .

if [[ -v DOCKER_PASSWORD && -v DOCKER_USERNAME ]]; then
	echo "Choosing tag to upload to... (Branch: '$TRAVIS_BRANCH' | Tag: '$TRAVIS_TAG')"
	if [[ "$TRAVIS_BRANCH" == "master" ]]; then
		DOCKER_PUSH_TAG=latest
	elif [[ "$TRAVIS_BRANCH" == "develop" ]]; then
		DOCKER_PUSH_TAG=nightly
	elif [[ "$TRAVIS_TAG" != "" ]]; then
		DOCKER_PUSH_TAG=$TRAVIS_TAG
	else
		echo "Skipping deployment because it's neither master, develop or a versioned tag"
		exit
	fi

	docker tag "$REPO_PHP" "$DOCKER_USERNAME/$REPO_PHP:$DOCKER_PUSH_TAG"
	docker push "$DOCKER_USERNAME/$REPO_PHP:$DOCKER_PUSH_TAG"

	docker tag "$REPO_NGINX" "$DOCKER_USERNAME/$REPO_NGINX:$DOCKER_PUSH_TAG"
	docker push "$DOCKER_USERNAME/$REPO_NGINX:$DOCKER_PUSH_TAG"
fi
