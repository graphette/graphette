<?php declare(strict_types=1);
/**
 * @author Lukáš Jelič
 */

namespace Graphette\Graphette\TypeRegistry;


use Nette\Loaders\RobotLoader;
use Graphette\Graphette\Utils\Directory;

class TypeFinder {

    private string $rootPath;

    private string $tempDir;

    public function __construct(
        string  $rootPath,
        string  $tempDir,
    ) {
        $this->rootPath = $rootPath;
        $this->tempDir = $tempDir;
    }

    /**
     * @return \ReflectionClass[]
     * @throws \ReflectionException
     */
    public function findTypes(): array {
        $robot = new RobotLoader;
        $robot->setTempDirectory($this->tempDir);
        $robot->addDirectory($this->rootPath);
        $robot->acceptFiles = ['*.php'];
        $robot->reportParseErrors(false);
        $robot->refresh();
        $classes = array_unique(array_keys($robot->getIndexedClasses()));

// todo filter only classes that are needed (implement Type?)
        $found = [];
        foreach ($classes as $class) {
            $rc = new \ReflectionClass($class);

            if ($rc->isEnum()) {
                $rc = new \ReflectionEnum($class);
            }

            $found[] = $rc;
        }

        return $found;
    }

    public function getRootDirHash(): string {
        return Directory::calculateDirectoryContentHash($this->rootPath);
    }

}
