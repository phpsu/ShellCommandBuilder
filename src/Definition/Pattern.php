<?php

declare(strict_types=1);

namespace PHPSu\ShellCommandBuilder\Definition;

use PHPSu\ShellCommandBuilder\Exception\ShellBuilderException;

final class Pattern
{
    // @see https://github.com/ruby/ruby/blob/master/lib/shellwords.rb#L82
    public const SHELLWORD_PATTERN = <<<REGEXP
/\G\s*(?>([^\s\\\\\'\"]+)|'([^\']*)'|"((?:[^\"\\\\]|\\\\.)*)"|(\\\\.?)|(\S))(\s|\z)?/m
REGEXP;

    // @see https://github.com/jimmycuadra/rust-shellwords/blob/master/src/lib.rs#L104
    public const METACHAR_PATTERN = /** @lang PhpRegExp */ '/\\\\([$`"\\\\\n])/';

    public const ESCAPE_PATTERN = /** @lang PhpRegExp */ '/\\\\(.)/';

    /**
     * Splitting an input into an array of shell words.
     * The pattern being used is based on a combination of the ruby and rust implementation of the same functionality
     * It derives from the original UNIX Bourne documentation
     *
     * @return array<string>
     * @throws ShellBuilderException
     */
    public static function split(string $input): array
    {
        $words = [];
        $field = '';
        $matches = [];
        preg_match_all(self::SHELLWORD_PATTERN, $input . ' ', $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL);
        /** @var array<int, null|string> $match */
        foreach ($matches as $match) {
            if ($match[5] ?? '') {
                throw new ShellBuilderException('The given input has mismatching Quotes');
            }

            $doubleQuoted = '';
            if (isset($match[3])) {
                $doubleQuoted = preg_replace(self::METACHAR_PATTERN, '$1', $match[3]);
            }

            $escaped = '';
            if (isset($match[4])) {
                $escaped = preg_replace(self::ESCAPE_PATTERN, '$1', $match[4]);
                $escaped .= '';
            }

            $field .= implode('', [$match[1], $match[2] ?? '', $doubleQuoted, $escaped]);
            $seperator = $match[6] ?? '';
            if ($seperator !== '') {
                $words[] = $field;
                $field = '';
            }
        }

        return $words;
    }
}
