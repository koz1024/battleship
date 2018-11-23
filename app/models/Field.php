<?php
namespace app\models;

use \app\models\base\FieldStatus;
use \app\models\base\ShipStatus;

class Field extends base\Field{

    protected $ships    = [];
    protected $shipCnt  = 0;
    private   $shipMap  = [];
    
    /**
     * Sets ship on the field
     * @param \app\models\base\Ship $ship
     */
    public function setShip(base\Ship $ship){
        $coords = $ship->getShip();
        foreach ($coords as $coord){
            
            parent::setShip($coord[0], $coord[1]);
            
            //for caching purpose
            $this->shipMap[$coord[0].'.'.$coord[1]] = $this->shipCnt;
        }
        $this->ships[$this->shipCnt++] = [
            'status' => ShipStatus::HEALTHY,
            'coords' => $coords,
        ];

    }
    
    /**
     * Checks if wasn't shots on passed coords yet
     * @param int $x
     * @param int $y
     * @return boolean
     */
    public function wasHit($x, $y){
        return ($this->matrix[$y][$x] == FieldStatus::HIT);
    }
    
    /**
     * Returns status of hit ship by passed coords
     * @param int $x
     * @param int $y
     * @return \app\models\base\ShipStatus
     */
    public function getShipStatus($x, $y){
        if ($this->wasHit($x, $y)){
            $shipIndex = $this->shipMap[$x.'.'.$y];
            return $this->ships[$shipIndex]['status'];
        }else{
            return ShipStatus::MISS;
        }
    }
    
    /**
     * Returns hit part of ship
     * @param int $x
     * @param int $y
     * @return array of coords
     */
    public function getHitPartShip($x, $y){
        $return = [];
        if ($this->wasHit($x, $y)){
            $shipIndex = $this->shipMap[$x.'.'.$y];
            $ship = $this->ships[$shipIndex]['coords'];
            foreach ($ship as $coord){
                if ($this->matrix[$coord[1]][$coord[0]] == FieldStatus::HIT){
                    $return[] = [$coord[0], $coord[1]];
                }
            }
        }
        return $return;
    }
    
    /**
     * Makes a hit (marks in internal structures)
     * and returns a status of hit.
     * 
     * @param int $x
     * @param int $y
     * @return array
     */
    public function hit($x, $y) {
        if ($this->matrix[$y][$x] == FieldStatus::SHIP){
            $shipIndex = $this->shipMap[$x.'.'.$y];
            $isSunk = true;
            foreach ($this->ships[$shipIndex]['coords'] as $coord){
                if ($coord[0] == $x && $coord[1] == $y) continue;
                $isSunk = $isSunk && $this->wasHit($coord[0], $coord[1]);
            }
            if ($isSunk){
                $this->ships[$shipIndex]['status'] = ShipStatus::SUNK;
                $isOver = true;
                foreach ($this->ships as $ship){
                    $isOver = $isOver && ($ship['status'] == ShipStatus::SUNK);
                }
                $return = ($isOver) ? ['status' => ShipStatus::NO_SHIPS] : $this->ships[$shipIndex];
            }else{
                $this->ships[$shipIndex]['status'] = ShipStatus::HIT;
                $return = ['status' => ShipStatus::HIT];
            }
        }else{
            $return = ['status' => ShipStatus::MISS];
        }
        $this->matrix[$y][$x] = FieldStatus::HIT;
        return $return;
    }
    
    /**
     * Returns saving of game's state.
     * (standard magic method isn't appropriate here)
     * @return array
     */
    public function serialize() {
        return ['field' => $this->matrix, 'ships' => $this->ships, 'shipMap' => $this->shipMap];
    }
    
    
    /**
     * Restores a game's state by json
     * @param string $json
     */
    public function wakeup($json){
        $data = json_decode($json);
        $this->matrix   = $data->field;
        $this->shipMap  = (array)$data->shipMap;
        foreach ($data->ships as $ship){
            $this->ships[] = (array)$ship;
        }
    }
}
