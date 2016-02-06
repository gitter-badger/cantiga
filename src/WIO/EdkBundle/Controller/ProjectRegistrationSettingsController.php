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
namespace WIO\EdkBundle\Controller;

use Cantiga\CoreBundle\Api\Actions\CRUDInfo;
use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/participants/registration-settings")
 * @Security("has_role('ROLE_PROJECT_MEMBER')")
 */
class ProjectRegistrationSettingsController extends ProjectPageController
{
	use Traits\RegistrationSettingsTrait;

	const REPOSITORY_NAME = 'wio.edk.repo.registration';

	/**
	 * @var CRUDInfo
	 */
	private $crudInfo;

	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$repository = $this->get(self::REPOSITORY_NAME);
		$repository->setRootEntity($this->getMembership()->getItem());
		$this->crudInfo = $this->newCrudInfo($repository)
			->setTemplateLocation('WioEdkBundle:RegistrationSettings:')
			->setItemNameProperty('name')
			->setPageTitle('Registration settings')
			->setPageSubtitle('Manage the registration settings for the routes')
			->setIndexPage('project_reg_settings_index')
			->setInfoPage('project_reg_settings_info')
			->setEditPage('project_reg_settings_edit')
			->setItemUpdatedMessage('The registration settings \'0\' have been updated.');

		$this->breadcrumbs()
			->workgroup('participants')
			->entryLink($this->trans('Registration settings', [], 'pages'), $this->crudInfo->getIndexPage(), ['slug' => $this->getSlug()]);
	}

	/**
	 * @Route("/index", name="project_reg_settings_index")
	 */
	public function indexAction(Request $request)
	{
		return $this->performIndex('project_reg_settings_ajax_list', $request);
	}

	/**
	 * @Route("/ajax-list", name="project_reg_settings_ajax_list")
	 */
	public function ajaxListAction(Request $request)
	{
		return $this->performAjaxList($request);
	}

	/**
	 * @Route("/{id}/info", name="project_reg_settings_info")
	 */
	public function infoAction($id, Request $request)
	{
		return $this->performInfo($id, 'project_route_info', 'project_area_info', $request);
	}

	/**
	 * @Route("/{id}/edit", name="project_reg_settings_edit")
	 */
	public function editAction($id, Request $request)
	{
		return $this->performEdit($id, $request);
	}
}
