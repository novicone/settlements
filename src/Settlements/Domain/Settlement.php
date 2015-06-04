<?php
/**
 * Created by PhpStorm.
 * User: novic
 * Date: 23.05.15
 * Time: 15:01
 */

namespace Settlements\Domain;


class Settlement {

    const EPSILON = 0.001;

    /**
     * @var array
     */
    private $amounts;

    private function __construct(array $amounts = []) {
        asort($amounts, SORT_NUMERIC);
        $this->amounts = array_filter($amounts, function($amount) {
            return abs($amount) > self::EPSILON;
        });
    }

    public function plus(Settlement $other) {
        $amounts = $this->amounts;
        foreach ($other->getAmounts() as $person => $amount) {
            if (isset($amounts[$person])) {
                $amounts[$person] += $amount;
            } else {
                $amounts[$person] = $amount;
            }
        }
        return new Settlement($amounts);
    }

    public function getAmounts() {
        return $this->amounts;
    }

    static public function create() {
        return new Settlement();
    }

    static public function fromTransfer($from, $to, $amount) {
        $from = is_array($from) ? $from : [$from];
        $to = is_array($to) ? $to : [$to];

        $perGiver = $amount / count($from);
        $perTaker = $amount / count($to);

        $amounts = [];
        foreach ($from as $giver) {
            $amounts[$giver] = -$perGiver;
        }
        foreach ($to as $taker) {
            if (isset($amounts[$taker])) {
                $amounts[$taker] += $perTaker;
            } else {
                $amounts[$taker] = $perTaker;
            }
        }

        return new Settlement($amounts);
    }

}