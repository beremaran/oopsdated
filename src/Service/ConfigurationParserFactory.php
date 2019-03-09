<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 17:03
 */

namespace App\Service;


class ConfigurationParserFactory
{

    /**
     * @var AbstractPackageConfigurationParser[]
     */
    protected $parsers;

    public function __construct(AbstractPackageConfigurationParser ...$parsers)
    {
        $this->parsers = $parsers;
    }

    /**
     * @param string $packageManagerType
     * @return AbstractPackageConfigurationParser|null
     */
    public function get(string $packageManagerType)
    {
        foreach ($this->parsers as $parser) {
            if ($parser->getPackageManagerType() === $packageManagerType) {
                return $parser;
            }
        }

        return null;
    }

    public function getPackageManagerTypes()
    {
        return array_map(function (AbstractPackageConfigurationParser $parser) {
            return $parser->getPackageManagerType();
        }, $this->parsers);
    }
}