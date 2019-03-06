<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 06.03.2019
 * Time: 20:12
 */

namespace App\Service;


use App\Entity\Package;

class NPMRegistry extends AbstractPackageRegistry
{
    /**
     * @param string $name
     * @return string
     */
    protected function getURIForPackage(string $name): string
    {
        return $name;
    }

    /**
     * @param array $meta
     * @return Package
     */
    protected function parsePackageMeta(array $meta): Package
    {
        $package = new Package();

        $package->setName($meta['name']);
        $package->setVersion($meta['dist-tags']['latest']);
        $package->setRegistryType($this->getPackageManagerType());

        return $package;
    }

    /**
     * @return string
     */
    public function getPackageManagerType(): string
    {
        return 'npm';
    }
}