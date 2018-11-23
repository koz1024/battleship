<?php
namespace app\models\base;

use \app\models\base\FieldStatus as Status;

abstract class Ship {
    
    protected $ship     = [];
    
    protected $shape    = [];
    
    protected $rotation = [];
    protected $startX   = 0;
    protected $startY   = 0;
    
    public function __construct(Field $field) {
        do{
            $this
                ->setRandomPosition($field)
                ->setRandomRotation()
                ->buildShip();
            
        }while(!$this->isFieldAvailable($field));
    }
    
    /**
     * Checks if there is place for ship
     * 
     * @param \app\models\base\Field $field
     * @return boolean
     */
    protected function isFieldAvailable(Field $field) {
        $flag = true;
        foreach ($this->ship as $coord){
            if ($field->check($coord[0], $coord[1]) != Status::FREE){
                $flag = false;
            }
        }
        return $flag;
    }
    
    /**
     * Sets a rotation of the ship
     * 
     * @return \app\models\base\Ship
     */
    protected function setRandomRotation(){
        mt_srand(microtime(true));
        $rotations = [[1, 1], [-1, 1], [1, -1], [-1, -1]];
        $this->rotation = $rotations[mt_rand(0, 3)];
        return $this;
    }
    
    /**
     * Sets a initial position of the ship (random)
     * @param \app\models\base\Field $field
     * @return \app\models\base\Ship
     */
    protected function setRandomPosition(Field $field) {
        $dim = $field->getDimensions();
        mt_srand();
        do{
            $this->startY = mt_rand(0, $dim[0]-1);
            $this->startX = mt_rand(0, $dim[1]-1);
        }while($field->check($this->startX, $this->startY));
        return $this;
    }
    
    /**
     * Sets a initial position of the ship (by passed coords)
     * @param int $x
     * @param int $y
     */
    public function setPosition($x, $y) {
        $this->startX = $x;
        $this->startY = $y;
    }
    
    /**
     * Returns an array of coords of the ship
     * @return array
     */
    public function getShip(){
        return $this->ship;
    }
    
    private function buildShip(){
        $this->ship = [];
        //it returns real random boolean:
        $isRotated = (ord(substr(md5(microtime()), 0, 1)) % 2) == 0;
        foreach ($this->shape as $coord){
            if ($isRotated){
                $this->ship[] = [
                    $this->startX + $coord[0]*$this->rotation[0], 
                    $this->startY + $coord[1]*$this->rotation[1], 
                ];
            }else{
                $this->ship[] = [
                    $this->startY + $coord[1]*$this->rotation[1],
                    $this->startX + $coord[0]*$this->rotation[0], 
                ];
            }
        }
    }
}
