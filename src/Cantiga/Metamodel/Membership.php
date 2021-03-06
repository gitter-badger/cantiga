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

use Cantiga\Metamodel\Capabilities\IdentifiableInterface;

/**
 * Represents information about the membership of some user in the given place.
 *
 * @author Tomasz Jędrzejewski
 */
class Membership
{
	private $item;
	private $role;
	private $note;
	
	public function __construct(IdentifiableInterface $item = null, MembershipRole $role = null, $note = '')
	{
		$this->item = $item;
		$this->role = $role;
		$this->note = $note;
	}
	
	/**
	 * @return IdentifiableInterface
	 */
	public function getItem()
	{
		return $this->item;
	}

	/**
	 * @return MembershipRole
	 */
	public function getRole()
	{
		return $this->role;
	}

	/**
	 * @return string
	 */
	public function getNote()
	{
		return $this->note;
	}
}
