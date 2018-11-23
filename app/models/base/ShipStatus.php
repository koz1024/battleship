<?php
namespace app\models\base;

class ShipStatus{
    
    /* game statuses */
    const HEALTHY       = 0;
    const HIT           = 1;
    const SUNK          = 2;

    const MISS          = 3;
    const NO_SHIPS      = 4;
}
