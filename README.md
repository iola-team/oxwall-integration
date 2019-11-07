# Oxwall Integration for iola Messenger App

## Install
- `git clone --recursive https://gitlab.com/iola-team/oxwall-integration`
- `composer install`
- `cd client && npm i && npm run build`

## Test
- [GraphIQL Feen](https://chrome.google.com/webstore/detail/graphiql-feen/mcbfdonlkfpbfdpimkjilhdneikhfklp) with Server URL `*OXWALL_URL*/iola/api/graphql`

## Release
- Download the Oxwall Integration project via zip archive from GitLab: `https://gitlab.com/iola-team/oxwall-integration/-/archive/master/oxwall-integration-master.zip`
- Unzip it: `unzip oxwall-integration-master.zip`
- Rename directory: `mv oxwall-integration-master iola`
- `cd iola/` 
- Prepare project for release: `sh release.sh`
- `cd ../`
- Make zip archive: `zip -r iola.zip iola/`
