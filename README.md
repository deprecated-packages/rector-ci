# automatic-pull-request-test

Creates new pull request with test file into your current branch.

## Install
1. `composer install`
2. `cp .env.dist .env`
3. populate .env file with your [Github Personal Access Token](https://github.com/settings/tokens)

## Usage
`./script.sh $NAME` - replace $name with random string of your choice

## Next steps
1. Script (Symfony app?) to handle webhook + test 
2. Dockerize + Webserver which will receive Github hooks (traefik)
3. Run rector instead of dummy script
4. Create new [Github APP](https://github.com/settings/apps)

https://developer.github.com/v3/checks/
