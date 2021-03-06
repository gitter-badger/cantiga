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
namespace Cantiga\Metamodel;

/**
 * Represents a single role that can be assigned to a member of something.
 *
 * @author Tomasz Jędrzejewski
 */
class MembershipRole
{
	private $id;
	private $name;
	private $authRole;
	
	public function __construct($id, $name, $authRole)
	{
		$this->id = $id;
		$this->name = $name;
		$this->authRole = $authRole;
	}
	
	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getAuthRole()
	{
		return $this->authRole;
	}
	
	/**
	 * True, if this role represents an unknown role. It may appear, if the given entity doesn't support
	 * the role of the specified ID.
	 * 
	 * @return boolean
	 */
	public function isUnknown()
	{
		return $this->id < 0;
	}
}
