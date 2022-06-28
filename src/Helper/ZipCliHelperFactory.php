<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Helper;

use Psr\Container\ContainerInterface;

/**
 * Factory for ZIP custom log helper.
 * 
 * Configuration key that needed for CLI helper :
 * 1. log_dir          : Default log directory location, default './data/log'.
 * 2. zip_log_filename : Default full path ZIP log file name, default './data/zip/log_'.date('Ymd').'.zip'.
 */
class ZipCliHelperFactory
{
    /**
     * Initialize and create ZipCliHelper instance.
     * 
     * @param ContainerInterface $container Container reference.
     * @return ZipCliHelper CLI helper instance.
     */
    public function __invoke (ContainerInterface $container) : ZipCliHelper
    {
        $config = $container->get('config')['mezzio_custom_log'];

        $config = array_merge_recursive([
            'log_dir' => './data/log',
            'zip_log_filename' => './data/zip/log_'.date('Ymd').'.zip',
        ], $config);

        return new ZipCliHelper($config);
    }
}