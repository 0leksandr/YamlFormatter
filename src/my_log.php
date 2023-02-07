<?php

declare(strict_types=1);

use YamlFormatter\FormattedNamed;
use YamlFormatter\Formatter;
use YamlFormatter\Stringer\FormattedLiteral;

// add to:
// - \Monolog\Logger::log

if (!function_exists('\my_log')) { // v3.0
    define('VOID', 'VOID');
    define('DEBUG_ERRORS_LEVEL', 1);
    define(
        'IGNORED_ERRORS',
        [
            'vendor/symfony/error-handler/Debug.php:32' => [
                '^zend\\.assertions may be completely enabled or disabled only in php\\.ini$',
            ],
            'vendor/symfony/error-handler/DebugClassLoader.php:325' => [
                '^Method "([^:]+)::[^(]+\(\)" might add "[^"]+" as a native return type '
                . 'declaration in the future\\. Do the same in (?:implementation|child class) '
                . '"([^"]+)" now to avoid errors or add an explicit @return annotation to '
                . 'suppress this message\\.$',
                '^The "([^"]+)" class implements "[^"]+" that is deprecated\\.$',
            ],
            'phar:///app/vendor/phpstan/phpstan/phpstan.phar/vendor/nette/di/src/DI/ContainerLoader.php:87' => [
                '^file_get_contents\(/tmp/phpstan/cache/.*\): Failed to open stream: No such file or directory$',
            ],
            'phar:///app/vendor/phpstan/phpstan/phpstan.phar/src/Command/CommandHelper.php:185' => [
                '^mkdir\(\): File exists$',
            ],
            'vendor/dg/bypass-finals/src/BypassFinals.php:227' => [
                '^stat\(\): stat failed for /app/[^ ]+$',
            ],
        ]
    );

    function my_log_at_point(array $vars, int $point): void
    {
        $fileLine = Formatter
            ::fmtTrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[$point])
            ->asString();
        my_log_file_vars($fileLine, $vars);
    }

    function my_log_file_vars(string $fileLine, array $vars): void
    {
        $my_log_write = static function (string $string): void {
            if ($root = Formatter::getProjectRoot()) {
                $filename = $root . '/my_log.yml';
                if (!file_exists($filename)) {
                    shell_exec("touch {$filename}");
                    shell_exec("chown 1000:1000 {$filename}");

                    // shell_exec('chsh -s /bin/bash www-data');
                    // shell_exec("runuser -l www-data -c 'touch {$filename}'");
                }

                /** @var resource $fp */
                $fp = fopen($filename, 'ab');
                fwrite($fp, $string);
                fclose($fp);
            } else {
                die(__FILE__ . ':' . __LINE__ . ': cannot define project root');
            }
        };

        $my_log_write(PHP_EOL);
        foreach ($vars as $var) {
            $date = (new DateTime())
                ->setTimezone(Formatter::defaultTimezone())
                ->format('Y-m-d H:i:s.u');
            $formattedNamed = new FormattedNamed(
                1,
                "{$date} {$fileLine}",
                (new Formatter($var))->format(1),
            );
            $my_log_write($formattedNamed->asYaml() . PHP_EOL);
        }
    }

    function my_log(...$vars): void
    {
        $vars = $vars ?: [VOID];
        my_log_at_point($vars, 1);
    }

    function my_log_trace(): void
    {
        my_log_at_point(
            [
                array_map(
                    static function (array $trace): FormattedLiteral {
                        return Formatter::fmtTrace($trace);
                    },
                    debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
                ),
            ],
            1
        );
    }

    if (DEBUG_ERRORS_LEVEL >= 1) {
        $isVendor = static function (string $class): bool {
            $root = Formatter::getProjectRoot();
            /** @var Composer\Autoload\ClassLoader $loader */
            $loader = require($root . '/vendor/autoload.php');
            if ($path = $loader->getClassMap()[$class] ?? null) {
                return strpos(realpath($path), realpath($root . '/vendor/')) === 0;
            }
            return true;
        };
        $isErrorIgnored = static function (string $fileLine, string $error) use ($isVendor): bool {
            foreach (IGNORED_ERRORS[$fileLine] ?? [] as $ignoredError) {
                /** @var string $ignoredError */
                $ignoredError = str_replace('/', '\\/', $ignoredError);
                if (preg_match("/{$ignoredError}/", $error, $matches)) {
                    foreach (array_slice($matches, 1) as $class) {
                        if (!$isVendor($class)) {
                            return false;
                        }
                    }
                    return true;
                }
            }
            return false;
        };
        if (DEBUG_ERRORS_LEVEL >= 2) {
            declare(ticks=1);
            register_tick_function(static function (): void {
                global $backtrace;
                $backtrace = debug_backtrace();
            });
        }
//        ini_set('error_log', Formatter::getProjectRoot() . '/my_log.txt');
        register_shutdown_function(static function () use ($isErrorIgnored): void {
            global $backtrace;
            if ($backtrace) {
                if (DEBUG_ERRORS_LEVEL === 2) {
                    $backtrace = array_map(
                        static function (array $trace): FormattedLiteral {
                            return Formatter::fmtTrace($trace);
                        },
                        $backtrace
                    );
                }
                my_log($backtrace);
            }

            if ($error = error_get_last()) {
                $fileLine = Formatter::fmtTrace($error)->asString();
                $message = $error['message'];
                if (!$isErrorIgnored($fileLine, $message)) {
                    my_log_file_vars(
                        $fileLine,
                        [
                            new FormattedNamed(
                                1,
                                'Last error',
                                (new Formatter($message))->format(2),
                            ),
                        ],
                    );
                }
            }
        });
        set_error_handler(static function (
            int $code,
            string $description,
            string $file,
            int $line
//            array  $context
        ) use ($isErrorIgnored): bool {
            $fileLine = Formatter::fmtFileLine($file, $line)->asString();
            if (!$isErrorIgnored($fileLine, $description)) {
                my_log_file_vars(
                    $fileLine,
                    [new FormattedNamed(1, 'Error', (new Formatter($description))->format(2))],
                );
            }
            return true;
        });
        set_exception_handler(static function (Throwable $throwable): void {
            my_log($throwable);
        });

//        $exception = new \ReflectionMethod(\Exception::class, '__construct');
//        class Exception {}
    }
}
