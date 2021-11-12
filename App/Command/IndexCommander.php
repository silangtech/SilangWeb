<?php
namespace App\Command;


class IndexCommander
{
    public $input;
    public function Index()
    {
        echo "run index->cli command";
        var_dump($this->input);
    }
}