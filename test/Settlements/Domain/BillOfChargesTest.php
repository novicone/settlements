<?php
/**
 * Created by PhpStorm.
 * User: novic
 * Date: 23.05.15
 * Time: 20:07
 */

namespace Settlements\Domain;


class BillOfChargesTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var BillOfCharges
     */
    private $billOfCharges;

    protected function setUp() {
        $this->billOfCharges = new BillOfCharges(1, "testowy");
        $this->billOfCharges->addExpense(new Expense("zakup 1", [new Payment("tytus", 20)], ["tytus", "romek"]));
        $this->billOfCharges->addExpense(new Expense("zakup 2", [new Payment("romek", 100), new Payment("a'tomek", 50)], ["tytus", "romek", "a'tomek"]));
    }

    public function testIfDeterminesTheParticipantsCorrectly() {
        $this->assertSameArrayValues($this->billOfCharges->getParticipants(), ["tytus", "romek", "a'tomek"]);
        $this->billOfCharges->removeExpense(new Expense("zakup 2", [], []));
        $this->billOfCharges->addExpense(new Expense("zakup 2", [new Payment("papcio chmiel", 10)], ["tytus"]));
        $this->assertSameArrayValues($this->billOfCharges->getParticipants(), ["tytus", "romek", "papcio chmiel"]);
    }

    public function testIfCalculatesDebtsCorrectly() {
        $debts = $this->billOfCharges->getDebts();
        $this->assertEquals(1, count($debts));
        /** @var Debt $debt */
        $debt = $debts[0];
        $this->assertEquals(40, $debt->getAmount());
        $this->assertEquals("tytus", $debt->getDebtor());
        $this->assertEquals("romek", $debt->getCreditor());

        $this->billOfCharges->addExpense(new Expense("zakup 3", [new Payment("papcio chmiel", 10)], ["tytus"]));
        $debts = $this->billOfCharges->getDebts();
        $this->assertEquals(2, count($debts));
    }

    private function assertSameArrayValues($expected, $actual, $message = "") {
        $this->assertSame(array_diff($expected, $actual), array_diff($actual, $expected), $message);
    }

}
