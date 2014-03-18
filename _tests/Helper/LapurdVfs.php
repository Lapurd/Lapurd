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

namespace Lapurd\Test\Helper;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

/**
 * Class LapurdVfs
 *
 * @package Lapurd
 */
abstract class LapurdVfs
{
    protected $root;

    protected $lapurd;

    protected $application;

    public function __construct()
    {
        $this->root = vfsStream::setup('root');
    }

    public function setUp($testcase=null)
    {
        $this->setLapurd();

        if (is_null($testcase)) {
            $caller=debug_backtrace()[1];

            //$class = substr($caller['class'], strrpos($caller['class'], '\\') + 1);
            $testcase = $caller['function'];
        }

        vfsStream::copyFromFileSystem(
            __DIR__ . '/../testcases/' . $testcase,
            $this->root
        );
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function getLapurd()
    {
        return $this->lapurd;
    }

    public function setLapurd()
    {
        vfsStream::newFile('index.php')->setContent(
            file_get_contents(__DIR__ . '/../../index.php')
        )->at($this->lapurd);

        vfsStream::newFile('bootstrap.php')->setContent(
            file_get_contents(__DIR__ . '/../../bootstrap.php')
        )->at($this->lapurd);

        vfsStream::newFile('Lapurd.php')->setContent(
            file_get_contents(__DIR__ . '/../../Lapurd.php')
        )->at($this->lapurd);

        vfsStream::newFile('lapurd.inc.php')->setContent(
            file_get_contents(__DIR__ . '/../../lapurd.inc.php')
        )->at($this->lapurd);

//        vfsStream::copyFromFileSystem(
//            __DIR__ . '/../includes',
//            vfsStream::newDirectory('includes')
//        )->at($this->lapurd);
//
//        vfsStream::copyFromFileSystem(
//            __DIR__ . '/../library',
//            vfsStream::newDirectory('library')
//        )->at($this->lapurd);
//
//        vfsStream::copyFromFileSystem(
//            __DIR__ . '/../modules',
//            vfsStream::newDirectory('modules')
//        )->at($this->lapurd);
    }

    public function addCoreModule($name)
    {
        vfsStream::copyFromFileSystem(
            __DIR__ . '/../skeletons/modules/' . $name,
            vfsStream::newDirectory($name)
        )->at(vfsStream::newDirectory('modules')->at($this->lapurd));
    }

    public function getApplication($name=null)
    {
        if (is_null($name)) {
            return $this->application;
        }

        return $this->root->getChild('applications')->getChild($name);
    }

    public function setApplication($name)
    {
        vfsStream::copyFromFileSystem(
            __DIR__ . '/../skeletons/applications/' . $name,
            $this->application
        );
    }

    public function addApplication($name)
    {
        vfsStream::copyFromFileSystem(
            __DIR__ . '/../skeletons/applications/' . $name,
            vfsStream::newDirectory($name)
        )->at($this->root->getChild('applications'));
    }

    public function addApplicationModule($name)
    {
        vfsStream::copyFromFileSystem(
            __DIR__ . '/../skeletons/modules/' . $name,
            vfsStream::newDirectory($name)
        )->at(vfsStream::newDirectory('modules')->at($this->application));
    }

    public function showStructure($vfs=null)
    {
        if (is_null($vfs)) {
            $vfs = $this->root;
        }
        $callback = function ($path, $nodes) use (&$callback) {
            foreach ($nodes as $name => $node) {
                $file = $path . '/' . $name;
                if (is_array($node)) {
                    print $file . '/' . PHP_EOL;
                    $callback($file, $node);
                } else {
                    print $file . PHP_EOL;
                }
            }
        };

        $visitor = new vfsStreamStructureVisitor();
        $structure = $visitor->visitDirectory($vfs)->getStructure();

        print PHP_EOL;

        $callback('', $structure);
    }
}
