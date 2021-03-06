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
namespace Cantiga\CoreBundle\Api;

use Cantiga\Metamodel\Membership;
use Cantiga\Metamodel\MembershipLoaderInterface;
use LogicException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Represents a single logical part of the application that has certain access control settings,
 * its own pages and logic. Example workspaces are the 'admin' or 'individual' workspace, as well
 * as area and management spaces, provided directly by Cantiga core.
 * 
 * <p>Workspace logic is handled by the workspace listener. It asks the controller with <tt>WorkspaceAwareInterface</tt>
 * implemented to return the workspace instance. There are predefined controllers for all the core
 * Cantiga workspaces. The workspace menu is configured by sending <tt>WorkspaceEvent</tt> to the engine
 * that collects the configuration of the workgroups, work items, etc., and then passes it to the template.
 *
 * @author Tomasz Jędrzejewski
 */
abstract class Workspace
{
	private $workgroups = array();
	private $items = array();
	
	final public function addWorkgroup(Workgroup $workgroup)
	{
		$this->workgroups[$workgroup->getKey()] = $workgroup;
	}
	
	final public function addWorkItem($workgroupId, WorkItem $workItem)
	{
		if (!isset($this->items[$workgroupId])) {
			$this->items[$workgroupId] = array();
		}
		$this->items[$workgroupId][] = $workItem;
	}
	
	final public function getWorkgroups()
	{
		$workgroups = array_values($this->workgroups);
		usort($workgroups, function($a, $b) {
			return $a->getOrder() - $b->getOrder();
		});
		return $workgroups;
	}
	
	final public function workgroupHasContent(Workgroup $workgroup)
	{
		return isset($this->items[$workgroup->getKey()]);
	}
	
	final public function workitemsOf(Workgroup $workgroup)
	{
		if (isset($this->items[$workgroup->getKey()])) {
			return $this->items[$workgroup->getKey()];
		}
		return array();
	}
	
	final public function getWorkgroup($workgroupId)
	{
		if (!isset($this->workgroups[$workgroupId])) {
			throw new LogicException('Unknown workgroup: '.$workgroupId);
		}
		return $this->workgroups[$workgroupId];
	}
	
	/**
	 * If this workspace is tied to a concrete entity the user is member of, this
	 * method shall return a loader for that entity and all the entity information.
	 * Otherwise, the returned value shall be NULL.
	 * 
	 * @return MembershipLoaderInterface
	 */
	public function getMembershipLoader()
	{
		return null;
	}
	
	public function onWorkspaceLoaded(Membership $membership)
	{
		// empty
	}
	
	public function getHelpPages(RouterInterface $router)
	{
		return [];
	}
	
	/**
	 * @return Key used for workspace registration in the bundle.
	 */
	abstract public function getKey();
	
	/**
	 * Returns the name of the event used for populating this workspace.
	 * 
	 * @return string
	 */
	abstract public function getWorkspaceEvent();
}
