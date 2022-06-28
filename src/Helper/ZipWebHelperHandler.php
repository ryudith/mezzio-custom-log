<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog\Helper
 */
declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Helper;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Zip log files web helper.
 */
class ZipWebHelperHandler implements RequestHandlerInterface
{
    /**
     * ZipWebHelperHandler instance constructor.
     * 
     * @param array $config 'mezzio_custom_log' configuration for web helper only.
     * @return self
     */
    public function __construct(
        /**
         * mezzio_custom_log configuration with additional specific data for web helper only.
         * 
         * @var array $config
         */
        private array $config
    ) {
        
    }

    /**
     * Handle the request.
     * 
     * @param ServerRequestInterface $request Request instance.
     * @return ResponseInterface Text response message result.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($_SERVER['REMOTE_ADDR'] !== $this->config['helper_whitelist_ip'])
        {
            return new TextResponse('Whare are you doing ?!');
        }

        $queryParams = $request->getQueryParams();
        $backDaysParamName = $this->config['back_days_param_name'];
        if (! isset($queryParams[$backDaysParamName]) || ! filter_var($queryParams[$backDaysParamName], FILTER_VALIDATE_INT))
        {
            return new TextResponse('Invalid days!');
        }

        $endTime = strtotime(date('Y-m-d 00:00:00'));
        $baseDateParamName = $this->config['base_date_param_name'];
        if (isset($queryParams[$baseDateParamName]))
        {
            $endTime = strtotime($queryParams[$baseDateParamName]);
            if ($endTime === false)
            {
                return new TextResponse('Invalid base date!');
            }
            
        }

        $days = $queryParams[$backDaysParamName];
        $startTime = strtotime('-'.$days.' day', $endTime);

        $destFileName = $this->config['zip_log_filename'];
        $srcPath = $this->config['log_dir'];
        if (Zip::packLog($destFileName, $srcPath, date('Y-m-d H:i:s', $startTime), date('Y-m-d H:i:s', $endTime)))
        {
            return new TextResponse('ZIP Log file success!');
        }
        
        return new TextResponse(Zip::$errorMessage);
    }
}