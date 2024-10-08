<?php

namespace AscentCreative\Transact\Objects;

class ObjectBase {


    /**
     * Magic Attribute Setter
     * @param mixed $method
     * @param mixed $params=null
     * 
     * @return [type]
     */
    public function __call($method, $params=null) {
        // var_dump($params);
        if(!method_exists($this, $method)) {
            if(isset($params[0])) {
                $this->$method = $params[0];
            }
            return $this;
        }
    
    }

}