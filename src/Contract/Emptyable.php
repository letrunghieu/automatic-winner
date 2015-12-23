<?php

namespace HieuLe\MongoODM\Contract;

interface Emptyable
{
    public function reset();

    public function isEmpty();
}