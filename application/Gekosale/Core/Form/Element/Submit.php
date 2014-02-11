<?php

/**
 * Gekosale, Open Source E-Commerce Solution 
 * 
 * For the full copyright and license information, 
 * please view the LICENSE file that was distributed with this source code. 
 * 
 * @category    Gekosale 
 * @package     Gekosale\Core\Form
 * @subpackage  Gekosale\Core\Form\Element
 * @author      Adam Piotrowski <adam@gekosale.com>
 * @copyright   Copyright (c) 2008-2014 Gekosale sp. z o.o. (http://www.gekosale.com)
 */
namespace Gekosale\Core\Form\Element;

use Gekosale\Core\Form\Node;

class Submit extends Node
{

    protected function prepareAttributesJavascript ()
    {
        $attributes = Array(
            $this->formatAttributeJavascript('name', 'sName'),
            $this->formatAttributeJavascript('class', 'sClass'),
            $this->formatAttributeJavascript('label', 'sLabel'),
            $this->formatAttributeJavascript('icon', 'sIcon'),
            $this->formatDependencyJavascript()
        );
        return $attributes;
    }

    public function GetValue ()
    {
        return '';
    }

    public function Populate ($value)
    {
    }
}
