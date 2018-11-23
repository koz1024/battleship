<?php
namespace app\models;

use \app\models\base\ShipStatus;

class AI{
    
    protected $field;
    protected $previousHits  = [];
    
    /**
     * setter for Field
     * @param \app\models\Field $field
     */
    public function setField(Field $field){
        $this->field = $field;
    }
    
    /**
     * setter for previous hist
     * @param array $hits
     */
    public function setPreviousHits($hits){
        $this->previousHits = $hits;
    }
    
    /**
     * make a hit
     * @return array coords
     */
    public function getHit(){
        if (sizeof($this->previousHits)>0){
            $lastHit = $this->previousHits[sizeof($this->previousHits)-1];
            $prevResult = $this->field->getShipStatus($lastHit[0], $lastHit[1]);
            if (!$prevResult || $prevResult == ShipStatus::SUNK || $prevResult == ShipStatus::MISS){
                return $this->getRandomHit();
            }else{
                return $this->getClosestHit();
            }
        }else{
            return $this->getRandomHit();
        }
    }
    
    /**
     * get possible coords of the rest of the last hit ship
     * @return array coords
     */
    protected function getClosestHit(){
        $coords  = $this->getRandomHit();
        $lastHit = $this->previousHits[sizeof($this->previousHits)-1];
        $ship    = $this->getLastShip();
        //get possible hits
        //(some overhead here, can be optimized)
        $possibleHits = [];
        if (sizeof($ship) == 1){
            //if only one shot on ship - then any direction is possible
            $possibleHits = [
                [$lastHit[0]-1, $lastHit[1]],
                [$lastHit[0], $lastHit[1]-1],
                [$lastHit[0]+1, $lastHit[1]],
                [$lastHit[0], $lastHit[1]+1]
            ];
        }elseif (sizeof($ship) == 2){
            //if 2 shots on ship: it could be also any direction because of L-shaped ship
            
            if ($ship[0][0] - $ship[1][0] == 0){
                //horizontal
                //doesn't matter, if possibleHist contains already-hitted cells, on next step we check if wasHit ;)
                $possibleHits = [
                    [$ship[0][0] - 1, $ship[0][1]],
                    [$ship[0][0], $ship[0][1] - 1],
                    [$ship[0][0], $ship[0][1] + 1],
                    [$ship[1][0] + 1 , $ship[1][1]],
                    [$ship[1][0], $ship[1][1] - 1],
                    [$ship[1][0], $ship[1][1] + 1],
                ];
            }else{
                //vertical
                $possibleHits = [
                    [$ship[0][0] - 1, $ship[0][1]],
                    [$ship[0][0] + 1, $ship[0][1]],
                    [$ship[0][0], $ship[0][1] - 1],
                    [$ship[1][0] -1 , $ship[1][1]],
                    [$ship[1][0] + 1, $ship[1][1]],
                    [$ship[1][0], $ship[1][1] + 1],
                ];
                
            }
        }else{
            //if 3 shots on ship - 4th can be on any adjacent cell of end of ship
            $lastShipsCells = self::getLastCells($ship);

            $possibleHits = [
                [$lastShipsCells[0][0]-1, $lastShipsCells[0][1]],
                [$lastShipsCells[0][0]+1, $lastShipsCells[0][1]],
                [$lastShipsCells[0][0], $lastShipsCells[0][1]-1],
                [$lastShipsCells[0][0], $lastShipsCells[0][1]+1],
                [$lastShipsCells[1][0]-1, $lastShipsCells[1][1]],
                [$lastShipsCells[1][0]+1, $lastShipsCells[1][1]],
                [$lastShipsCells[1][0], $lastShipsCells[1][1]-1],
                [$lastShipsCells[1][0], $lastShipsCells[1][1]+1],
            ];
        }

        //
        foreach ($possibleHits as $possibleHit){
            if (!$this->field->wasHit($possibleHit[0], $possibleHit[1]) && $this->field->isInRange($possibleHit[0], $possibleHit[1])){
                $coords = [$possibleHit[0], $possibleHit[1]];
                break;
            }
        }
        return $coords;
    }
    
    /**
     * get random position of field, where there wasn't shots yet
     * @return array coords
     */
    protected function getRandomHit(){
        $dimensions = $this->field->getDimensions();
        do{
            mt_srand();
            $y = mt_rand(0, $dimensions[0] - 1);
            $x = mt_rand(0, $dimensions[1] - 1);
        }while($this->field->wasHit($x, $y));
        return [$x, $y];
    }

    /**
     * get coords of last hit ship
     * @return array
     */
    protected function getLastShip(){
        $lastShot = $this->previousHits[sizeof($this->previousHits) - 1];
        return $this->field->getHitPartShip($lastShot[0], $lastShot[1]);
    }
    
    /**
     * get first and last cells of ship
     * only for 3-cell ships
     * @param array $ship
     * @return array
     */
    protected static function getLastCells($ship){
        $xadj = false;
        $yadj = false;
        $xes  = [];
        $yes  = [];
        $ret  = [];
        foreach ($ship as $cell){
            if (in_array($cell[0], $xes)){
                $xadj = $cell[0];
            }
            if (in_array($cell[1], $yes)){
                $yadj = $cell[1];
            }
            $xes[] = $cell[0];
            $yes[] = $cell[1];
        }
        if ($xadj && $yadj){
            foreach ($ship as $cell){
                if ($cell[0]!=$xadj || $cell[1] != $yadj){
                    $ret[] = $cell;
                }
            }
        }else{
            if ($xadj){
                $xcells = [];
                foreach ($ship as $cell){
                    if ($cell[0]==$xadj){
                        $xcells[] = $cell[1];
                    }
                }
                sort($xcells);
                $ret[] = [$xadj, $xcells[0]];
                $ret[] = [$xadj, $xcells[sizeof($xcells)-1]];
            }else{
                $ycells = [];
                foreach ($ship as $cell){
                    if ($cell[1]==$yadj){
                        $ycells[] = $cell[0];
                    }
                }
                sort($ycells);
                $ret[] = [$ycells[0], $yadj];
                $ret[] = [$ycells[sizeof($ycells)-1], $yadj];
            }
        }
        return $ret;
    }
    /*
    protected static function sortShipCells($ship){
        $return = [];
        $xes = [];
        $yes = [];
        $map = [];

        foreach ($ship as $i => $cell){
            if (!in_array($cell[1], $xes)){
                $xes[] = $cell[1];
            }
            if (!isset($yes[$cell[1]])){
                $yes[$cell[1]] = array();
            }
            if (!in_array($cell[0], $yes[$cell[1]])){
                $yes[$cell[1]][] = $cell[0];
            }
            $map[$cell[1]][$cell[0]] = $i;
        }
        sort($xes);
        foreach ($yes as &$y){
            sort($y);
        }

        foreach ($xes as $x){
            foreach ($yes[$x] as $y){
                $return[] = $ship[$map[$x][$y]];
            }
        }
        //end
        return $return;
    }*/
}
