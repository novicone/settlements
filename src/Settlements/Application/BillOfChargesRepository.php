<?php
/**
 * Created by PhpStorm.
 * User: novic
 * Date: 29.05.15
 * Time: 20:39
 */

namespace Settlements\Application;


use Settlements\Domain\BillOfCharges;

interface BillOfChargesRepository {

    /**
     * @param $id
     * @param callable $callback
     */
    public function performTransaction($id, callable $callback);

    /**
     * @param BillOfCharges $billOfCharges
     */
    public function store(BillOfCharges $billOfCharges);

    /**
     * @param $id
     * @return BillOfCharges
     */
    public function find($id);

}