<?php

use SimpleLogger\Laravel\Facade\Logger as LoggerFacade;
use SimpleLogger\Enum\LogLevelEnum;


if (!function_exists("executeLoggingSQL")) {

    /**
     * 実行されたSQLをログに出力する
     * 
     * @param \SimpleLogger\Enum\LogLevelEnum $logLevel
     * @param string $message
     * @return void
     */
    function executeLoggingSQL(LogLevelEnum $logLevel, string $message): void
    {
        $logger = LoggerFacade::make($logLevel);

        $logger->setDirectory(config("logging-sql.log_directory", "sql"));

        $logger->add($message);

        $logger->logging();
    }
}
