<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 16:52
 */

namespace App\Service;


use App\Entity\Package;
use Doctrine\Common\Collections\Collection;

class NPMConfigurationParser extends AbstractPackageConfigurationParser
{
    /**
     * @return string
     */
    public function getPackageManagerType(): string
    {
        return 'npm';
    }

    /**
     * @return string
     */
    public function getConfigurationFilename(): string
    {
        return 'package.json';
    }

    /**
     * @return Package[]|Collection
     */
    protected function extractDependencies()
    {
        $dependencies = [];

        foreach (["dependencies", "devDependencies"] as $depType) {
            if (!isset($this->fileContents[$depType])) {
                continue;
            }

            $deps = $this->fileContents[$depType];

            foreach (array_keys($deps) as $dep) {
                $package = new Package();

                $package->setName($dep);
                $package->setVersion($deps[$dep]);

                $dependencies[] = $package;
            }
        }

        return $dependencies;
    }

    /**
     * @return string
     */
    public function getConfigurationFileFormat(): string
    {
        return 'json';
    }
}