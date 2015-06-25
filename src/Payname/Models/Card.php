<?php
namespace Payname\Models;

/**
 * Card model class
 */
class Card extends BaseModel
{
    public function __construct()
    {
        $this->properties['expiry'] = [];
    }

    /**
     * Set expiration date
     *
     * @param int $month Expiry month
     * @param int $year Expiry year
     *
     * @return void
     */
    public function setExpiry($month, $year) {
        $this->properties['expiry']['month'] = $month;
        $this->properties['expiry']['year'] = $year;
    }

    /**
     * Get expiry
     *
     * @return array
     */
    public function getExpiry() {
        return $this->properties['expiry'];
    }

    /**
     * Set card number
     *
     * @param string $number Card number
     *
     * @return void
     */
    public function setNumber($number) {
        $this->properties['number'] = $number;
    }

    /**
     * Get card number
     *
     * @return string
     */
    public function getNumber() {
        if(isset($this->properties['number'])) {
            return $this->properties['number'];
        }

        return null;
    }

    /**
     * Set CVV
     *
     * @param string $cvv Credit card CVV
     *
     * @return void
     */
    public function setCvv($cvv) {
        $this->properties['cvv'] = $cvv;
    }

    /**
     * Get CVV
     *
     * @return string
     */
    public function getCvv() {
        if(isset($this->properties['cvv'])) {
            return $this->properties['cvv'];
        }

        return null;
    }

}
