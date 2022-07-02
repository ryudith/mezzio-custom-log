<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog
 */
declare(strict_types=1);

namespace Ryudith\MezzioCustomLog;


/**
 * Config provider class.
 * 
 * Library specific configuration description (mezzio_custom_log) :
 * 1. format            : Log data format, default json (json or plain).
 * 2. data_delimiter    : Log data delimiter if using plain format, default '|'.
 * 3. log_file          : Log full file name if use file and default functionality (not use 'log_storage_class').
 * 4. default_message   : Default for log message.
 * 5. notify_log        : Flag to send notification or not after log process success.
 * 6. notify_log_level  : Log level that must send a notification after success.
 * 7. log_notify_email  : List of email to send notification, grouped by log level.
 * 8. log_notify_from   : Value for 'FROM' email notification header.
 * 9. log_storage_class : Full class name for custom storage log handler (i.e. RedisLog::class).
 * 10. log_notify_class : Full class name for custom notification log handler (i.e. SlackNotification::class).
 */
class ConfigProvider
{
    public function __invoke () : array
    {
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
    }
}