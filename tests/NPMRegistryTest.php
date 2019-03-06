<?php
/**
 * Created by PhpStorm.
 * User: beremaran
 * Date: 07.03.2019
 * Time: 11:23
 */

namespace App\Tests;

use App\Service\NPMRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NPMRegistryTest extends WebTestCase
{
    /**
     * @var NPMRegistry
     */
    protected $npmRegistry;

    protected function setUp()
    {
        parent::setUp();
        self::bootKernel();

        $this->npmRegistry = self::$container->get("app.registry.npm.test");
    }

    public function testGetPackageByName()
    {
        $package = $this->npmRegistry->getPackageByName('bootstrap');
        $this->assertEquals("4.3.1", $package->getVersion());
    }
}
