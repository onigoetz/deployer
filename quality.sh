#!/usr/bin/env bash

vendor/bin/phpmd src/ text cleancode,codesize,controversial,design,naming,unusedcode

vendor/bin/phpcs --standard=PSR2 src
