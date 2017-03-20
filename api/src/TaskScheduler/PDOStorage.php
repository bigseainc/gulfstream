<?php

namespace BigSea\Gulfstream\API\TaskScheduler;

use \DateTime;
use \PDO;

/**
 * @SuppressWarnings(PHPMD)
 */
class PDOStorage implements StorageInterface
{
    const FORMAT_MYSQL = "Y-m-d H:i:s";
    private $db;

    public function __construct($connection)
    {
        $this->db = $connection;
    }

    private function getNextQueueOrder()
    {
        $q = "SELECT COALESCE(MAX(`queue_order`),0)+1 FROM `tasks`";
        $s = $this->db->prepare($q);
        $s->execute();
        return $s->fetchColumn();
    }

    public function add($timestamp, $classname, $params)
    {
        if (!$this->db->beginTransaction()) {
            return ['','','Could not get transaction lock'];
        }

        $next_order = $this->getNextQueueOrder();
        $q = "INSERT INTO `tasks` (`when`, `class`, `params`, `created`, `queue_order`) "
            ."VALUES (:when, :class, :params, NOW(), :order);";
        $s = $this->db->prepare($q);
        $s->bindValue(':when', date(self::FORMAT_MYSQL, $timestamp), PDO::PARAM_STR);
        $s->bindValue(':class', $classname, PDO::PARAM_STR);
        $s->bindValue(':params', serialize($params), PDO::PARAM_STR);
        $s->bindValue(':order', $next_order, PDO::PARAM_INT);
        $result = $s->execute() || $this->db->errorInfo();
        $this->db->commit();
        return $result;
    }

    private function getRowForProcessing($datetime = null)
    {
        $this->db->beginTransaction();

        if ($datetime === null) {
            $q = "SELECT * FROM `tasks` WHERE `state` = 'WAITING' "
                ."ORDER BY `queue_order` ASC LIMIT 1 FOR UPDATE";
            $s = $this->db->prepare($q);
        } else {
            $q = "SELECT * FROM `tasks` WHERE `state` = 'WAITING' "
                ."AND `when` <= :when "
                ."ORDER BY `queue_order` ASC LIMIT 1 FOR UPDATE";
            $s = $this->db->prepare($q);
            $s->bindValue(':when', $datetime);
        }

        if (!$s->execute()) {
            return false;
        }

        $row = $s->fetch(PDO::FETCH_OBJ);
        if (!$row) {
            return false;
        }

        $q2 = "UPDATE `tasks` SET `state` = 'PROCESSING' WHERE `id` = :id";
        $s2 = $this->db->prepare($q2);
        $s2->bindValue(':id', $row->id, PDO::PARAM_INT);
        $s2->execute();

        $this->db->commit();

        return $row;
    }

    public function release($id)
    {
        $this->db->beginTransaction();
        $q = "UPDATE `tasks` SET `state` = 'WAITING' WHERE `id` = :id AND `state` = 'PROCESSING'";
        $s = $this->db->prepare($q);
        $s->bindValue(':id', $id, PDO::PARAM_INT);
        $s->execute();
        $this->db->commit();
    }

    public function iterator()
    {
        do {
            $found = $this->getRowForProcessing();
            if ($found) {
                yield $found;
                $this->release($found->id);
            }
        } while ($found);
    }

    public function iteratorBefore($timestamp)
    {
        $cutoff = date(self::FORMAT_MYSQL, $timestamp);
        do {
            $found = $this->getRowForProcessing($cutoff);
            if ($found) {
                yield $found;
                $this->release($found->id);
            }
        } while ($found);
    }

    public function iteratorWithFilter(callable $filter)
    {
        if (!is_callable($filter)) {
            foreach ($this->iterator() as $row) {
                yield $row;
            }
            return;
        }
        foreach ($this->iterator() as $row) {
            if (call_user_func($filter, $row)) {
                yield $row;
            }
        }
    }

    public function finish($id)
    {
        $this->db->beginTransaction();
        $q = "DELETE FROM `tasks` WHERE `id` = :id";
        $s = $this->db->prepare($q);
        $s->bindValue(':id', $id, PDO::PARAM_INT);
        $s->execute();
        $this->db->commit();
    }

    public function requeue($id)
    {
        $this->db->beginTransaction();
        $order = $this->getNextQueueOrder();
        $q = "UPDATE `tasks` SET `queue_order` = :order, `count` = `count`+1  WHERE `id` = :id;";
        $s = $this->db->prepare($q);
        $s->bindValue(':id', $id, PDO::PARAM_INT);
        $s->bindValue(':order', $order, PDO::PARAM_INT);
        $s->execute();
        $this->db->commit();

        $this->release($id);
    }

    public function reschedule($id, $timestamp)
    {
        $this->db->beginTransaction();
        $q = "UPDATE `tasks` SET `when` = :when, `count` = `count`+1 WHERE `id` = :id";
        $s = $this->db->prepare($q);
        $s->bindValue(':id', $id, PDO::PARAM_INT);
        $s->bindValue(':when', date(self::FORMAT_MYSQL, $timestamp), PDO::PARAM_STR);
        $s->execute();
        $this->db->commit();

        $this->release($id);
    }

    public function disable($id)
    {
        $this->db->beginTransaction();
        $q = "UPDATE `tasks` SET `state` = 'DISABLED', `count` = `count`+1  WHERE `id` = :id";
        $s = $this->db->prepare($q);
        $s->bindValue(':id', $id, PDO::PARAM_INT);
        $s->execute();
        $this->db->commit();
    }

    public function enable($id)
    {
        $this->db->beginTransaction();
        $q = "UPDATE `tasks` SET `state` = 'WAITING' WHERE `id` = :id";
        $s = $this->db->prepare($q);
        $s->bindValue(':id', $id, PDO::PARAM_INT);
        $s->execute();
        $this->db->commit();
    }
}
