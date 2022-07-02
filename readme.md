# **mezzio-custom-log**

**`Ryudith\MezzioCustomLog`** is middleware to auto create custom log based on request.


## **Installation**

To install run command :

```sh

$ composer require ryudith/mezzio-custom-log

```


## **Usage**

#### **Add `Ryudith\MezzioCustomLog\ConfigProvider`** to **`config/config.php`**  

```php

...

$aggregator = new ConfigAggregator([
    ...
    \Laminas\Diactoros\ConfigProvider::class,

    \Ryudith\MezzioCustomLog\ConfigProvider::class,  // <= add this line

    // Swoole config to overwrite some services (if installed)
    class_exists(\Mezzio\Swoole\ConfigProvider::class)
        ? \Mezzio\Swoole\ConfigProvider::class
        : function (): array {
            return [];
        },

    // Default App module config
    App\ConfigProvider::class,

    // Load application config in a pre-defined order in such a way that local settings
    // overwrite global settings. (Loaded as first to last):
    //   - `global.php`
    //   - `*.global.php`
    //   - `local.php`
    //   - `*.local.php`
    new PhpFileProvider(realpath(__DIR__) . '/autoload/{{,*.}global,{,*.}local}.php'),

    // Load development config if it exists
    new PhpFileProvider(realpath(__DIR__) . '/development.config.php'),
], $cacheConfig['config_cache_path']);

...

```  


#### Add **`Ryudith\MezzioCustomLog\CustomLogMiddleware`** to **`config/pipeline.php`**

```php

...

return function (Application $app, MiddlewareFactory $factory, ContainerInterface $container): void {
    // The error handler should be the first (most outer) middleware to catch
    // all Exceptions.
    $app->pipe(ErrorHandler::class);
    $app->pipe(CustomLogMiddleware::class);  // <= add this line

    ...

};
```

> You must place `$app->pipe(CustomLogMiddleware::class)` after `$app->pipe(ErrorHandler::class)` because custom log also add to exception listener so can log when exception occur.  


## **Custom Configuration**

Configuration is locate in **`vendor/ryudith/mezzio-custom-log/ConfigProvider.php`** :

```php

...

return [
    'dependencies' => [
    'factories' => [
        CustomLogMiddleware::class => CustomLogMiddlewareFactory::class,
        LogHandler::class => LogHandlerFactory::class,
    ],
],
'mezzio_custom_log' => [
    'format' => 'json',
    'data_delimiter' => '|',
    'log_file' => './data/log/log_'.date('Ymd'),
    'default_message' => 'https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/{responseCode}',
    'save_log_level' => ['ALERT', 'ERROR', 'CRITICAL', 'EMERGENCY',],

    'notify_log' => false,
    'notify_interval_time' => 3600,
    'notify_log_level' => ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR',],
    'log_notify_email' => [
        'EMERGENCY' => [
            'admin@localhost.com', 
            'dev@localhost.com',
        ],
        'ERROR' => [
            'sysadmin@localhost.com'
        ],
    ],
    'log_notify_from' => 'skynet@localhost.com',

    'log_storage_class' => null,
    'log_notify_class' => null,
],
];

...

```

Detail :

1. **`format`**  
 is format output log, `json` or `plain`.

2. **`data_delimiter`**  
 is data delimiter if use `plain` format.

3. **`log_file`**  
 is log file location directory.

3. **`default_message`**  
 is default message for log, except for exception which the message is exception message.

4. **`save_log_level`**  
 is log level that need to save.

5. **`notify_log`**  
 is flag to notify when some level log occur, default using php mail.

6. **`notify_interval_time`**  
 is interval time to send notify email log, default is 3600 or 1 hour.

7. **`notify_log_level`**  
 is which log level should send notification.

8. **`log_notify_email`**  
 is email address to send notification grouped by log level, log level control by `notify_log_level` configuration.

9. **`log_notify_from`**  
 is email address to verify notification email come from, if you using default php mail or use notification email in general.  
   > When you use email gateway service like sendinblue or sendgrid or any other service, 
   > make sure this value is your register valid email as sender to avoid block by provider.

10. **`log_storage_class`**  
 is storage class name if want to use custom storage handler.

11. **`log_notify_class`**  
 is notification class name if want to use custom notification handler.

## Enable Helper

To enable helper add the following items to `factories` configuration (usually in `config/dependencies.global.php`) : 

```php

...

'factories' => [
    
    ...
    
    // add this to enable web version helper
    Ryudith\MezzioCustomLog\Helper\ZipWebHelperHandler::class => Ryudith\MezzioCustomLog\Helper\ZipWebHelperHandlerFactory::class,

    // add this to enable cli version helper
    Ryudith\MezzioCustomLog\Helper\ZipCliHelper::class => Ryudith\MezzioCustomLog\Helper\ZipCliHelperFactory::class,

    ...
]

...

```

Then for web helper register helper to `routes.php`  
 
```php
$app->get('/customlog/zip', ZipWebHelperHandler::class);
```

> You can change `/customlog/zip` to any route path.

For web helper you can change default configuration values as listed below by add array key to `mezzio_custom_log` configuration, also **don't forget** to add your IP to whitelist to be able access helper.

1. **`helper_whitelist_ip`**  
 is string single IP address value to access helper, default is `127.0.0.1`.

2. **`log_dir`**  
 is log directory location, default is `./data/log` from your Mezzio project directory.

3. **`zip_log_filename`**  
 is log output ZIP file name, default `'./data/zip/log_'.date('Ymd').'.zip'`.

4. **`back_days_param_name`**  
 is URL query parameter name for how many days back from `base_date_param_name` parameter name value.

5. **`base_date_param_name`**  
 is URL query parameter name for end date (exclusive) to pack log files.

> ### Change default configuration web helper value  
> Create new file `config/autoload/customlog.local.php` and add :
> ```php
>
> <?php
>
> declare(strict_types=1);
>
> return [
>    'mezzio_custom_log' => [
>        'helper_whitelist_ip' => '127.0.0.1',
>        'log_dir' => './data/log',
>        'zip_log_filename' => './data/zip/log_'.date('Ymd').'.zip',
>        'back_days_param_name' => 'days',
>        'base_date_param_name' => 'date',
>    ],
> ];
>
> ```
> 
> Then you can access helper with URL **http://localhost:8080//customlog/zip?days=10&date=2022-06-12 00:00:00** with your whitelist IP for example. The URl tell to ZIP log from date `2022-06-12 00:00:00` to 10 days before or from >= `2022-06-02 00:00:00` to < `2022-06-12 00:00:00` and will fail if there is no log file.
  
  
For CLI helper you need to register helper command to `laminas-cli commands` configuration usually locate in file `config/autoload/mezzio.global.php` :

```php

...

'laminas-cli' => [
        'commands' => [
            ...
            'your:command' => Ryudith\MezzioCustomLog\Helper\ZipCliHelper::class,
            ...
        ],
    ],

...

```

> Change `your:command` to your own choice command.

> If you fail run command `composer run mezzio your:command help` for example, try use run command `vendor/bin/laminas --ansi your:command help`.

## Documentation

[API Documentation](https://github.com/ryudith/mezzio-custom-log/tree/master/docs/api/classes)

[Issues or questions](https://github.com/ryudith/mezzio-custom-log/issues)