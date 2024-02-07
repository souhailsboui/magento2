<?php

namespace Biztech\Ausposteparcel\Block\Adminhtml\Articletype\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('articletype_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Article Types'));
    }
}
