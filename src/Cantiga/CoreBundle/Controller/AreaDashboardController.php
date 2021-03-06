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
use Cantiga\CoreBundle\CoreExtensions;
use Cantiga\CoreBundle\CoreTexts;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/area/{slug}")
 */
class AreaDashboardController extends AreaPageController
{

	use Traits\DashboardTrait;

	/**
	 * @Route("/index", name="area_dashboard")
	 */
	public function indexAction(Request $request)
	{
		return $this->render('CantigaCoreBundle:Area:dashboard.html.twig', array(
			'user' => $this->getUser(),
			'area' => $this->getMembership()->getItem(),
			'topExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::AREA_DASHBOARD_TOP)),
			'rightExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::AREA_DASHBOARD_RIGHT)),
			'centralExtensions' => $this->renderExtensions($request, $this->findDashboardExtensions(CoreExtensions::AREA_DASHBOARD_CENTRAL)),
		));
	}

	/**
	 * @Route("/help/introduction", name="area_help_introduction")
	 */
	public function helpIntroductionAction(Request $request)
	{
		return $this->renderHelpPage($request, 'area_help_introduction', CoreTexts::HELP_AREA_INTRODUCTION);
	}

	/**
	 * @Route("/help/members", name="area_help_members")
	 */
	public function helpMembersAction(Request $request)
	{
		return $this->renderHelpPage($request, 'area_help_members', CoreTexts::HELP_AREA_MEMBERS);
	}

}
