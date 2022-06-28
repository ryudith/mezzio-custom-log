<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog
 */

declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Interface;

/**
 * Interface to implement for custom log notification.
 */
interface NotificationInterface
{
    /**
     * Notify about log.
     * 
     * @param array $data Data that will pass from middleware.
     * @return bool Log notification status.
     */
    public function notify (array $data) : bool;
}