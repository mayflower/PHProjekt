<?php

interface Phprojekt_IModel
{
    public function find();
    public function fetchAll();
    public function save();
    public function getSubModules();
}