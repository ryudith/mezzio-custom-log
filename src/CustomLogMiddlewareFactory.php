<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog
 */
declare(strict_types=1);

namespace Ryudith\MezzioCustomLog;

use Psr\Container\ContainerInterface;

/**
 * Factory for CustomLogMiddleware class.
 */
class CustomLogMiddlewareFactory 
{
    /**
     * Prepare and get CustomLogMiddleware instance.
     * 
     * @param ContainerInterface $container Framework container instance.
     * @return CustomLogMiddleware CustomLogMiddleware class instance.
     */
    public function __invoke (ContainerInterface $container) : CustomLogMiddleware
    {
        $logHandler = $container->get(LogHandler::class);
        return new CustomLogMiddleware($logHandler);
    }
}