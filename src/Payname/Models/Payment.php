<?php
namespace Payname\Models;

use Payname\Models\Card;

/**
 * Payment model class
 */
class Payment extends BaseModel
{
    public function __construct()
    {
        $this->properties['datas'] = [];
        $this->properties['datas']['general'] = [];
    }

    /**
     * Set payment amount
     *
     * @param double $amount Payment amount
     *
     * @return void
     */
    public function setAmount($_amount) {
        $this->properties['datas']['general']['amount'] = $_amount;
    }

    /**
     * Get payment amount
     *
     * @return double
     */
    public function getAmount() {
        if(isset($this->properties['datas']['general']['amount'])) {
            return $this->properties['datas']['general']['amount'];
        }

        return null;
    }

    /**
     * Set order ID
     *
     * @param int $_orderId Order ID associated to payment
     *
     * @return void
     */
    public function setOrderId($_orderId) {
        $this->properties['datas']['general']['order_id'] = $_orderId;
    }

    /**
     * Get order ID
     *
     * @return int
     */
    public function getOrderId() {
        if(isset($this->properties['datas']['general']['order_id'])) {
            return $this->properties['datas']['general']['order_id'];
        }

        return null;
    }

    /**
     * Set card
     *
     * @param \Payname\Models\Card $_card Credit card associated to payment
     *
     * @return void
     */
    public function setCard(Card $_card) {
        $this->properties['type'] = 'cb';
        $this->properties['datas']['card'] = $_card;
    }

    /**
     * Get card
     *
     * @return Card
     */
    public function getCard() {
        if(isset($this->properties['datas']['card'])) {
            return $this->properties['datas']['card'];
        }

        return null;
    }

}
