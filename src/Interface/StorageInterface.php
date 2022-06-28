<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog
 */

declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Interface;

/**
 * Interface to implement for custom log storage.
 */
interface StorageInterface 
{
    /**
     * Save log data to storage medium.
     * 
     * @param array $data Data that will pass from middleware.
     * @return bool Log save status.
     */
    public function save (array $data) : bool;
}