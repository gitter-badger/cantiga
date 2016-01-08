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
namespace Cantiga\MilestoneBundle\Controller;

use Cantiga\CoreBundle\Api\Controller\ProjectPageController;
use Cantiga\MilestoneBundle\Repository\MilestoneSummaryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/project/{slug}/milestone-summary")
 * @Security("has_role('ROLE_PROJECT_VISITOR')")
 */
class ProjectMilestoneSummaryController extends ProjectPageController
{
	const REPOSITORY_NAME = 'cantiga.milestone.repo.summary';
	const MILESTONE_TEMPLATE = 'CantigaMilestoneBundle:MilestoneEditor:milestone-summary.html.twig';
	/**
	 * @var MilestoneSummaryRepository
	 */
	private $repository;
	
	public function initialize(Request $request, AuthorizationCheckerInterface $authChecker)
	{
		$this->repository = $this->get(self::REPOSITORY_NAME);
		$this->breadcrumbs()
			->workgroup('summary')
			->entryLink($this->trans('Milestones', [], 'pages'), 'project_milestone_summary', ['slug' => $this->getSlug()]);
	}
	
	/**
	 * @Route("/view/{type}", name="project_milestone_summary", defaults={"type" = 0})
	 */
	public function indexAction($type, Request $request)
	{
		if ($type == 0) {
			$items = $this->repository->findMilestoneProgressForAreasInProject($this->getActiveProject());
		} else {
			$items = $this->repository->findMilestoneProgressForGroupsInProject($this->getActiveProject());
		}
		return $this->render(self::MILESTONE_TEMPLATE, array(
			'pageTitle' => 'Milestones',
			'pageSubtitle' => 'View the progress of areas and groups',
			'viewPage' => 'project_milestone_summary',
			'showTypeSelector' => true,
			'editPage' => 'project_milestone_editor',
			'type' => $type,
			'items' => $items,
		));
	}
}