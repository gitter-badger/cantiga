<?php
/*
 * This file is part of Cantiga Project. Copyright 2015 Tomasz Jedrzejewski.
 *
 * Cantiga Project is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Cantiga Project is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Foobar; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
namespace Cantiga\CoreBundle\Extension;

use Cantiga\CoreBundle\Api\Controller\CantigaController;
use Cantiga\CoreBundle\Entity\Area;
use Symfony\Component\HttpFoundation\Request;

/**
 * Used for rendering blocks on the dashboards
 * 
 * @author Tomasz Jędrzejewski
 */
interface AreaInformationExtensionInterface
{
	const PRIORITY_HIGH = 0;
	const PRIORITY_MEDIUM = 100;
	const PRIORITY_LOW = 200;
	
	public function getPriority();
	/**
	 * Renders the given area information component. The method shall return a rendered HTML code to display in
	 * the area information summary on the right side.
	 *  
	 * @param CantigaController $controller Current controller
	 * @param Request $request HTTP request
	 * @param Area $area Rendered area
	 * @return string
	 */
	public function render(CantigaController $controller, Request $request, Area $area);
}
