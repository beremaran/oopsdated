<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 11:39
 */

namespace App\Tests;


use App\Service\PackagistRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PackagistRegistryTest extends WebTestCase
{
    /**
     * @var PackagistRegistry
     */
    protected $packagistRegistry;

    protected function setUp()
    {
        parent::setUp();
        self::bootKernel();

        $this->packagistRegistry = self::$container->get("app.registry.packagist.test");
    }

    public function testGetPackageByName()
    {
        $package = $this->packagistRegistry->getPackageByName("symfony/skeleton");
        $this->assertEquals("v4.2.3.2", $package->getVersion());
    }

}