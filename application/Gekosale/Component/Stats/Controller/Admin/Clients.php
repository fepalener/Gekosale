<?php

/**
 * Gekosale, Open Source E-Commerce Solution
 * http://www.gekosale.pl
 *
 * Copyright (c) 2008-2012 Gekosale sp. z o.o.. Zabronione jest usuwanie informacji o licencji i autorach.
 *
 * This library is free software; you can redistribute it and/or 
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version. 
 * 
 * 
 * $Revision: 438 $
 * $Author: gekosale $
 * $Date: 2011-08-27 11:29:36 +0200 (So, 27 sie 2011) $
 * $Id: statsclients.php 438 2011-08-27 09:29:36Z gekosale $ 
 */

namespace Gekosale\Component\Statsclients\Controller\Admin;
use Gekosale\Core\Component\Controller\Admin;

class Clients extends Admin
{

	public function index ()
	{
        App::getModel('contextmenu')->add($this->trans('TXT_SALES_STATS'), $this->getRouter()->url('admin', 'statssales'));
        App::getModel('contextmenu')->add($this->trans('TXT_CLIENTS'), $this->getRouter()->url('admin', 'client'));

        
		$this->registry->xajax->processRequest();
		
		$this->renderLayout(array(
			'from' => date('Y/m/1'),
			'to' => date('Y/m/d'),
			'summaryStats' => App::getModel('mainside')->getSummaryStats()
		));
	}
}