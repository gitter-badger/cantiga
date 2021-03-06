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
namespace Cantiga\ForumBundle\Repository;

use Cantiga\ForumBundle\Entity\ForumCategoryView;
use Cantiga\ForumBundle\Entity\ForumRoot;
use Cantiga\ForumBundle\Entity\ForumView;
use Cantiga\ForumBundle\ForumTables;
use Doctrine\DBAL\Connection;

/**
 * Offers an access to the list of forums, categories, topics, etc...
 */
class ForumViewRepository
{
	private $conn;
	
	public function __construct(Connection $conn)
	{
		$this->conn = $conn;
	}
	
	public function fetchMainPageData(ForumRoot $root)
	{
		$categoryData = $this->conn->fetchAll('SELECT * FROM `'.ForumTables::FORUM_CATEGORY_TBL.'` WHERE `rootId` = :rootId ORDER BY `displayOrder`', [':rootId' => $root->getId()]);
		$forumData = $this->conn->fetchAll('SELECT * FROM `'.ForumTables::FORUM_TBL.'` WHERE `rootId` = :rootId AND `parentId` IS NULL ORDER BY `categoryId`, `leftPosition`', [':rootId' => $root->getId()]);
	
		$categories = [];
		$categoryMap = [];
		foreach ($categoryData as $category) {
			$item = new ForumCategoryView($root, $category);
			$categories[] = $item;
			$categoryMap[$item->getId()] = $item;
		}
		foreach ($forumData as $forum) {
			$category = $categoryMap[$forum['categoryId']];
			$category->appendForum(new ForumView($root, $category, $forum));
		}
		return $categories;
	}
}
