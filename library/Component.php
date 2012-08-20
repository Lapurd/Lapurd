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

namespace Lapurd;

/**
 * Component class
 *
 * Defines the basic methods of all the system components.
 *
 * A component could be an application, a module or a theme, the system even
 * provides a 'Lapurd' component itself. It could implement system hooks and
 * response to requests.
 *
 * @package Lapurd
 */
abstract class Component
{
    /**
     * The name of the component
     *
     * @var string
     */
    private $name;

    /**
     * The info array of the component
     *
     * This array was retrieved from the 'info()' function defined in the
     * component's namespace. It is need to construct the component instance.
     *
     * @var array
     */
    private $info;

    /**
     * Constructor of the component
     *
     * A component must be constructed with an info array retrieved from the
     * 'info()' function defined in the component's namespace, which describes
     * the basic information about the component.
     *
     * @param array $info
     *
     * @throws \OutOfRangeException
     */
    public function __construct(array $info)
    {
        $this->name = $info['name'];
        $this->info = $info;
    }

    /**
     * Getter of class properties
     *
     * If no property is found, it will try the 'info' array.
     *
     * @param string $property
     *   The name of the property
     *
     * @return mixed
     *   The value of the property
     *
     * @throws \LogicException
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        } elseif (isset($this->info[$property])) {
            return $this->info[$property];
        } else {
            throw new \LogicException("No property can be found!");
        }
    }
}
