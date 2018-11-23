<?php
namespace app\models\base;

abstract class Field{
    
    protected $matrix = [];
    
    protected $height = 10;
    protected $width  = 10;
    
    public function __construct($x = 10, $y = 10){
        $this->width    = $x;
        $this->height   = $y;
        
        $this->matrix   = array_fill(0, $this->width, []);
        foreach ($this->matrix as &$row){
            $row = array_fill(0, $this->height, FieldStatus::FREE);
        }
    }
    
    /**
     * Returns a status of the cell
     * @param int $x
     * @param int $y
     * @return \app\models\base\FieldStatus
     */
    public function check($x, $y) {
        return (isset($this->matrix[$y]) && isset($this->matrix[$y][$x])) 
                ? $this->matrix[$y][$x] 
                : FieldStatus::NOT_IN_RANGE;
    }
    
    /**
     * Returns a dimension of field
     * 
     * @return array coord
     */
    public function getDimensions(){
        return [$this->height, $this->width];
    }
    
    /**
     * Checks if passed coord is in or out of field range
     * @param int $x
     * @param int $y
     * @return boolean
     */
    public function isInRange($x, $y){
        return (isset($this->matrix[$y]) && isset($this->matrix[$y][$x]));
    }
    
    /**
     * Sets DOT of ship on the field
     * @param int $x
     * @param int $y
     * @throws \Exception
     */
    protected function setShip($x, $y) {
        if (isset($this->matrix[$y]) && isset($this->matrix[$y][$x])){
            
            $this->matrix[$y][$x] = FieldStatus::SHIP;
            
            //set "border"
            for ($xi = $x-1; $xi <= $x+1; $xi++){
                for ($yi = $y - 1; $yi <= $y + 1; $yi++){
                    if ($xi >= 0 && $yi >= 0 && $xi < $this->width && $yi < $this->height && $this->matrix[$yi][$xi] !== FieldStatus::SHIP){

                        $this->matrix[$yi][$xi] = FieldStatus::BORDER;

                    }
                }
            }
        }else{
            throw new \Exception("Cannot set ship at " . $x . ':' . $y . " - ");
        }
    }
    
    abstract function hit($x, $y);
    
    /**
     * Returns of the field
     * @return array of array (field)
     */
    public function getField(){
        return $this->matrix;
    }
    
}
