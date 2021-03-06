<?php
/*
 * Gekosale Open-Source E-Commerce Platform
 *
 * This file is part of the Gekosale package.
 *
 * (c) Adam Piotrowski <adam@gekosale.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace Gekosale\Core\Form\Elements;

/**
 * Class OptionedField
 *
 * @package Gekosale\Core\Form\Elements
 * @author  Adam Piotrowski <adam@gekosale.com>
 */
abstract class OptionedField extends Field
{

    protected function formatOptionsJs()
    {
        if (!isset($this->attributes['options']) || !is_array($this->attributes['options'])) {
            return '';
        }
        $options = Array();
        foreach ($this->attributes['options'] as $option) {
            $value     = addslashes($option->value);
            $label     = addslashes($option->label);
            $options[] = "{sValue: '{$value}', sLabel: '{$label}'}";
        }

        return 'aoOptions: [' . implode(', ', $options) . ']';
    }

}
