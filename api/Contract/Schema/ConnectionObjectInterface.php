<?php

namespace Everywhere\Api\Contract\Schema;

interface ConnectionObjectInterface
{
    public function getRoot();
    public function getItems($arguments = null);
    public function getCount();
    public function getArguments();
}
