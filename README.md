# nl-tech-test

## prepare (dev):

`git clone --recursive https://github.com/AndreiV-A/nl-tech-test.git`

`cd nl-tech-test`

`composer install`

[ edit `.env` and add the database details ]

`php artisan migrate`

[ edit `.env` and add passport details ]

example: 

`PASSPORT_LOGIN_ENDPOINT=http://localhost:7001/oauth/token`

`PASSPORT_CLIENT_ID=2`

`PASSPORT_CLIENT_SECRET=VEwZiRdntkvh0tP22FeEhUQudbDIp9tsUWIhjl4m`



## run (dev):
### terminal_1:

`./run_dev.sh`

### terminal_2:

`npm ci`

`npm run watch`

### in browser, navigate to:

`http://[address]:7000`