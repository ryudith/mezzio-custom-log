<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog
 */

declare(strict_types=1);

namespace Ryudith\MezzioCustomLog\Interface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Interface to implement for custom log request handler used by middleware.
 */
interface LogHandlerInterface extends LoggerInterface
{
    /**
     * Process log for request.
     * 
     * @param ServerRequestInterface $request Request instance reference.
     * @param ResponseInterface $response Response instance reference.
     * @param ?Throwable $e Exception instance reference.
     * @return bool Log request status.
     */
    public function logRequest (ServerRequestInterface $request, ResponseInterface $response, ?Throwable $e = null) : bool;
}