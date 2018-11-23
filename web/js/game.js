var game = {
    /* statuses: ['HEALTY', 'HIT', 'SUNK', 'MISS'],*/
    init: function(){
        $('#result').css('display', 'none');
        $.ajax({
            url: '?action=start',
            type: 'GET',
            dataType: 'JSON',
            success: function(data){
                var enemyField = game.getEmptyField(10, 10);
                var userField  = game.getFilledField(data);
                $('#enemyField').html(enemyField);
                $('#userField').html(userField);
                
                //css fix
                $('.field').css('height', $('.field').css('width'));
                
                $('#enemyField .field').on('click', game.click);
            },
            error: function(){
                alert('Cannot start the game. Server error. Try later');
            }
        })
    },
    getEmptyField: function(m, n){
        var html = '';
        for (var i = 0; i < m; i++){
            for (var j = 0; j < n; j++){
                html += '<div class="field" data-y="'+i+'" data-x="'+j+'"></div>';
            }
        }
        return html;
    },
    getFilledField: function(data){
        var html = '';
        for (var i in data){
            for (var j in data[i]){
                html += '<div class="field ship_'+data[i][j]+'" data-y="'+i+'" data-x="'+j+'"></div>';
            }
        }
        return html;
    },
    click: function(e){
        if (queue.hasDone()){
            $(e.target).off('click');
            var x = $(e.target).data('x');
            var y = $(e.target).data('y');
            //console.log(x + ':' + y);
            $.ajax({
                url: '?action=hit&x=' + x + '&y=' + y,
                type: 'GET',
                dataType: 'JSON',
                success: function(data){
                    //console.log(data);
                    game.userTurnResult(data, x, y);
                },
                error: function(){
                    alert('Server error');
                }
            })
        }else{
            console.warn('Queue is not empty!');
        }
    },
    userTurnResult: function(data, x, y){
        switch (data.status){
            case 1:
                game.message('You hit enemy\'s ship! Your turn');
                $('#enemyField .field[data-x='+x+'][data-y='+y+']').addClass('ship_1');
                break;
            case 2:
                game.message('Enemy\'s ship was sunk! Your turn');
                $('#enemyField .field[data-x='+x+'][data-y='+y+']').addClass('ship_2');
                break;
            case 3:
                game.message('You missed!');
                $('#enemyField .field[data-x='+x+'][data-y='+y+']').addClass('ship_3');
                game.aiTurn(data.AI);
                break;
            case 4:
                game.message('You won!');
                $('#enemyField .field[data-x='+x+'][data-y='+y+']').addClass('ship_2');
                $('#enemyField .field').off('click');
            default:
        }
    },
    aiTurn: function(data){
        //console.log(data);
        for (var i in data){
            queue.add((function(datarow){
                game.aiTurnResult(datarow);
            }).bind(this, data[i]))
        }
        queue.start();
    },
    aiTurnResult: function(data){
        //console.log(data)
        switch (data.status.status){
            case 1:
                game.message('Your ship in flame!');
                break;
            case 2:
                game.message('Your ship was sunk!');
                break;
            case 3:
                game.message('Your enemy missed!');
                break;
            case 4:
                game.message('Your enemy won!');
                $('#enemyField .field').off('click');
            default:
        }
        $('#userField .field[data-x='+data.x+'][data-y='+data.y+']').addClass('ship_s_' + data.status.status);
    },
    message: function(message){
        $('#result').html(message);
        $('#result').css('display', 'flex');
    }
}
var queue = {
    _queue: [],

    add: function(func){
        this._queue.push(func);
    },
    start: function(){
        if (!this.hasDone()){
            setTimeout(function(){
                if (queue._queue.length > 0){
                    var f = queue._queue.shift();
                    if (typeof(f) === 'function'){
                        f();
                    }
                    queue.start();
                }
            }, 1000);
        }
    },
    hasDone: function(){
        return (this._queue.length==0);
    }
}
$(document).ready(function(){
    
    $('#start').on('click', game.init);
    
    $(window).resize(function(){
        //css fix
        $('.field').css('height', $('.field').css('width'));
    })
})