<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 16:46
 */

namespace App\Service;


use App\Entity\Package;
use Doctrine\Common\Collections\Collection;
use Psr\Log\LoggerInterface;

abstract class AbstractPackageConfigurationParser
{
    /**
     * @var string
     */
    protected $fileContents;

    /**
     * @var Package[]|Collection
     */
    protected $dependencies;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public abstract function getPackageManagerType(): string;

    /**
     * @return string
     */
    public abstract function getConfigurationFilename(): string;

    /**
     * @return string
     */
    public abstract function getConfigurationFileFormat(): string;

    /**
     * @param $fileContents string
     */
    public function parse(string $fileContents)
    {
        $fileFormat = $this->getConfigurationFileFormat();
        switch ($fileFormat) {
            case 'json':
                $fileContents = json_decode($fileContents, true);
                break;
            default:
                $this->logger->warning('Unknown configuration file format: ' . $fileFormat);
        }

        $this->fileContents = $fileContents;
        $this->dependencies = $this->extractDependencies();
    }

    /**
     * @return Package[]|Collection
     */
    protected abstract function extractDependencies();

    /**
     * @return Package[]|Collection
     */
    public function getDependencies()
    {
        if ($this->dependencies === null) {
            $this->dependencies = $this->extractDependencies();
        }

        return $this->dependencies;
    }
}