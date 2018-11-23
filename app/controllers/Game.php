<?php
namespace app\controllers;

use \app\models\Field;
use \app\models\LShip;
use \app\models\IShip;
use \app\models\DotShip;

use \app\models\base\ShipStatus;

class Game{
    
    protected $fieldAI;
    protected $fieldUser;
    
    function __construct(){
        session_start();
        
        $this->fieldAI      = new Field();
        $this->fieldUser    = new Field();
        
    }
    
    function restore(){
        $this->fieldAI->wakeup($_SESSION['fieldAI']);
        $this->fieldUser->wakeup($_SESSION['fieldUser']);
    }
    
    function store(){
        $_SESSION['fieldAI'] = json_encode($this->fieldAI->serialize());
        $_SESSION['fieldUser'] = json_encode($this->fieldUser->serialize());
    }
    
    function start(){
        
        $this->fieldAI->setShip(new LShip($this->fieldAI));
        $this->fieldAI->setShip(new IShip($this->fieldAI));
        $this->fieldAI->setShip(new DotShip($this->fieldAI));
        $this->fieldAI->setShip(new DotShip($this->fieldAI));
        
        $this->fieldUser->setShip(new LShip($this->fieldUser));
        $this->fieldUser->setShip(new IShip($this->fieldUser));
        $this->fieldUser->setShip(new DotShip($this->fieldUser));
        $this->fieldUser->setShip(new DotShip($this->fieldUser));
        
        $this->store();
        
        $_SESSION['aiPreviousHits'] = [];
        $_SESSION['gameover'] = false;
        
        return $this->fieldUser->getField();
    }
    
    function userTurn(){
        if ($_SESSION['gameover']){
            return [];
        }
        $this->restore();
        $x = intval($_GET['x']);
        $y = intval($_GET['y']);
        
        $result = $this->fieldAI->hit($x, $y);
        $this->store();
        if ($result['status'] == ShipStatus::MISS){
            $steps = [];
            do{
                $r = $this->aiTurn();
                $steps[] = $r;
            }while($r['status']['status'] != ShipStatus::MISS && $r['status']['status'] !== ShipStatus::NO_SHIPS);
            return array_merge($result, ['AI' => $steps]);
        }else{
            return $result;
        }
    }
    
    function aiTurn(){
        $ai = new \app\models\AI();
        $ai->setField($this->fieldUser);
        if (isset($_SESSION['aiPreviousHits'])){
            $ai->setPreviousHits($_SESSION['aiPreviousHits']);
        }
        $coords = $ai->getHit();
        $result = $this->fieldUser->hit($coords[0], $coords[1]);
        if ($result['status'] == ShipStatus::HIT){
            $_SESSION['aiPreviousHits'][] = $coords;
        }
        if ($result['status'] == ShipStatus::NO_SHIPS){
            $_SESSION['gameover'] = true;
        }
        $this->store();
        return ['x' => $coords[0], 'y' => $coords[1], 'status' => $result];
    }
}
