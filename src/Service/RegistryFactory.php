<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 19:38
 */

namespace App\Service;


class RegistryFactory
{
    protected $registries;

    /**
     * RegistryFactory constructor.
     * @param AbstractPackageRegistry ...$registries
     */
    public function __construct(AbstractPackageRegistry ...$registries)
    {
        $this->registries = $registries;
    }

    /**
     * @param string $packageManagerType
     * @return AbstractPackageRegistry|null
     */
    public function get(string $packageManagerType)
    {
        foreach ($this->registries as $registry) {
            if ($registry->getPackageManagerType() === $packageManagerType) {
                return $registry;
            }
        }

        return null;
    }
}