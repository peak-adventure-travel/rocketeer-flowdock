# Flowdock Rocketeer Plugin

This plugin will allow Rocketeer to announce deployments to a/many flowdock event flows for before and after deploy
events have triggered, as well as any rollbacks that need to occur.

## Installation

1. In your current project directory you'll need to install the project via :
```bash
composer install liamamos-intrepid/rocketeer-flowdock --dev
```

Then you'll need to install it to Rocketeer via:
```bash
rocketeer plugin:install liamamos-intrepid/rocketeer-flowdock
```
Then in your applications `.rocketeer/config.php`, add `Rocketeer\Plugins\Flowdock\RocketeerFlowdock` to the plugin array.

## Usage
To setup the config you'll need to run the below command :

```bash
rocketeer plugin:config liamamos-intrepid/rocketeer-flowdock
```

Then go into `.rocketeer/plugins/rocketeers/rocketeer-flowdock/config.php` and configure the variables to as you wish,
you will require at least one source_token for this plugin to work.

## Testing (WIP)

```bash
$ phpunit
```