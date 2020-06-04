<?php


namespace org\helper;

/**
 * Class CSVHandler
 * @deprecated 已弃用
 * @package org\helper
 */
class CSVHandler
{
    protected $columns;
    public function __construct($columns=array())
    {
        $this->columns = $columns;
    }

    public function export(){

    }
}