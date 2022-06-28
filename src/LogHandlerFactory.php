<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog
 */
declare(strict_types=1);

namespace Ryudith\MezzioCustomLog;

use Psr\Container\ContainerInterface;

/**
 * Factory for LogHandler class.
 */
class LogHandlerFactory 
{
    /**
     * Prepare and get LogHandler instance.
     * 
     * @param ContainerInterface $container Framework container instance.
     * @return LogHandler LogHandler class instance.
     */
    public function __invoke (ContainerInterface $container) : LogHandler
    {
        $config = $container->get('config')['mezzio_custom_log'];
        
        $storage = null;
        if ($config['log_storage_class'])
        {
            $storage = $container->get($config['log_storage_class']);
        }

        $notification = null;
        if ($config['log_notify_class'])
        {
            $notification = $container->get($config['log_notify_class']);
        }

        return LogHandler::create($config, $storage, $notification);
    }
}