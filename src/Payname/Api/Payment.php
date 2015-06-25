<?php
namespace Payname\Api;

class Payment extends ApiCall
{
    /**
     * Create a payment
     *
     *
     * @return \Payname\Transport\Response
     */
    public function create()
    {
        $payLoad = $this->model->toArray();

        $response = $this->executeCall(
            "/payment",
            "POST",
            $payLoad
        );

        return $response;
    }

    /**
     * Confirm a simple payment
     *
     *
     * @return \Payname\Transport\Response
     */
    public function confirm()
    {
        $payLoad = [];
        $payLoad['action'] = 'confirm';
        $payLoad['datas'] = [];
        $payLoad['datas']['order_id'] = $this->model->getOrderId();

        $response = $this->executeCall(
            "/payment",
            "PUT",
            $payLoad
        );

        return $response;
    }

}
