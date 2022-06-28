<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog
 */
declare(strict_types=1);

namespace Ryudith\MezzioCustomLog;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ryudith\MezzioCustomLog\Interface\LogHandlerInterface;

/**
 * Middleware for custom logging request.
 */
class CustomLogMiddleware implements MiddlewareInterface
{
    /**
     * Create CustomLogMiddleware instance.
     * 
     * @param CustomLogInterface $logHandler Log handler instance.
     * @return self CustomLogMiddleware Instance.
     */
    public function __construct (
        /**
         * Log handler instance.
         * 
         * @var CustomLogInterface $logHandler
         */
        private LogHandlerInterface $logHandler
    ) {  

    }

    /**
     * Log every response.
     * 
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handle
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        $this->logHandler->logRequest($request, $response);

        return $response;
    }
}