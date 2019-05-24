<?php

require_once(dirname(__FILE__) . '/../../../config/config.inc.php');

class StateOrder
{

    private $lang;

    public function __construct($lang)
    {

        $this->lang = $lang;
    }

    public function getStateOrderFromDb()
    {

        $sql = 'SELECT state.id_order_state as id, state.name as name 
                  FROM '._DB_PREFIX_.'order_state_lang as state 
                  WHERE state.id_lang = '.$this->lang;

        $results = Db::getInstance()->executeS($sql);

        return $results;
    }

    public function getValuesCheckboxBackOff()
    {

        $states = $this->getStateOrderFromDb();

        $arrayValues = [];

        foreach($states as $state){

            $arrayValues[] = [
                'check_id' => $state['id'],
                'name' => $state['name'],
                'val' => $state['id']
            ];
        }

        return $arrayValues;
    }

    public function getCountAllStatesDb()
    {

        $states = $this->getStateOrderFromDb();

        return count($states);
    }
}