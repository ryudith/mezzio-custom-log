<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog\Helper
 */

declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Helper;

use Psr\Container\ContainerInterface;

/**
 * Configuration key that needed for web helper :
 * 1. helper_whitelist_ip  : Whitelist IP can access helper, default is '127.0.0.1'.
 * 2. log_dir              : Log files location directory, default is './data/log'
 * 3. zip_log_filename     : Log output ZIP file, default is './data/ziplog/log_'.date('Ymd').'.zip'
 * 4. back_days_param_name : Query parameter name for back day, default is 'backdays'.
 * 5. base_date_param_name : Query parameter name for base date or end date, default is 'basedate'.
 */
class ZipWebHelperHandlerFactory
{
    /**
     * Initialize and create ZipWebHelperHandler instance.
     * 
     * @param ContainerInterface $container Container reference.
     * @return ZipWebHelperHandler Web helper instance.
     */
    public function __invoke (ContainerInterface $container) : ZipWebHelperHandler
    {
        $config = $container->get('config')['mezzio_custom_log'];

        $config = array_merge_recursive([
            'helper_whitelist_ip' => '127.0.0.1',
            'log_dir' => './data/log',
            'zip_log_filename' => './data/zip/log_'.date('Ymd').'.zip',
            'back_days_param_name' => 'backdays',
            'base_date_param_name' => 'basedate',
        ], $config);

        return new ZipWebHelperHandler($config);
    }
}