#!/bin/sh

cd $(dirname "$0")

composer install

cd client/
npm install
npm run release;

cd ..

rm -rf ./client
rm ./release.sh