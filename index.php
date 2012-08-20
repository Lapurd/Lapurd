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

if (file_exists($autoload = 'vendor/autoload.php')) {
    require($autoload);
}

require_once __DIR__ . '/bootstrap.php';

$lapurd = \Lapurd\Core::get();

$lapurd->run();
