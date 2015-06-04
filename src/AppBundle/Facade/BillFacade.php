<?php
/**
 * Created by PhpStorm.
 * User: novic
 * Date: 29.05.15
 * Time: 21:31
 */

namespace AppBundle\Facade;


use AppBundle\Repository\DatabaseBillOfChargesRepository;
use Settlements\Application\BillOfChargesEditor;
use Settlements\Domain\BillOfCharges;
use Settlements\Domain\Debt;
use Settlements\Domain\Expense;
use Settlements\Domain\Payment;

class BillFacade {

    /**
     * @var DatabaseBillOfChargesRepository
     */
    private $repository;
    /**
     * @var BillOfChargesEditor
     */
    private $editor;

    public function __construct(DatabaseBillOfChargesRepository $repository, BillOfChargesEditor $editor) {
        $this->repository = $repository;
        $this->editor = $editor;
    }

    public function createBill($title) {
        $id = $this->repository->nextId();
        $this->editor->create($id, $title);
        return $id;
    }

    public function getBill($id) {
        $bill = $this->repository->find($id);
        return assembleBillInfo($bill);
    }

    public function getExpense($id, $name) {
        $bill = $this->repository->find($id);
        $expenses = $bill->getExpenses();
        foreach ($expenses as $expense) {
            if ($expense->getName() == $name) {
                return assembleExpenseInfo($expense);
            }
        }
        return null;
    }

    public function addExpense($id, $name, array $payments, array $beneficiaries) {
        $expense = assembleExpense($name, $payments, $beneficiaries);
        $this->editor->addExpense($id, $expense);
    }

    public function removeExpense($id, $name) {
        $this->editor->removeExpense($id, new Expense($name, [], []));
    }

    public function replaceExpense($id, $oldName, $name, array $payments, array $beneficiaries) {
        $expense = assembleExpense($name, $payments, $beneficiaries);
        $this->editor->replaceExpense($id, $oldName, $expense);
    }

}

function assembleExpense($name, array $payments, array $beneficiaries) {
    return new Expense($name, array_map(function(array $paymentInfo) {
        return new Payment($paymentInfo["payer"], $paymentInfo["amount"]);
    }, $payments), $beneficiaries);
}

function assembleBillInfo(BillOfCharges $bill) {
    $expenses = array_map(function(Expense $expense) {
        return assembleExpenseInfo($expense);
    }, $bill->getExpenses());
    usort($expenses, function($a, $b) {
        return strcmp($a["name"], $b["name"]);
    });

    $debts = array_map(function(Debt $debt) {
        return [
            "creditor" => $debt->getCreditor(),
            "debtor" => $debt->getDebtor(),
            "amount" => $debt->getAmount()
        ];
    }, $bill->getDebts());

    return [
        "id" => $bill->getId(),
        "title" => $bill->getTitle(),
        "participants" => $bill->getParticipants(),
        "expenses" => $expenses,
        "debts" => $debts
    ];
}

function assembleExpenseInfo(Expense $expense) {
    $payments = array_map(function(Payment $payment) {
        return [
            "payer" => $payment->getPayer(),
            "amount" => $payment->getAmount()
        ];
    }, $expense->getPayments());
    return [
        "name" => $expense->getName(),
        "beneficiaries" => $expense->getBeneficiaries(),
        "payments" => $payments
    ];
}