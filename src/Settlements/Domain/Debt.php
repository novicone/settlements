<?php
/**
 * Created by PhpStorm.
 * User: Novic
 * Date: 2014-10-05
 * Time: 12:21
 */

namespace Settlements\Domain;


class Debt {

    private $creditor;
    private $debtor;
    private $amount;

    function __construct($creditor, $debtor, $amount) {
        $this->creditor = $creditor;
        $this->debtor = $debtor;
        $this->amount = $amount;
    }

    public function getCreditor() {
        return $this->creditor;
    }

    public function getDebtor() {
        return $this->debtor;
    }


    public function getAmount() {
        return $this->amount;
    }

}