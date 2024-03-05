<?php

return [
    /**
     * Basic
     * 
     * 基本設定
     * 
     * enable           : bool 実行されたSQLをログに出力するかどうか
     * log_directory    : string SQLログの出力先ディレクトリ
     * slow_query_time  : int スロークエリとして扱う閾値(ms)
     * catch_transaction: bool トランザクションに関するログを出力するかどうか
     */
    "enable"            => env("LOGGING_SQL_ENABLE", false),
    "log_directory"     => env("LOGGING_SQL_LOG_DIRECTORY", "sql"),
    "slow_query_time"   => env("LOGGING_SQL_SLOW_QUERY_TIME", 1000),
    "catch_transaction" => env("LOGGING_SQL_CATCH_TRANSACTION", false),
];
