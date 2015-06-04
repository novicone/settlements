<?php
/**
 * Created by PhpStorm.
 * User: novic
 * Date: 23.05.15
 * Time: 15:00
 */

namespace Settlements\Domain;


class Expense {

    private $name;
    /**
     * @var array
     */
    private $payments;
    /**
     * @var array
     */
    private $beneficiaries;

    private $settlement;

    /**
     * @param $name
     * @param array|Payment[] $payments
     * @param array $beneficiaries
     */
    public function __construct($name, array $payments, array $beneficiaries) {
        $this->name = $name;
        $this->payments = $payments;
        $this->beneficiaries = $beneficiaries;

        $settlement = Settlement::create();
        foreach ($payments as $payment) {
            $settlement = $settlement->plus(Settlement::fromTransfer($payment->getPayer(), $beneficiaries, $payment->getAmount()));
        }
        $this->settlement = $settlement;
    }

    public function getName() {
        return $this->name;
    }

    public function getPayments() {
        return $this->payments;
    }

    public function getBeneficiaries() {
        return $this->beneficiaries;
    }

    public function getParticipants() {
        $participants = $this->beneficiaries;
        foreach ($this->payments as $payment) {
            $participants[] = $payment->getPayer();
        }
        return array_unique($participants);
    }

    public function getSettlement() {
        return $this->settlement;
    }

    public function equals(Expense $other = null) {
        if (!$other) {
            return false;
        }
        return $other->getName() == $this->name;
    }

}