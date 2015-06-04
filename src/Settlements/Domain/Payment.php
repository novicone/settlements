<?php
/**
 * Created by PhpStorm.
 * User: novic
 * Date: 23.05.15
 * Time: 15:02
 */

namespace Settlements\Domain;


class Payment {

    private $payer;
    private $amount;

    public function __construct($payer, $amount) {
        $this->payer = $payer;
        $this->amount = $amount;
    }

    /**
     * @return mixed
     */
    public function getPayer() {
        return $this->payer;
    }

    /**
     * @return mixed
     */
    public function getAmount() {
        return $this->amount;
    }

}