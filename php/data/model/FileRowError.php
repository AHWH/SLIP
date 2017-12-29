<?php
/**
 * Created by PhpStorm.
 * User: weiho
 * Date: 18/12/2017
 * Time: 5:19 PM
 */
namespace IS203\data\model;

class FileRowError
{
    private $lineNo;
    private $errors;

    /**
     * FileRowError constructor. Constructor is overloaded in the traditional sense!
     */
    public function __construct()
    {
        //Argument order is always: $lineNo, $cause/$errors, $reason
        $this->lineNo = func_get_arg(0);
        if(func_num_args() === 2) {
            $this->errors = func_get_arg(1);
        } else if(func_num_args() === 3) {
            $this->errors = array(func_get_arg(1) => func_get_arg(2));
        }
    }

    /**
     * @return mixed
     */
    public function getLineNo()
    {
        return $this->lineNo;
    }

    /**
     * @param mixed $lineNo
     */
    public function setLineNo($lineNo)
    {
        $this->lineNo = $lineNo;
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $cause
     * @param $reason
     */
    public function setCause($cause, $reason)
    {
        $errors[$cause] = $reason;
    }


}