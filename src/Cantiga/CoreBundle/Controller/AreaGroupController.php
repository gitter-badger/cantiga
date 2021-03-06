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
namespace Cantiga\CoreBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\AreaPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/area/{slug}/my-group")
 * @Security("has_role('ROLE_AREA_MEMBER')")
 */
class AreaGroupController extends AreaPageController
{
	/**
	 * @Route("/", name="area_my_group")
	 */
	public function myGroupAction(Request $request)
	{
		$area = $this->getMembership()->getItem();
		$group = $area->getGroup();
		if (null === $area->getGroup()) {
			return $this->showPageWithError('AreaNotAssignedToGroupMsg', 'area_dashboard', ['slug' => $this->getSlug()]);
		}
		
		$this->breadcrumbs()
			->workgroup('community')
			->entryLink($this->trans('My group', [], 'pages'), 'area_my_group', ['slug' => $this->getSlug()]);
		return $this->render('CantigaCoreBundle:AreaGroup:my-group.html.twig', [
			'group' => $group,
			'members' => $group->findMemberInformationForEntity($this->get('database_connection'), $area),
			'areas' => $group->findAreaSummary($this->get('database_connection'))
		]);
	}
}
