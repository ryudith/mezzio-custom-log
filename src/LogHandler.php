<?php
/**
 * @author Ryudith
 * @package Ryudith\MezzioCustomLog
 * 
 * Data key (careful with order for plain text format) :
 * 1. refId           : Log data reference.
 * 2. logLevel        : Log data level.
 * 3. logDateTimeAt   : Time log created.
 * 4. responseCode    : HTTP code from response.
 * 5. requestIP       : Request IP address.
 * 6. requestPort     : Server port.
 * 7. requestMethod   : Request method.
 * 8. requestUrl      : Full request URL.
 * 9. sessionId       : Request session ID if request use session.
 * 10. sessionData    : Request session data (can be array, object or null).
 * 11. userAgent      : Client user agent information.
 * 12. message        : String message for log.
 * 13. additionalData : Log additional data (array or object or null).
 */
declare(strict_types=1);

namespace Ryudith\MezzioCustomLog;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ryudith\MezzioCustomLog\Interface\LogHandlerInterface;
use Ryudith\MezzioCustomLog\Interface\NotificationInterface;
use Ryudith\MezzioCustomLog\Interface\StorageInterface;
use Stringable;
use Throwable;

/**
 * Real class to handle log.
 */
class LogHandler implements LogHandlerInterface
{
    public const EMERGENCY_LEVEL = 'EMERGENCY';
    public const ALERT_LEVEL = 'ALERT';
    public const CRITICAL_LEVEL = 'CRITICAL';
    public const ERROR_LEVEL = 'ERROR';
    public const WARNING_LEVEL = 'WARNING';
    public const NOTICE_LEVEL = 'NOTICE';
    public const INFO_LEVEL = 'INFO';
    public const DEBUG_LEVEL = 'DEBUG';

    /**
     * Flag for only once log per request occur.
     * 
     * @var bool $isAlreadyLogRequest
     */
    public static bool $isAlreadyLogRequest = false;

    /**
     * Singleton variable instance.
     * 
     * @var ?self $instance
     */
    private static ?self $instance = null;

    /**
     * Store all log piece data.
     * 
     * @var array $data
     */
    private array $data;

    /**
     * Flag if log process success or not.
     * 
     * @var bool $isLogSuccess
     */
    private bool $isLogSuccess;

    /**
     * Create singleton instance of LogHandler class.
     * 
     * @param array $config Assoc array config for library.
     * @param ?StorageInterface $storage Storage instance reference.
     * @param ?NotificationInterface $notification Notification instance reference.
     * @return self Class instance.
     */
    public static function create (
        array $config, 
        ?StorageInterface $storage = null, 
        ?NotificationInterface $notification = null
    ) : self
    {
        if (self::$instance === null) 
        {
            self::$instance = new LogHandler($config, $storage, $notification);
        }
        return self::$instance;
    }

    /**
     * Create class instance.
     * 
     * @param array $config Assoc array config for library.
     * @param ?StorageInterface $storage Storage instance reference.
     * @param ?NotificationInterface $notification Notification instance reference.
     * @return self Class instance.
     */
    public function __construct (
        /**
         * Assoc array config for library.
         * 
         * @var array $config
         */
        private array $config,

        /**
         * Storage instance reference.
         * 
         * @var ?StorageInterface $storage
         */
        private ?StorageInterface $storage = null,

        /**
         * Notification instance reference.
         * 
         * @var ?NotificationInterface $notification
         */
        private ?NotificationInterface $notification = null,
    ) { 
        $sessionId = session_id();
        $this->data = [
            'refId' => round(microtime(true) * 1000, 0, PHP_ROUND_HALF_DOWN),
            'logLevel' => '-',
            'logDateTimeAt' => date('Y-m-d H:i:s eP'),
            'responseCode' => '-',
            'requestIP' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-',
            'requestPort' => isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : '-',
            'requestMethod' => '-',
            'requestUrl' => '-',
            'sessionId' => $sessionId ? $sessionId : '-',
            'sessionData' => isset($_SESSION) ? rawurlencode(json_encode($_SESSION)) : null,
            'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-',
            'message' => '-',
            'additionalData' => null,
        ];
        $this->isLogSuccess = false;
    }

    /**
     * Processed logging for request. 
     * Compose data from request and response information, then write log data
     * also send notification if config 'notify_log' is true.
     * 
     * @param ServerRequestInterface $request Request instance reference.
     * @param ResponseInterface $response Response instance reference from the request
     * @param ?Throwable $e Exception reference if any.
     * @return bool Process status.
     */
    public function logRequest (ServerRequestInterface $request, ResponseInterface $response, ?Throwable $e = null) : bool
    {
        if (self::$isAlreadyLogRequest)
        {
            return $this->isLogSuccess;
        }

        $this->data['requestMethod'] = $request->getMethod();
        $this->data['requestUrl'] = (string) $request->getUri();

        $responseCode = $response->getStatusCode();
        $this->data['responseCode'] = $e === null ? $responseCode : 500;
        $message = str_replace('{responseCode}', (string) $responseCode, $this->config['default_message']);
        $logLevel = self::INFO_LEVEL;
        if ($e !== null) 
        {
            $message = $e->getMessage().' in '.$e->getFile().' on line '.$e->getLine();
            $logLevel = self::EMERGENCY_LEVEL;
        }
        else if ($responseCode >= 500) 
        {
            $logLevel = self::ALERT_LEVEL;
        }
        else if ($responseCode < 500 && $responseCode >= 400) 
        {
            $logLevel = self::ERROR_LEVEL;
        }

        $this->log($logLevel, $message);
        self::$isAlreadyLogRequest = $this->isLogSuccess;

        return $this->isLogSuccess;
    }

    /**
     * Set log data.
     * 
     * @param string $key Data key to save value.
     * @param string $value Data value to save.
     * @return void
     */
    public function setData (string $key, string $value) : void 
    {
        $this->data[$key] = $value;
    }

    /**
     * Get log data.
     * 
     * @param string $key Data key to get value.
     * @param mixed $default Default value if data not exists.
     * @return mixed Return data value or from $default.
     */
    public function getData (string $key, mixed $default = null) : mixed 
    {
        if (isset($this->data[$key])) 
        {
            return $this->data[$key];
        }

        return $default;
    }

    /**
     * Emergency log.
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function emergency (string|Stringable $message, array $context = []): void
    {
        $this->data['logLevel'] = 'EMERGENCY';
        $this->processLog($message);
    }

    /**
     * Alert log.
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function alert (string|Stringable $message, array $context = []) : void
    {
        $this->data['logLevel'] = 'ALERT';
        $this->processLog($message);
    }

    /**
     * Critical log.
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function critical (string|Stringable $message, array $context = []): void
    {
        $this->data['logLevel'] = 'CRITICAL';
        $this->processLog($message);
    }

    /**
     * Error log.
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function error (string|Stringable $message, array $context = []): void
    {
        $this->data['logLevel'] = 'ERROR';
        $this->processLog($message);
    }

    /**
     * Warning log.
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function warning (string|Stringable $message, array $context = []): void
    {
        $this->data['logLevel'] = 'WARNING';
        $this->processLog($message);
    }

    /**
     * Notice log.
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function notice (string|Stringable $message, array $context = []): void
    {
        $this->data['logLevel'] = 'NOTICE';
        $this->processLog($message);
    }

    /**
     * Info log.
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function info (string|Stringable $message, array $context = []): void
    {
        $this->data['logLevel'] = 'INFO';
        $this->processLog($message);
    }

    /**
     * Debug log.
     * 
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function debug (string|Stringable $message, array $context = []): void
    {
        $this->data['logLevel'] = 'DEBUG';
        $this->processLog($message);
    }

    /**
     * General log function to call other log function depend $level value.
     * 
     * @param $level Log level (see class constants)
     * @param string|stringable $message
     * @param array $context
     * @return void
     */
    public function log ($level, string|Stringable $message, array $context = []): void
    {
        switch ($level) {
            case self::EMERGENCY_LEVEL : 
            {
                $this->emergency($message, $context);
                break;
            }

            case self::ALERT_LEVEL :
            {
                $this->alert($message, $context);
                break;
            }

            case self::CRITICAL_LEVEL :
            {
                $this->critical($message, $context);
                break;
            }

            case self::ERROR_LEVEL :
            {
                $this->error($message, $context);
                break;
            }

            case self::WARNING_LEVEL : 
            {
                $this->warning($message, $context);
                break;
            }

            case self::NOTICE_LEVEL :
            {
                $this->notice($message, $context);
                break;
            }

            case self::INFO_LEVEL : 
            {
                $this->info($message, $context);
                break;
            }

            default:
            {
                $this->debug($message, $context);
                break;
            }
        }
    }

    /**
     * Processed logging data and notify.
     * 
     * @param string $message Log message.
     * @return void
     */
    private function processLog (string $message) : void
    {
        if ($this->save($message)) 
        {
            $this->isLogSuccess = true;
            $this->notify();
        }
    }

    /**
     * Compose log assoc array data to string.
     * 
     * @return string Log string.
     */
    private function composeMessage () : string 
    {
        if ($this->config['format'] === 'json') 
        {
            $message = json_encode($this->data);
            if ($message !== false) {
                return $message;
            }
        }

        $message = '';
        $delimiter = $this->config['data_delimiter'];
        $i = 0;
        $n = count($this->data);
        foreach ($this->data as $key => $value) {
            if ($value !== null && ! is_scalar($value)) {
                $value = json_decode($value);
            }
            $message .= '"'.$value.'"'.($i < $n ? $delimiter : '');
        }

        return $message;
    }

    /**
     * Send notification after log success, default mail function.
     * Note : 
     * php 'mail' function is not run concurrently so it will block response to user when send email.
     * Highly recommend to use 'log_notify_class' option that not block user to get response as fast as possible.
     * 
     * @return bool Notification process status.
     */
    private function notify () : bool
    {
        if (! $this->config['notify_log'] || ! in_array($this->data['logLevel'], $this->config['notify_log_level'], true)) 
        {
            return false;
        }

        if ($this->notification !== null) 
        {
            return $this->notification->notify($this->data);
        }

        $logLevel = $this->data['logLevel'];
        $emailGroup = $this->config['log_notify_email'];
        if (! isset($emailGroup[$logLevel]) || ! is_array($emailGroup[$logLevel]) || empty($emailGroup[$logLevel])) 
        {
            return false;
        }

        $to = implode(',', $emailGroup[$logLevel]);
        $subject = 'Log Notification - '.$this->data['logLevel'].'@'.$_SERVER['SERVER_NAME'].'(Response Code '.$this->data['responseCode'].')';
        $message = wordwrap("Message : \r\n".$this->data['message']."\r\n(Ref : ".$this->data['refId'].')', 70, "\r\n");
        $headers = 'From: <'.$this->config['log_notify_from'].'>'."\r\n".'Content-Type:text/plain;charset=UTF-8';
        if ($this->config['notify_log'])
        {
            return mail($to, $subject, $message, $headers);
        }

        return true;
    }

    /**
     * Save log data to storage medium, default is file.
     * 
     * @param string $message Log message.
     * @return bool Save log status.
     */
    private function save (string $message) : bool 
    {
        if ($this->storage !== null) 
        {
            return $this->storage->save($this->data);
        }

        $logdir = dirname($this->config['log_file']);
        if (! file_exists($logdir)) 
        {
            mkdir($logdir, 0755, true);
        }

        $this->data['message'] = $message;
        $strData = $this->composeMessage();
        
        return error_log($strData."\n", 3, $this->config['log_file']);
    }
}