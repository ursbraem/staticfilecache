<?php

declare(strict_types=1);

namespace SFC\Staticfilecache\Service\InlineAssets;

/**
 * Class InlineScripts.
 *
 * @author Marcus Förster ; https://github.com/xerc
 */
class InlineScripts extends AbstractInlineAssets
{
    /**
     * Check if the class can handle the file extension.
     */
    public function canHandleExtension(string $fileExtension): bool
    {
        return 'js' === $fileExtension;
    }

    /**
     * Replace all matching Files within given HTML.
     */
    public function replaceInline(string $content): string
    {
        if (false === preg_match_all('/<script(\sasync)? src="(?<path>\/.+?)(\.\d+)?\.js(\.gzi?p?)?(\?\d*)?"[^>]*>(?=<\/script>)/', $content, $matches)) {
            return $content;
        }

        foreach ($matches['path'] as $index => $path) {
            $fileSrc = file_get_contents($this->sitePath.$path.'.js');

            if ($this->configurationService->get('inlineScriptMinify')) {
                if (false === preg_match('/\/\//', $fileSrc)) {// NO one-line comments
                    $fileSrc = preg_replace('/\v+/', '', $fileSrc); // remove line-breaks
                }
                $fileSrc = preg_replace('/\h+/', ' ', $fileSrc); // shrink whitespace

                $fileSrc = preg_replace('/\/\*.*?\*\//s', '', $fileSrc); // remove multi-line comments
                $fileSrc = preg_replace('/ *([({,;:<>=*+\-\/&?})]) */', '$1', $fileSrc); // remove no-req. spaces
                $fileSrc = preg_replace('/;(?=})|(?<=});/', '', $fileSrc); // shorten function endings
            }

            $content = str_replace($matches[0][$index], '<script>'.rtrim($fileSrc), $content);
        }

        return preg_replace('/<\/script>\s*<script>/', '', $content); // cleanup
    }
}
