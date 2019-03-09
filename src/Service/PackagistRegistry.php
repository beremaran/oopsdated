<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 06.03.2019
 * Time: 20:19
 */

namespace App\Service;


use App\Entity\Package;
use Jelix\Version\VersionComparator;

class PackagistRegistry extends AbstractPackageRegistry
{

    /**
     * @param array $meta
     * @return Package
     */
    protected function parsePackageMeta(array $meta): Package
    {
        $meta = $meta['package'];

        $package = new Package();
        $package->setName($meta['name']);

        $versions = array_keys($meta['versions']);
        $latestVersion = $this->findLatestVersion($versions);
        $package->setVersion($latestVersion);
        $package->setRegistryType($this->getPackageManagerType());

        return $package;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getURIForPackage(string $name): string
    {
        return '/packages/' . $name . '.json';
    }

    protected function findLatestVersion(array $versions): string
    {
        $versions = array_filter($versions, function ($version) {
            return strpos($version, 'dev') === false;
        });

        if (count($versions) === 0) {
            return '0.0.0';
        }

        $versions = array_map(function (string $version) {
            return ltrim($version, 'v');
        }, $versions);

        $isSorted = usort($versions, function ($a, $b) {
            return -1 * VersionComparator::compareVersion($a, $b);
        });

        if (!$isSorted) {
            // fallback to basic linear search
            $maxVersion = $versions[0];
            foreach ($versions as $version) {
                if (VersionComparator::compareVersion($version, $maxVersion) > 0) {
                    $maxVersion = $version;
                }
            }

            return $maxVersion;
        }

        return $versions[0];
    }

    /**
     * @return string
     */
    public function getPackageManagerType(): string
    {
        return 'composer';
    }
}