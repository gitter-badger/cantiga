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

/**
 * Allows different bundles to register custom form implementations, so that they can be
 * attached to certain places within the system.
 *
 * @author Tomasz Jędrzejewski
 */
class CustomForms
{
	private static $services = array();
	
	public static function registerService($name, $implementation)
	{
		self::$services[$name] = $implementation;
	}
	
	public static function getNames()
	{
		return self::$services;
	}
	
	public static function getSelectableNames()
	{
		$result = array();
		foreach (self::$services as $name => $impl) {
			$result[$name] = $name;
		}
		return $result;
	}
	
	public static function asService($name)
	{
		if (!isset(self::$services[$name])) {
			throw new \LogicException('The specified custom form does not exist: '.$name);
		}
		return self::$services[$name];
	}
}
