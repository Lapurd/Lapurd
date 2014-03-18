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

namespace Lapurd\Lapurd;

/**
 * URL paths that the system provides as fallback
 *
 * @return array
 */
function paths()
{
    return array(
        'index' => array(
            'callback' => 'sayHelloWorld',
        ),
    );
}
