<?php
/**
 * This file is part of the Lapurd package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author     Techlive Zheng <techlivezheng@gmail.com>
 * @package    Lapurd
 */

namespace Lapurd\Test;

use Jeremeamia\SuperClosure\SerializableClosure;

/**
 * Class CoreTest
 *
 * @package Lapurd
 * @runTestsInSeparateProcesses
 */
class CoreTest extends \PHPUnit_Framework_TestCase
{
//    private static $string;
//
//    public static function setUpBeforeClass()
//    {
//        self::$string .= ob_get_clean();
//        ob_start();
//    }
//
//    public function setUp()
//    {
//        // Close the ob_start for this test first
//        ob_end_clean();
//
//
//        self::$string .= ob_get_clean();
//
//        ob_start();
//
//        ob_start();
//    }
//
//    public function tearDown()
//    {
//    }
//
//    public static function tearDownAfterClass()
//    {
//        self::$string .= ob_get_clean();
//        print self::$string;
//    }

    private function runLapurd(Helper\LapurdVfs $vfs)
    {
        require_once $vfs->getLapurd()->getChild('bootstrap.php')->url();

        $lapurd = \Lapurd\Core::get(true);

        $lapurd->run();
    }

    public function getLapurdSetups()
    {
        $providers = array();

        /**
         * Use Lapurd as Root
         * Put module inside Lapurd's modules directory
         */
        $providers[][] = new SerializableClosure(function () {
            $vfs = new Helper\LapurdVfsAsRoot();

            $vfs->setApplication('HelloWorld');

            $vfs->addCoreModule('Foo');

            $vfs->setUp('testLapurdAsRoot');

            return $vfs;
        });

        /**
         * Use Lapurd as Root
         * Put module inside Application's modules directory
         */
        $providers[][] = new SerializableClosure(function () {
            $vfs = new Helper\LapurdVfsAsRoot();

            $vfs->setApplication('HelloWorld');

            $vfs->addApplicationModule('Foo');

            $vfs->setUp('testLapurdAsRoot');

            return $vfs;
        });

        return $providers;
    }

    /**
     * @dataProvider getLapurdSetups
     */
    public function testPageIndex($provider)
    {
        unset($_REQUEST['q']);

        $vfs = call_user_func($provider);

        $this->runLapurd($vfs);

        $this->expectOutputString('Hello World!');
    }

    /**
     * @dataProvider getLapurdSetups
     */
    public function testPageNotFound($provider)
    {
        $_REQUEST['q'] = 'page/not/found';

        $vfs = call_user_func($provider);

        $this->runLapurd($vfs);

        //$headers = xdebug_get_headers();

        $this->expectOutputString('<h1>404 Page Not Found</h1>The page you requested can not be found.');
    }
}
