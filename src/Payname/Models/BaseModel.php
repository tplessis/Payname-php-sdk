<?php
namespace Payname\Models;

/**
 * Generic Model class
 */
class BaseModel
{
    protected $properties = array();

    public function __construct()
    {
        $this->properties = [];
    }

    /**
     * Converts Params to Array
     *
     * @param $param
     * @return array
     */
    private function convertToArray($param)
    {
        $ret = array();
        foreach ($param as $k => $v) {
            if ($v instanceof BaseModel) {
                $ret[$k] = $v->toArray();
            } else if (sizeof($v) <= 0 && is_array($v)) {
                $ret[$k] = array();
            } else if (is_array($v)) {
                $ret[$k] = $this->convertToArray($v);
            } else {
                $ret[$k] = $v;
            }
        }

        // Array is empty, convert it to a new empty Base class
        if (sizeof($ret) <= 0) {
            $ret = new BaseModel();
        }

        return $ret;
    }

    /**
     * Returns array representation of object
     *
     * @return array
     */
    public function toArray()
    {
        return $this->convertToArray($this->properties);
    }

}
