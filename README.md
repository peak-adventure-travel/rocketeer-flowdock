# Rocketeer Plugin for Flowdock

This plugin will allow Rocketeer to announce deployments to a single or multiple flowdock event flows for before and
after deploy events have triggered, as well as any rollbacks that need to occur.

## Installation

In your current project directory you'll need to install the project via :
```bash
composer install peak-adventure-travel/rocketeer-flowdock --dev
```

Then you'll need to install it to Rocketeer via:
```bash
rocketeer plugin:install peak-adventure-travel/rocketeer-flowdock
```

Then in your applications `.rocketeer/config.php`, add `Rocketeer\Plugins\Flowdock\RocketeerFlowdock`
to the plugin array.

## Usage
To setup the config you'll need to run the below command :

```bash
rocketeer plugin:config peak-adventure-travel/rocketeer-flowdock
```

Then go into `.rocketeer/plugins/rocketeers/rocketeer-flowdock/config.php` and configure the variables to as you wish,
you will require at least one source_token for this plugin to work. (See `Retrieving your Source Token` below)

## Testing

To run just the singular PHPUnit tests in tests/ type the following command :

```bash
$ vendor/bin/phing phpunit
```

## Retrieving your Source Tokens

1. Whilst in Flowdock select the flow (Channel) that you wish to add the announcements too,
2. Click the cog wheel on the flow, and select Settings,
3. On the new window, select the Integrations tab and search for "Rocketeer"
4. Click on '+ Connect Rocketeer Deployment to Flowdock',
5. On the new window, enter the name you wish to call this Integration (recommend: Rocketeer Deployment),
6. Click the 'Create source' button,
7. Store the source token that is generated and place it in the `config.php` file with an appropriate name.


