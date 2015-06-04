<?php
/**
 * Created by PhpStorm.
 * User: novic
 * Date: 29.05.15
 * Time: 20:23
 */

namespace Settlements\Application;


use Settlements\Domain\BillOfCharges;
use Settlements\Domain\Expense;

class BillOfChargesEditor {

    /**
     * @var BillOfChargesRepository
     */
    private $repository;

    public function __construct(BillOfChargesRepository $repository) {
        $this->repository = $repository;
    }

    public function create($id, $title) {
        $billOfCharges = new BillOfCharges($id, $title);
        $this->repository->store($billOfCharges);
    }

    public function addExpense($id, Expense $expense) {
        $this->repository->performTransaction($id, function(BillOfCharges $billOfCharges) use ($expense) {
            $billOfCharges->addExpense($expense);
        });
    }

    public function replaceExpense($id, $name, Expense $expense) {
        $this->repository->performTransaction($id, function(BillOfCharges $billOfCharges) use ($name, $expense) {
            $billOfCharges->removeExpense(new Expense($name, [], []));
            $billOfCharges->addExpense($expense);
        });
    }

    public function removeExpense($id, Expense $expense) {
        $this->repository->performTransaction($id, function(BillOfCharges $billOfCharges) use ($expense) {
            $billOfCharges->removeExpense($expense);
        });
    }

}