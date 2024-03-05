<?php

namespace LaravelLoggingSql;

use Illuminate\Support\ServiceProvider as Provider;

use SimpleLogger\Enum\LogLevelEnum;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\QueryExecuted;

use Illuminate\Support\Facades\Event;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitting;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

/**
 * ServiceProvider
 * 実行されたSQLをログに出力する処理とパッケージに含まれるファイルの公開の設定を行う
 * 
 * @package LaravelLoggingSql
 */
class ServiceProvider extends Provider
{
    /**
     * publications配下を公開する際に使うルートパス
     *
     * @var string
     */
    private string $publicationsPath = __DIR__ . DIRECTORY_SEPARATOR . "publications";


    /**
     * アプリケーションの起動時に実行する
     * SQLが実行された際にログに出力する処理を登録する
     *
     * @return void
     */
    public function register(): void
    {
        if (!config("logging-sql.enable", false)) return;

        DB::listen(static function (QueryExecuted $event) {
            $sql = $event->connection
                ->getQueryGrammar()
                ->substituteBindingsIntoRawSql(
                    sql: $event->sql,
                    bindings: $event->connection->prepareBindings($event->bindings),
                );

            $logLevel = $event->time > config("logging-sql.slow_query_time", 1000) ? LogLevelEnum::WARNING : LogLevelEnum::DEBUG;

            executeLoggingSQL($logLevel, sprintf("SQL: %s; %.2f ms", $sql, $event->time));
        });

        if (config("logging-sql.catch_transaction", false)) {
            Event::listen(static fn (TransactionBeginning $event)  => executeLoggingSQL(LogLevelEnum::DEBUG, "TRANSACTION BEGIN"));
            Event::listen(static fn (TransactionCommitting $event) => executeLoggingSQL(LogLevelEnum::DEBUG, "TRANSACTION COMMITTING"));
            Event::listen(static fn (TransactionCommitted $event)  => executeLoggingSQL(LogLevelEnum::DEBUG, "TRANSACTION COMMITTED"));
            Event::listen(static fn (TransactionRolledBack $event) => executeLoggingSQL(LogLevelEnum::WARNING, "TRANSACTION ROLLED BACK"));
        }
    }

    /**
     * アプリケーションのブート時に実行する
     * パッケージに含まれるファイルの公開の設定を行う
     * 
     * @return void
     */
    public function boot(): void
    {
        // config配下の公開
        // 自作パッケージ共通タグ
        $this->publishes([
            $this->publicationsPath . DIRECTORY_SEPARATOR . "config" => config_path(),
        ], "publications");

        // このパッケージのみ
        $this->publishes([
            $this->publicationsPath . DIRECTORY_SEPARATOR . "config" => config_path(),
        ], "logging-sql");
    }
}
