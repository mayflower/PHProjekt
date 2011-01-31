<?php

class Calendar2_Extension extends Phprojekt_Extension_Abstract
{
    public function getVersion()
    {
        return '6.1.0';
    }

    public function getMigration() {
        return new Calendar2_Migration();
    }
}
