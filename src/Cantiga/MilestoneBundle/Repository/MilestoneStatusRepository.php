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
namespace Cantiga\MilestoneBundle\Repository;

use Cantiga\CoreBundle\CoreTables;
use Cantiga\CoreBundle\Entity\Area;
use Cantiga\CoreBundle\Entity\Entity;
use Cantiga\CoreBundle\Entity\Group;
use Cantiga\CoreBundle\Entity\Project;
use Cantiga\CoreBundle\Settings\ProjectSettings;
use Cantiga\Metamodel\Capabilities\MembershipEntityInterface;
use Cantiga\Metamodel\Exception\ItemNotFoundException;
use Cantiga\Metamodel\Exception\ModelException;
use Cantiga\Metamodel\TimeFormatterInterface;
use Cantiga\Metamodel\Transaction;
use Cantiga\MilestoneBundle\Entity\Milestone;
use Cantiga\MilestoneBundle\MilestoneSettings;
use Cantiga\MilestoneBundle\MilestoneTables;
use Doctrine\DBAL\Connection;
use LogicException;
use PDO;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * The logic for updating the status of milestones for particular entities is here.
 *
 * @author Tomasz Jędrzejewski
 */
class MilestoneStatusRepository
{
	/**
	 * @var Connection 
	 */
	private $conn;
	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * @var TimeFormatterInterface
	 */
	private $timeFormatter;
	/**
	 * @var ProjectSettings
	 */
	private $settings;
	
	public function __construct(Connection $conn, Transaction $transaction, TimeFormatterInterface $timeFormatter, ProjectSettings $settings)
	{
		$this->conn = $conn;
		$this->transaction = $transaction;
		$this->timeFormatter = $timeFormatter;
		$this->settings = $settings;
	}
	
	public function findEntity($id)
	{
		$this->transaction->requestTransaction();
		try {
			$item = Entity::fetchById($this->conn, $id);
			if (false === $item) {
				throw new ItemNotFoundException('Item not found.');
			}
			return $item;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function findMilestone($id, Entity $entity, Project $project)
	{
		$this->transaction->requestTransaction();
		try {
			$item = Milestone::fetchByProjectAndType($this->conn, $id, $entity->getType(), $project);
			if (false === $item) {
				throw new ItemNotFoundException('Milestone not found.');
			}
			return $item;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function completeMilestone(Entity $entity, Milestone $milestone, MembershipEntityInterface $who)
	{
		$this->transaction->requestTransaction();
		try {
			$result = $milestone->complete($this->conn, $entity, Milestone::RETURN_SUMMARY, $this->timeFormatter);
			if (false === $result) {
				throw new ModelException('This milestone is already completed!');
			}
			return $result;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function updateMilestone(Entity $entity, Milestone $milestone, MembershipEntityInterface $who, $progress)
	{
		$this->transaction->requestTransaction();
		try {
			$result = $milestone->updateProgress($this->conn, $entity, $progress, Milestone::RETURN_SUMMARY, $this->timeFormatter);
			if (false === $result) {
				throw new ModelException('Milestone progress update has failed!');
			}
			return $result;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function cancelMilestone(Entity $entity, Milestone $milestone, MembershipEntityInterface $who)
	{
		$this->transaction->requestTransaction();
		try {
			$result = $milestone->cancel($this->conn, $entity, Milestone::RETURN_SUMMARY, $this->timeFormatter);
			if (false === $result) {
				throw new ModelException('This milestone is not completed yet!');
			}
			return $result;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function getAvailableEntities(MembershipEntityInterface $who, TranslatorInterface $translator)
	{
		$this->transaction->requestTransaction();
		try {
			$results = [];
			$results[] = ['id' => $who->getEntity()->getId(), 'name' => $translator->trans($who->getEntity()->getType().'Nominative: 0', [$who->getName()])];
			$query = '';
			if ($who instanceof Project) {
				$query = '(SELECT e.`id`, e.`name`, e.`type` FROM `'.CoreTables::ENTITY_TBL.'` e INNER JOIN `'.CoreTables::GROUP_TBL.'` g ON g.`entityId` = e.`id` WHERE g.`projectId` = '.$who->getId().' ORDER BY g.`name`) '
					. 'UNION (SELECT e.`id`, e.`name`, e.`type` FROM `'.CoreTables::ENTITY_TBL.'` e INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`entityId` = e.`id` WHERE a.`projectId` = '.$who->getId().' ORDER BY a.`name`)';
			} elseif ($who instanceof Group) {
				$query = 'SELECT e.`id`, e.`name`, e.`type` FROM `'.CoreTables::ENTITY_TBL.'` e INNER JOIN `'.CoreTables::AREA_TBL.'` a ON a.`entityId` = e.`id` WHERE a.`groupId` = '.$who->getId().' ORDER BY a.`name`';
			}

			if (!empty($query)) {
				foreach ($this->conn->fetchAll($query) as $entity) {
					$results[] = ['id' => $entity['id'], 'name' => $translator->trans($entity['type'].'Nominative: 0', [$entity['name']])];
				}
			}
			return $results;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function computeTotalProgress(Entity $entity, Project $project)
	{
		$this->transaction->requestTransaction();
		try {
			$completedNum = $this->conn->fetchColumn('SELECT `completedNum` FROM `'.MilestoneTables::MILESTONE_PROGRESS_TBL.'` WHERE `entityId` = :id', [':id' => $entity->getId()]);
			$total = $this->conn->fetchColumn('SELECT COUNT(`id`) FROM `'.MilestoneTables::MILESTONE_TBL.'` WHERE `projectId` = :projectId AND `entityType` = :entityType', [
				':projectId' => $project->getId(),
				':entityType' => $entity->getType(),
			]);

			if (0 == $total) {
				return 0;
			} else {
				return ($completedNum / $total * 100);
			}
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function findAllMilestones(Entity $entity, Project $project, $editable)
	{
		$this->transaction->requestTransaction();
		try {
			$stmt = $this->conn->prepare('SELECT m.`id`, m.`name`, m.`type`, m.`description`, m.`deadline`, s.`progress`, s.`completedAt` '
				. 'FROM `'.MilestoneTables::MILESTONE_TBL.'` m '
				. 'INNER JOIN `'.MilestoneTables::MILESTONE_STATUS_TBL.'` s ON m.`id` = s.`milestoneId` '
				. 'WHERE s.`entityId` = :entityId AND m.`projectId` = :projectId  '
				. 'ORDER BY m.`displayOrder`');
			$stmt->bindValue(':entityId', $entity->getId());
			$stmt->bindValue(':projectId', $project->getId());
			$stmt->execute();
			$results = [];
			while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$results[] = Milestone::processStatus($row, $this->timeFormatter, $editable);
			}
			return $results;
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function findClosestDeadline(Entity $entity, Project $project)
	{
		return Milestone::fetchClosestDeadline($this->conn, $entity, $project);
	}
	
	public function isAllowed(Entity $entity, MembershipEntityInterface $who, $editable = false)
	{
		$this->transaction->requestTransaction();
		try {
			if ($who instanceof Project) {
				return $this->isAllowedForProject($entity, $who, $editable);
			} elseif ($who instanceof Group) {
				return $this->isAllowedForGroup($entity, $who, $editable);
			} elseif ($who instanceof Area) {
				return $this->isAllowedForArea($entity, $who, $editable);
			}
			throw new LogicException('Unsupported type of entity!');
		} catch(Exception $exception) {
			$this->transaction->requestRollback();
			throw $exception;
		}
	}
	
	public function isEditable(Entity $entity, MembershipEntityInterface $who)
	{
		if ($who instanceof Area) {
			return $this->settings->get(MilestoneSettings::AREA_CAN_UPDATE_OWN_PROGRESS)->getValue();
		} elseif ($who instanceof Group) {
			if ($entity->getType() == 'Group') {
				return $this->settings->get(MilestoneSettings::GROUP_CAN_UPDATE_OWN_PROGRESS)->getValue();
			} else if($entity->getType() == 'Area') {
				return $this->settings->get(MilestoneSettings::GROUP_CAN_UPDATE_AREA_PROGRESS)->getValue();
			}
			return false;
		} elseif ($who instanceof Project) {
			return true;
		}
		return false;
	}
	
	private function isAllowedForProject(Entity $entity, MembershipEntityInterface $who, $editable = false)
	{
		switch ($entity->getType()) {
			case 'Project':
				return $who->getEntity()->getId() == $entity->getId();
			case 'Group':
				$pid = $this->conn->fetchColumn('SELECT `projectId` FROM `'.CoreTables::GROUP_TBL.'` WHERE `entityId` = :id', [':id' => $entity->getId()]);
				return $pid == $who->getId();
			case 'Area':
				$pid = $this->conn->fetchColumn('SELECT `projectId` FROM `'.CoreTables::AREA_TBL.'` WHERE `entityId` = :id', [':id' => $entity->getId()]);
				return $pid == $who->getId();
		}
		return false;
	}
	
	private function isAllowedForGroup(Entity $entity, MembershipEntityInterface $who, $editable = false)
	{
		switch ($entity->getType()) {
			case 'Project':
				return false;
			case 'Group':
				if (!$editable || $this->settings->get(MilestoneSettings::GROUP_CAN_UPDATE_OWN_PROGRESS)->getValue()) {
					return $who->getEntity()->getId() == $entity->getId();
				}
				return false;
			case 'Area':
				if (!$editable || $this->settings->get(MilestoneSettings::GROUP_CAN_UPDATE_AREA_PROGRESS)->getValue()) {
					$pid = $this->conn->fetchColumn('SELECT `groupId` FROM `'.CoreTables::AREA_TBL.'` WHERE `entityId` = :id', [':id' => $entity->getId()]);
					return $pid == $who->getId();
				}
				return false;
		}
		return false;
	}
	
	private function isAllowedForArea(Entity $entity, MembershipEntityInterface $who, $editable = false)
	{
		if ($entity->getType() == 'Area') {
			if (!$editable || $this->settings->get(MilestoneSettings::AREA_CAN_UPDATE_OWN_PROGRESS)->getValue()) {
				return $who->getEntity()->getId() == $entity->getId(); 
			}
		}
		return false;
	}
}
