<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\TypeRegistry;


use Nette\DI\Container;
use Graphette\Graphette\Utils\Directory;


/**
 * Class TypeRegistryLoader
 * @todo very basic implementation, needs to be improved
 * @package Graphette\Graphette\TypeRegistry
 * @author Lukas Jelic
 */
class TypeRegistryLoader {

    private string $tempDir;

    private string $rootDir;

    private bool $debugMode;

    private TypeRegistryBuilderFactory $builderFactory;

    private TypeRegistry $typeRegistry;

    public function __construct(
        string                     $tempDir,
        string                     $rootDir,
        bool                       $debugMode,
        TypeRegistryBuilderFactory $builderFactory,
    ) {
        $this->tempDir = $tempDir;
        $this->rootDir = $rootDir;
        $this->debugMode = $debugMode;
        $this->builderFactory = $builderFactory;
    }

    public function load(): TypeRegistry {
        if (isset($this->typeRegistry)) {
            return $this->typeRegistry;
        }

        $filePath = $this->getFilePath();
        $lockFilePath = $filePath . '.lock';

//        if ($this->debugMode === true && file_exists($lockFilePath)) {
//            $currentDirHash = Directory::calculateDirectoryContentHash($this->rootDir);
//            $lockedDirHash = file_get_contents($lockFilePath);
//
//            if ($currentDirHash !== $lockedDirHash) {
//                // force rebuild of TypeRegistry
//                unlink($filePath);
//                unlink($lockFilePath);
//            }
//        }

        if (!file_exists($filePath)) {
            $builder = $this->builderFactory->create();
            $typeRegistryFileContent = $builder->build();
            file_put_contents($filePath, $typeRegistryFileContent);

            $dirHash = Directory::calculateDirectoryContentHash($this->rootDir);
            file_put_contents($lockFilePath, $dirHash);

        }

        include $filePath;

        $className = 'TypeRegistry';

        return $this->typeRegistry = new $className();

    }

    public function getFilePath(): string {
        return $this->tempDir . '/TypeRegistry.php';
    }
}
