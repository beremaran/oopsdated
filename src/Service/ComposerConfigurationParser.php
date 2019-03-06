<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 16:59
 */

namespace App\Service;


use App\Entity\Package;
use Doctrine\Common\Collections\Collection;

class ComposerConfigurationParser extends AbstractPackageConfigurationParser
{

    /**
     * @return string
     */
    public function getPackageManagerType(): string
    {
        return 'composer';
    }

    /**
     * @return string
     */
    public function getConfigurationFilename(): string
    {
        return 'composer.json';
    }

    /**
     * @return string
     */
    public function getConfigurationFileFormat(): string
    {
        return 'json';
    }

    /**
     * @return Package[]|Collection
     */
    protected function extractDependencies()
    {
        $dependencies = [];

        foreach (["require", "require-dev"] as $depType) {
            if (!isset($this->fileContents[$depType]))
                continue;

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
}