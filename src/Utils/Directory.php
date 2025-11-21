<?php

namespace Graphette\Graphette\Utils;

class Directory {

    private static function concatenateFileContents($directoryPath): string {
        $fileContents = '';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directoryPath));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $fileContents .= file_get_contents($file->getPathname());
            }
        }

        return $fileContents;
    }

    public static function calculateDirectoryContentHash($directoryPath): string {
        $fileContents = self::concatenateFileContents($directoryPath);
        return md5($fileContents);
    }

}
