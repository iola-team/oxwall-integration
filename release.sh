#!/bin/sh

composer install

cd client/
npm install
npm run release;

cd ..

rm ./release.sh