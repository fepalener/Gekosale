<?php

/**
 * Gekosale, Open Source E-Commerce Solution 
 * 
 * For the full copyright and license information, 
 * please view the LICENSE file that was distributed with this source code. 
 * 
 * @category    Gekosale 
 * @package     Gekosale\Core\Form 
 * @author      Adam Piotrowski <adam@gekosale.com>
 * @copyright   Copyright (c) 2008-2014 Gekosale sp. z o.o. (http://www.gekosale.com)
 */
namespace Gekosale\Core\Form;

abstract class Node
{

    public $form;

    public $container;

    public $parent;

    protected $_id;

    protected $_attributes;

    protected $_renderMode;

    protected $_tabs;

    protected $_jsNodeName;

    protected $_xajaxMethods;

    protected static $_nextId = 0;

    public function __construct ($attributes)
    {
        $this->_id = self::$_nextId ++;
        $this->_attributes = $attributes;
        $this->_renderMode = 'Static';
        $this->_tabs = '';
        $classPath = explode('\\', get_class($this));
        $this->_jsNodeName = 'GForm' . end($classPath);
        $this->form = null;
        $this->parent = null;
        $this->_xajaxMethods = Array();
    }

    public function render ($mode = 'JS', $tabs = '')
    {
        $this->_tabs = $tabs;
        $this->_renderMode = $mode;
        $renderFunction = 'render_' . $mode;
        $lines = explode("\n", $this->$renderFunction());
        foreach ($lines as &$line) {
            $line = $this->_tabs . $line;
        }
        
        return implode("\n", $lines);
    }

    public function addRule ($rule)
    {
        if (! isset($this->_attributes['rules']) || ! is_array($this->_attributes['rules'])) {
            $this->_attributes['rules'] = Array();
        }
        $this->_attributes['rules'][] = $rule;
    }

    public function clearRules ()
    {
        $this->_attributes['rules'] = Array();
    }

    public function addFilter ($filter)
    {
        if (! isset($this->_attributes['filters']) || ! is_array($this->_attributes['filters'])) {
            $this->_attributes['filters'] = Array();
        }
        $this->_attributes['filters'][] = $filter;
    }

    public function setFilter ($filter)
    {
        if (! isset($this->_attributes['filters']) || ! is_array($this->_attributes['filters'])) {
            $this->_attributes['filters'] = Array();
        }
        $this->_attributes['filters'][] = $filter;
    }

    public function clearFilters ()
    {
        $this->_attributes['filters'] = Array();
    }

    public function addDependency ($dependency)
    {
        if (! isset($this->_attributes['dependencies']) || ! is_array($this->_attributes['dependencies'])) {
            $this->_attributes['dependencies'] = Array();
        }
        $this->_attributes['dependencies'][] = $dependency;
    }

    protected function filter ($values)
    {
        if (! isset($this->_attributes['filters']) || ! is_array($this->_attributes['filters'])) {
            return $values;
        }
        if (is_array($values)) {
            foreach ($values as &$value) {
                foreach ($this->_attributes['filters'] as $filter) {
                    $value = $filter->Filter($value);
                }
            }
        }
        else {
            foreach ($this->_attributes['filters'] as $filter) {
                $values = $filter->Filter($values);
            }
        }
        
        return $values;
    }

    public function getName ()
    {
        return $this->_attributes['name'];
    }

    protected function harvestValues ($node, $levels)
    {
        $value = $node->GetValue();
        foreach ($levels as $level) {
            if (isset($value[$level])) {
                $value = $value[$level];
            }
            else {
                return '';
            }
        }
        
        return $value;
    }

    protected function harvestErrors ($node, $levels)
    {
        if (! isset($node->_attributes['error'])) {
            return '';
        }
        $value = $node->_attributes['error'];
        foreach ($levels as $level) {
            if (isset($value[$level])) {
                $value = $value[$level];
            }
            else {
                return '';
            }
        }
        
        return $value;
    }

    protected function harvest ($action, $levelsCount = 0, $levels = Array())
    {
        if (isset($this->_children)) {
            $array = Array();
            foreach ($this->_children as $child) {
                $name = $child->getName();
                if (empty($name)) {
                    continue;
                }
                if (get_class($this) == 'FormEngine\Elements\FieldsetRepeatable') {
                    $repetitions = $child->harvestRepetitions($levelsCount);
                    foreach ($repetitions as $repetition) {
                        $levelsCopy = $levels + Array(
                            $repetition
                        );
                        $array[$repetition][$name] = $child->harvest($action, $levelsCount + 1, $levelsCopy);
                    }
                }
                else {
                    $array[$name] = $child->harvest($action, $levelsCount, $levels);
                }
            }
            
            return $array;
        }
        else {
            if (is_array($action)) {
                return call_user_func($action, $this, $levels);
            }
            
            return $action($this, $levels);
        }
    }

    protected function harvestRepetitions ($level = 0)
    {
        if (isset($this->_children)) {
            $array = Array();
            foreach ($this->_children as $child) {
                array_push($array, $child->harvestRepetitions($level));
            }
            
            return array_unique($array);
        }
        else {
            $value = $this->GetValue();
            $repetitions = $this->extractRepetitions($value, $level);
            
            return array_unique($repetitions);
        }
    }

    protected function extractRepetitions ($array, $targetLevel, $level = 0)
    {
        if ($targetLevel >= $level) {
            if (is_array($array)) {
                return array_keys($array);
            }
            
            return Array();
        }
        $repetitions = Array();
        foreach ($array as $key => $value) {
            array_push($repetitions, $this->extractRepetitions($value, $targetLevel, $level + 1));
        }
        
        return $repetitions;
    }

    protected function formatAttributesJavascript ($attributes)
    {
        $attributes = array_merge($attributes, $this->prepareAutoAttributesJavascript());
        $attributesString = "\n";
        foreach ($attributes as $attribute) {
            if (! empty($attribute)) {
                $attributesString .= $this->_tabs . $attribute . ",\n";
            }
        }
        
        return substr($attributesString, 0, - 2) . "\n";
    }

    protected function formatAttributeJavascript ($attributeName, $name = null, $type = FE::TYPE_STRING)
    {
        if ($name == null) {
            if (! isset($this->_attributes[$attributeName])) {
                if ($type == FE::TYPE_FUNCTION) {
                    return 'null';
                }
                elseif ($type == FE::TYPE_NUMBER) {
                    return '0';
                }
                elseif ($type == FE::TYPE_ARRAY) {
                    return '[]';
                }
                elseif ($type == FE::TYPE_OBJECT) {
                    return '{}';
                }
                elseif ($type == FE::TYPE_BOOLEAN) {
                    return 'false';
                }
                
                return '\'\'';
            }
            if ($type == FE::TYPE_FUNCTION) {
                return $this->_attributes[$attributeName];
            }
            elseif ($type == FE::TYPE_NUMBER) {
                return $this->_attributes[$attributeName];
            }
            elseif ($type == FE::TYPE_ARRAY) {
                return json_encode($this->_attributes[$attributeName]);
            }
            elseif ($type == FE::TYPE_OBJECT) {
                return json_encode($this->_attributes[$attributeName]);
            }
            elseif ($type == FE::TYPE_BOOLEAN) {
                return $this->_attributes[$attributeName] ? 'true' : 'false';
            }
            
            return str_replace(Array(
                "\r\n",
                "\n"
            ), '\n', '\'' . addslashes($this->_attributes[$attributeName]) . '\'');
        }
        if (! isset($this->_attributes[$attributeName])) {
            return '';
        }
        $value = $this->_attributes[$attributeName];
        if ($type == FE::TYPE_ARRAY) {
            return $name . ': ' . json_encode($value);
        }
        elseif ($type == FE::TYPE_OBJECT) {
            return $name . ': ' . json_encode($value);
        }
        elseif (is_array($value)) {
            foreach ($value as &$valuePart) {
                if ($type == FE::TYPE_FUNCTION) {
                    $valuePart = '' . ($valuePart) . '';
                }
                elseif ($type == FE::TYPE_NUMBER) {
                    $valuePart = '' . ($valuePart) . '';
                }
                else {
                    $valuePart = '\'' . addslashes($valuePart) . '\'';
                }
            }
            
            return str_replace("\n", '\n', $name . ': [' . implode(', ', $value) . ']');
        }
        else {
            if ($type == FE::TYPE_FUNCTION) {
                return $name . ': ' . ($value) . '';
            }
            elseif ($type == FE::TYPE_NUMBER) {
                return $name . ': ' . ($value) . '';
            }
            elseif ($type == FE::TYPE_BOOLEAN) {
                return $name . ': ' . ($value ? 'true' : 'false') . '';
            }
            else {
                return str_replace(Array(
                    "\r\n",
                    "\n"
                ), '\n', $name . ': \'' . addslashes($value) . '\'');
            }
        }
    }

    protected function formatRepeatableJavascript ()
    {
        if ((isset($this->_attributes['repeat_min']) && ($this->_attributes['repeat_min'] != 1)) || (isset($this->_attributes['repeat_max']) && ($this->_attributes['repeat_max'] != 1))) {
            $min = (isset($this->_attributes['repeat_min']) && is_numeric($this->_attributes['repeat_min'])) ? $this->_attributes['repeat_min'] : 1;
            $max = (isset($this->_attributes['repeat_max']) && is_numeric($this->_attributes['repeat_max'])) ? $this->_attributes['repeat_max'] : 1;
            if (isset($this->_attributes['repeat_max']) && ($this->_attributes['repeat_max'] == FE::INFINITE)) {
                $max = 'GForm.INFINITE';
            }
            
            return "oRepeat: {iMin: {$min}, iMax: {$max}}";
        }
        
        return '';
    }

    protected function formatDependencyJavascript ()
    {
        $dependencies = Array();
        if (isset($this->_attributes['dependencies']) && is_array($this->_attributes['dependencies'])) {
            foreach ($this->_attributes['dependencies'] as $dependency) {
                $dependencies[] = $dependency->renderJavascript();
            }
        }
        if (count($dependencies)) {
            return 'agDependencies: [' . implode(', ', $dependencies) . ']';
        }
        
        return '';
    }

    protected function formatFactorJavascript ($factor, $name)
    {
        return "{$name}: {$this->_attributes[$factor]->render()}";
    }

    public function renderJavascript ()
    {
        return "{fType: {$this->_jsNodeName},{$this->formatAttributesJavascript($this->prepareAttributesJavascript())}}";
    }

    public function renderJavascriptAllegroParams ()
    {
        return $this->renderJavascript();
    }

    protected function prepareAttributesJavascript ()
    {
        return Array();
    }

    public function renderStatic ()
    {
    }

    public function Validate ()
    {
        return true;
    }

    protected function isLocale ($locale)
    {
        $data = \Locale::parseLocale($locale);
        return isset($data['region']);
    }

    protected function _IsIterated ($array)
    {
        $key = key($array);
        
        if (is_numeric($key) || substr($key, 0, 4) == 'new-' || $this->isLocale($key)) {
            return true;
        }
        
        return false;
    }

    public function Populate ($value)
    {
    }

    protected function prepareAutoAttributesJavascript ()
    {
        $attributes = Array();
        $attributes = array_merge($attributes, $this->_xajaxMethods);
        
        return $attributes;
    }

    protected function registerXajaxMethod ($name, $callback)
    {
        $jsName = $name . '_' . $this->_id;
        $this->_attributes[$name] = 'xajax_' . $jsName;
        App::getRegistry()->xajaxInterface->registerFunction(array(
            $jsName,
            $callback[0],
            $callback[1]
        ));
        $this->_xajaxMethods[] = $this->formatAttributeJavascript($name, $name, FE::TYPE_FUNCTION);
    }

    public function __get ($attributeName)
    {
        return isset($this->_attributes[$attributeName]) ? $this->_attributes[$attributeName] : null;
    }
}
