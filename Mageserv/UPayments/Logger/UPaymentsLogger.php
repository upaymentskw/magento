<?php
/**
 * UPaymetnsLogger
 *
 * @copyright Copyright © 2023 Mageserv LTD. All rights reserved.
 * @author    mageserv.ltd@gmail.com
 */

namespace Mageserv\UPayments\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Driver\File as FileSystem;

class UPaymentsLogger extends \Monolog\Logger
{
    const DEBUG_SEVERITY = ['Info', 'Warning', 'Error'];
    const DEBUG_FILE = '/var/log/debug_upayments.log';
    const LOG_PREFIX = 'Upayments';
    private static $instance = null;

    protected $scopeConfig;
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new UPaymentsLogger();
        }
        return self::$instance;
    }
    private function __construct()
    {
        $handler = new ErrorHandler(new FileSystem(), null, self::DEBUG_FILE);
        parent::__construct('UPayments', [$handler]);
    }

    private static function isLogEnabled()
    {
        $objectManager = ObjectManager::getInstance();
        $scopeConfig = $objectManager->get(ScopeConfigInterface::class);
        return $scopeConfig->getValue(
            'payment/upayments/log_enabled'
        );
    }
    public static function ulog($msg, $severity = 1)
    {
        if(!self::isLogEnabled())
            return;
        try {
            $logger = UPaymentsLogger::getInstance();
            switch ($severity) {
                case 2:
                    $logger->warning($msg);
                    break;
                case 3:
                    $logger->error($msg);
                    break;
                default:
                    $logger->info($msg);
                    break;
            }
        } catch (\Throwable $th) {
                $severity_str = self::DEBUG_SEVERITY[$severity];
                $_prefix = date('c') . " " . self::LOG_PREFIX . "{$severity_str}: ";
                $_msg = ($_prefix . $msg . PHP_EOL);
                file_put_contents(BP . self::DEBUG_FILE, $_msg, FILE_APPEND);
        }
    }
}
