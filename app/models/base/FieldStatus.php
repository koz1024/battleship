<?php
namespace app\models\base;

class FieldStatus{
    
    /* initial statuses */
    const NOT_IN_RANGE  = -1;
    const FREE          = 0;
    const SHIP          = 1;
    const BORDER        = 2;
    
    /* game status */
    const HIT           = 3;
    
}
