<?php
/**
 * Created by PhpStorm.
 * User: novic
 * Date: 23.05.15
 * Time: 14:58
 */

namespace Settlements\Domain;


class BillOfCharges {

    private $id;
    private $title;
    /**
     * @var array|Expense[]
     */
    private $expenses;

    public function __construct($id, $title) {
        $this->id = $id;
        $this->title = $title;
        $this->expenses = [];
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    public function addExpense(Expense $expense) {
        $name = $expense->getName();
        if (isset($this->expenses[$name])) {
            throw new \Exception("Expense '$name' already added'");
        }
        $this->expenses[$name] = $expense;
    }

    public function removeExpense(Expense $expense) {
        $name = $expense->getName();
        if (!isset($this->expenses[$name])) {
            throw new \Exception("There is no expense '$name' in this settlement");
        }
        unset($this->expenses[$name]);
    }

    public function hasExpense(Expense $expense) {
        return isset($this->expenses[$expense->getName()]);
    }

    /**
     * @return array|Expense[]
     */
    public function getExpenses() {
        return array_values($this->expenses);
    }

    public function getParticipants() {
        $participants = [];
        foreach ($this->expenses as $expense) {
            $participants = array_unique(array_merge($participants, $expense->getParticipants()));
        }
        return $participants;
    }

    public function getDebts() {
        return $this->calculateDebts();
    }

    private function calculateDebts() {
        /** @var Settlement $settlement */
        $settlement = array_reduce($this->expenses, function(Settlement $settlement, Expense $expense) {
            return $settlement->plus($expense->getSettlement());
        }, Settlement::create());

        $debts = [];

        while (($participantsCount = count($amounts = $settlement->getAmounts())) > 0) {
            if ($participantsCount == 1) {
                throw new \Exception("Couldn't calculate debts");
            }
            $participants = array_keys($amounts);

            $creditSide = $participants[0];
            $debtSide = $participants[$participantsCount - 1];

            $a = abs($amounts[$creditSide]);
            $b = abs($amounts[$debtSide]);

            $transfer = min($a, $b);

            $debtSettlement = Settlement::fromTransfer($debtSide, $creditSide, $transfer);
            $settlement = $settlement->plus($debtSettlement);

            $debts[] = new Debt($creditSide, $debtSide, $transfer);
        }
        return $debts;
    }

}