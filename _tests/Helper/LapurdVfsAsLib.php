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

class LapurdVfsAsLib extends LapurdVfs
{
    public function __construct()
    {
        parent::__construct();
        $this->lapurd = vfsStream::newDirectory('vendor/Lapurd/Lapurd')->at($this->root);
        $this->application = $this->root;
    }
}
