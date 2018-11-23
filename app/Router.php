<?php
/**
 * Simple Router.
 * May be extended, so that's why class used.
  */
namespace app;

class Router{
    
    public static function route(){
        if (isset($_GET['action'])){
            $game = new controllers\Game();
            switch ($_GET['action']){
                case 'start':
                    $response = $game->start();
                    break;
                case 'hit':
                    $response = $game->userTurn();
                    break;
 
                default:
                    $response = [];
            }
            echo json_encode($response);
        }else{
            include('views/screen.php');
        }
    }
}
