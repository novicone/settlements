<?php
/**
 * Created by PhpStorm.
 * User: novic
 * Date: 29.05.15
 * Time: 21:06
 */

namespace AppBundle\Repository;


use Doctrine\DBAL\Connection;
use Settlements\Application\BillOfChargesRepository;
use Settlements\Domain\BillOfCharges;

class DatabaseBillOfChargesRepository implements BillOfChargesRepository {

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    /**
     * @param $id
     * @param callable $callback
     */
    public function performTransaction($id, callable $callback) {
        $bill = $this->find($id);
        $callback($bill);
        $this->store($bill);
    }

    /**
     * @param BillOfCharges $billOfCharges
     */
    public function store(BillOfCharges $billOfCharges) {
        $id = $billOfCharges->getId();
        if ($this->connection->fetchColumn("SELECT id FROM bills WHERE id = :id", ["id" => $id])) {
            $this->connection->delete("bills", ["id" => $id]);
        }
        $this->connection->insert("bills", [
            "id" => $id,
            "value" => serialize($billOfCharges)
        ]);
    }

    /**
     * @param $id
     * @return BillOfCharges
     */
    public function find($id) {
        return unserialize($this->connection->fetchColumn("SELECT value FROM bills WHERE id = :id", ["id" => $id]));
    }

    public function nextId() {
        $id = 0;
        $this->connection->transactional(function(Connection $connection) use (&$id) {
            $connection->insert("bills", ["value" => ""]);
            $id = $connection->lastInsertId();
            $connection->delete("bills", ["id" => $id]);
        });
        return $id;
    }

}