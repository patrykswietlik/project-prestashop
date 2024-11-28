<?php
/**
 * 2007-2023 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2023 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AmazonPayAdminActionsHelper
{
    private $transactions = [];
    private $summary = [
        'max_refund' => 0,
        'charge_allowed' => false,
        'refund_allowed' => false,
        'cancel_allowed' => false,
        'close_allowed' => false,
    ];

    /**
     * AmazonPayAdminActionsHelper constructor.
     * @param $transactions
     */
    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function getSummary()
    {
        $this->summary['max_refund'] = $this->calcMaxRefund();
        $this->summary['charge_allowed'] = $this->isChargeAllowed();
        $this->summary['refund_allowed'] = $this->isRefundAllowed();
        $this->summary['cancel_allowed'] = $this->isCancelAllowed();
        $this->summary['close_allowed'] = $this->isCloseAllowed();
        return $this->summary;
    }

    private function calcMaxRefund()
    {
        $sum = 0;
        foreach ($this->transactions as $tx) {
            if ($tx['transaction_type'] != 'chargePermission' && $tx['transaction_type'] != 'refund_declined') {
                $sum+= $tx['transaction_amount'];
            }
        }
        return $sum;
    }

    public function isChargeAllowed()
    {
        foreach ($this->transactions as $tx) {
            if (in_array($tx['transaction_type'], ['refund', 'cancel', 'close', 'capture'])) {
                return false;
            }
        }
        return true;
    }

    public function isRefundAllowed()
    {
        if ($this->summary['max_refund'] <= 0) {
            return false;
        } else {
            foreach ($this->transactions as $tx) {
                if ($tx['transaction_type'] == 'cancel' || $tx['transaction_type'] == 'close') {
                    return false;
                }
            }
        }
        foreach ($this->transactions as $tx) {
            if ($tx['transaction_type'] == 'capture') {
                return true;
            }
        }
        return false;
    }

    public function isCancelAllowed()
    {
        foreach ($this->transactions as $tx) {
            if (in_array($tx['transaction_type'], ['cancel', 'close', 'capture'])) {
                return false;
            }
        }
        return true;
    }

    public function isCloseAllowed()
    {
        foreach ($this->transactions as $tx) {
            if (in_array($tx['transaction_type'], ['close'])) {
                return false;
            }
        }
        return true;
    }
}
