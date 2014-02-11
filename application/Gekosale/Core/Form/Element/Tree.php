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

class Tree extends Field
{

    protected $_jsGetChildren;

    public function __construct ($attributes)
    {
        parent::__construct($attributes);
        $this->_jsGetChildren = 'GetChildren_' . $this->_id;
        if (isset($this->_attributes['load_children']) && is_callable($this->_attributes['load_children'])) {
            $this->_attributes['get_children'] = 'xajax_' . $this->_jsGetChildren;
            App::getRegistry()->xajaxInterface->registerFunction(array(
                $this->_jsGetChildren,
                $this,
                'getChildren'
            ));
        }
        if (! isset($this->_attributes['addLabel'])) {
            $this->_attributes['addLabel'] = $this->trans('TXT_ADD');
        }
        if (! isset($this->_attributes['retractable'])) {
            $this->_attributes['retractable'] = true;
        }
        
        $this->_attributes['total'] = count($this->_attributes['items']);
    }

    public function getChildren ($request)
    {
        $children = call_user_func($this->_attributes['load_children'], $request['parent']);
        if (! is_array($children)) {
            $children = Array();
        }
        return Array(
            'children' => $children
        );
    }

    protected function prepareAttributesJavascript ()
    {
        $attributes = Array(
            $this->formatAttributeJavascript('name', 'sName'),
            $this->formatAttributeJavascript('label', 'sLabel'),
            $this->formatAttributeJavascript('addLabel', 'sAddLabel'),
            $this->formatAttributeJavascript('error', 'sError'),
            $this->formatAttributeJavascript('selectable', 'bSelectable'),
            $this->formatAttributeJavascript('choosable', 'bChoosable'),
            $this->formatAttributeJavascript('clickable', 'bClickable'),
            $this->formatAttributeJavascript('deletable', 'bDeletable'),
            $this->formatAttributeJavascript('sortable', 'bSortable'),
            $this->formatAttributeJavascript('retractable', 'bRetractable'),
            $this->formatAttributeJavascript('addable', 'bAddable'),
            $this->formatAttributeJavascript('total', 'iTotal'),
            $this->formatAttributeJavascript('restrict', 'iRestrict'),
            $this->formatAttributeJavascript('items', 'oItems', \FormEngine\FE::TYPE_OBJECT),
            $this->formatAttributeJavascript('onClick', 'fOnClick', \FormEngine\FE::TYPE_FUNCTION),
            $this->formatAttributeJavascript('onDuplicate', 'fOnDuplicate', \FormEngine\FE::TYPE_FUNCTION),
            $this->formatAttributeJavascript('onAdd', 'fOnAdd', \FormEngine\FE::TYPE_FUNCTION),
            $this->formatAttributeJavascript('onAfterAdd', 'fOnAfterAdd', \FormEngine\FE::TYPE_FUNCTION),
            $this->formatAttributeJavascript('onDelete', 'fOnDelete', \FormEngine\FE::TYPE_FUNCTION),
            $this->formatAttributeJavascript('onAfterDelete', 'fOnAfterDelete', \FormEngine\FE::TYPE_FUNCTION),
            $this->formatAttributeJavascript('onSaveOrder', 'fOnSaveOrder', \FormEngine\FE::TYPE_FUNCTION),
            $this->formatAttributeJavascript('active', 'sActive'),
            $this->formatAttributeJavascript('onAfterDeleteId', 'sOnAfterDeleteId'),
            $this->formatAttributeJavascript('add_item_prompt', 'sAddItemPrompt'),
            $this->formatAttributeJavascript('get_children', 'fGetChildren', \FormEngine\FE::TYPE_FUNCTION),
            $this->formatAttributeJavascript('prevent_duplicates', 'bPreventDuplicates', \FormEngine\FE::TYPE_BOOLEAN),
            $this->formatAttributeJavascript('prevent_duplicates_on_all_levels', 'bPreventDuplicatesOnAllLevels', \FormEngine\FE::TYPE_BOOLEAN),
            $this->formatAttributeJavascript('set', 'sSet'),
            $this->formatRepeatableJavascript(),
            $this->_FormatRules_JS(),
            $this->formatDependencyJavascript(),
            $this->_FormatDefaults_JS()
        );
        return $attributes;
    }
}
