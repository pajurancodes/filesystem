<?php

namespace PajuranCodes\Filesystem;

use function is_dir;
use function implode;
use function is_array;
use function in_array;
use function is_string;
use function file_exists;

/**
 * A component for loading various files.
 *
 * @author pajurancodes
 */
class FilesLoader {

    /**
     * A list of allowed PHP expressions used to load files.
     * 
     * @var string[]
     */
    private const ALLOWED_PHP_EXPRESSIONS = [
        'include',
        'include_once',
        'require',
        'require_once',
    ];

    /**
     * Load one or more files.
     * 
     * @param string|string[] $files A filename or an array of filenames, associative or indexed.
     * @param string $phpExpression (optional) A PHP expression used to load a file. Possible 
     * values: "include", "include_once", "require", or "require_once".
     * @return static
     */
    public function loadFiles(string|array $files, string $phpExpression = 'require'): static {
        $phpExpressionToLower = strtolower($phpExpression);

        $this
            ->validateFiles($files)
            ->validatePhpExpression($phpExpressionToLower)
            ->includeFiles($files, $phpExpressionToLower)
        ;

        return $this;
    }

    /**
     * Validate a list of files.
     *
     * @param string|string[] $files A filename or an array of filenames, associative or indexed.
     * @return static
     * @throws \InvalidArgumentException The filename or the files list is empty.
     */
    private function validateFiles(string|array $files): static {
        if (
            (is_string($files) && empty($files)) ||
            (is_array($files) && !$files)
        ) {
            throw new \InvalidArgumentException('No files provided, in order to be loaded.');
        }

        return $this;
    }

    /**
     * Validate a PHP expression used to load a file.
     *
     * @param string $phpExpression A PHP expression used to load a file.
     * @return static
     * @throws \InvalidArgumentException The PHP expression is not supported.
     */
    private function validatePhpExpression(string $phpExpression): static {
        if (!in_array($phpExpression, self::ALLOWED_PHP_EXPRESSIONS, true)) {
            throw new \InvalidArgumentException(
                    'The provided PHP expression to use for loading files is '
                    . 'not valid. It must be one of the following strings: '
                    . implode(', ', self::ALLOWED_PHP_EXPRESSIONS)
                    . '.'
            );
        }

        return $this;
    }

    /**
     * Include one or more files by using a certain PHP expression.
     *
     * @param string|string[] $files A filename or an array of filenames, associative or indexed.
     * @param string $phpExpression A PHP expression used to include a file.
     * @return static
     * @throws \UnexpectedValueException One of the files is a directory.
     * @throws \UnexpectedValueException One of the files does not exist.
     */
    private function includeFiles(string|array $files, string $phpExpression): static {
        if (is_string($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if (is_dir($file)) {
                throw new \UnexpectedValueException(
                        'The filename "' . $file . '" of the '
                        . 'requested file must not be a directory name.'
                );
            }

            if (!file_exists($file)) {
                throw new \UnexpectedValueException(
                        'The requested file "' . $file . '" does not exist.'
                );
            }

            $this->includeFile($file, $phpExpression);
        }

        return $this;
    }

    /**
     * Include a file by using a certain PHP expression.
     *
     * @param string $file The filename of a file to be included.
     * @param string $phpExpression A PHP expression used to include a file.
     * @return void
     */
    private function includeFile(string $file, string $phpExpression): void {
        match ($phpExpression) {
            'include' => include $file,
            'include_once' => include_once $file,
            'require' => require $file,
            'require_once' => require_once $file,
            default => null,
        };
    }

}
