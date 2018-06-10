<?php
/**
 * Created by PhpStorm.
 * User: gabriel
 * Date: 23/03/2018
 * Time: 19:12
 */
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Openpaddle</title>
    <link rel="stylesheet" href="assets/css/screen.css">
    <link href="https://fonts.googleapis.com/css?family=Overpass:200,400,900" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Wallpoet" rel="stylesheet">
    <script src="assets/js/easytimer.min.js"></script>
    <script src="assets/js/jquery.min.js"></script>

    <script>
        function startTime() {
            var today = new Date();
            var h = today.getHours();
            var m = today.getMinutes();
            var s = today.getSeconds();
            m = checkTime(m);
            s = checkTime(s);
            document.getElementById('time').innerHTML =
                h + ":" + m + ":" + s;
            var t = setTimeout(startTime, 500);
        }
        function checkTime(i) {
            if (i < 10) {i = "0" + i};  // add zero in front of numbers < 10
            return i;
        }
    </script>

    <script src="assets/js/vue.js"></script>
    <script src="assets/js/axios.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body onload="startTime()">

<header>
    <div class="logo"><img src="assets/images/logo.png" alt="logo" height="70"></div>
    <div id="time"></div>
</header>

<section class="player" id="player">
    <div class="button" id="button-player">New player </div>
    <div class="button-back">back</div>
    <div class="form">
        <input type="text" placeholder="Nickname" v-model="nickname">
        <button v-on:click="createPlayer"> ok </button>
    </div>
</section>


<section class="game" id="game">
    <div class="button" id="button-game">New game</div>
    <div class="button-back" v-on:click="clearEverything">back</div>
    <div class="form">
        <input type="number" placeholder="Player 1" class="input-p1" v-model="idP1" min="1">
        <span class="paddle-p1"></span>
        <span class="paddle-p2"></span>
        <input type="number" placeholder="Player 2" class="input-p2" v-model="idP2" min="1">
        <button type="submit" id="go-game" v-on:click="launchGame"> GO ! </button>


        <div id="timer">
            <div class="values"></div>
        </div>
        <i class="who-won" style="display: none">Who won?</i>                      
        <button id="win-p1" v-on:click="endGame(1)" v-if="gameLaunched">{{player1}}</button>           
        <button id="win-p2" v-on:click="endGame(2)" v-if="gameLaunched">{{player2}}</button>           
    </div>
</section>


<section class="ranking" id="ranking">

    <div class="button" id="button-rank" v-on:click="fetchRanking">Ranking</div>
    <div class="button-back-2">back</div>
    <div class="ranking-container">
        <table>
            <thead>
            <tr>
                <th>Id</th>
                <th>Nickname</th>
                <th>Elo Rating</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="player in players">
                <td>{{player.player_id}}</td>
                <td>{{player.player_name}}</td>
                <td>{{player.player_elo}}</td>
            </tbody>
        </table>
    </div>

</section>

</body>

<script>
    var baseurl = 'https://openpaddle.test/';
</script>
<script>
    var vueRanking = new Vue({
        el: "#ranking",
        data: {
            players: {},
            loaded: false
        },
        methods: {
            fetchRanking: function () {
                axios.get(baseurl + 'api/fetch/players/rank')
                    .then(response => {
                        this.players = response.data;
                        this.loaded = true;
                })
            }

        }
    });
</script>

<script>
    var addPlayer = new Vue({
        el: "#player",
        data: {
            nickname: ""
        },
        methods: {
            createPlayer: function ()
            {
                axios.get(baseurl + 'api/create/player/'+this.nickname)
                    .then(response => {
                        if (response.data.result === 'success')
                        {
                            swal({
                                title: "Welcome, " + response.data.name + "! Your number is " + response.data.id+".",
                                icon: "success",
                                button: "Awesome!"
                            });
                            this.nickname = "";
                            $(".button-back").click();
                        }
                        else if (response.data.result === 'error')
                        {
                            swal({
                                title: "Error",
                                text: response.data.message,
                                icon: "error"
                            });
                            this.nickname = "";
                        }
                })
            }
        }
    });
</script>

<script>
    var game = new Vue({
        el: "#game",
        data: {
            idGame: null,
            idP1: null,
            idP2: null,
            gameLaunched: false,
            player1: null,
            player2: null
        },
        methods: {
            launchGame: function()
            {
                if ((this.idP1 == null || this.idP1.length < 1) || (this.idP2 == null || this.idP2.length < 1))
                {
                    swal({
                        title: "Error",
                        text: "Please specify two players to start a game!",
                        icon: "error"
                    });
                    return false;
                }

                if (this.idP1 == this.idP2)
                {
                    swal({
                        title: "Error",
                        text: "You can't create a game with two identical players!",
                        icon: "error"
                    });
                    this.player1 = null;
                    this.player2 = null;
                    return false;
                }

                axios.get(baseurl + 'api/fetch/player/'+this.idP1)
                    .then(response => {
                        if (typeof response.data.player_name != 'undefined')
                        {
                            this.player1 = response.data.player_name;
                            console.log("loaded player id "+this.idP1+" - "+this.player1);


                            axios.get(baseurl + 'api/fetch/player/'+this.idP2)
                                .then(response => {
                                    if (typeof response.data.player_name != 'undefined')
                                    {
                                        this.player2 = response.data.player_name;
                                        console.log("loaded player id "+this.idP2+" - "+this.player2);


                                        if ((this.player1 != null && this.player2 != null) && (this.idP1 !== this.idP2))
                                        {
                                            axios.get(baseurl + 'api/create/game/'+this.idP1+'/'+this.idP2)
                                                .then(response => {
                                                    if (response.data.result == "success")
                                                    {
                                                        this.idGame = response.data.gameId;
                                                        launchGame();
                                                        console.log('game id ' + this.idGame +' was launched');
                                                        this.gameLaunched = true;
                                                    }
                                                    else
                                                    {
                                                        swal({
                                                            title: "Error",
                                                            text: response.data.message,
                                                            icon: "error"
                                                        });
                                                    }
                                                });
                                        }
                                    }
                                    else
                                    {
                                        this.idP2 = null;
                                        swal({
                                            title: "Error",
                                            text: "Couldn't load player id "+this.idP2+".\nDo they exist in the database?",
                                            icon: "error"
                                        });
                                    }
                                });
                        }
                        else
                        {
                            this.idP1 = null;
                            swal({
                                title: "Error",
                                text: "Couldn't load player id "+this.idP1+".\nDo they exist in the database?",
                                icon: "error"
                            });
                        }
                    });

            },
            endGame: function (winner)
            {
                switch (winner)
                {
                    case 1:
                        axios.get(baseurl + 'api/endgame/'+this.idGame+'/1')
                            .then(response => {
                                if (response.data.result == "success")
                                {
                                    swal({
                                        title: this.player1+" is the winner!",
                                        text: "Data has been saved successfully",
                                        icon: "success"
                                    });
                                    $(".button-back").click();
                                }
                                else
                                {
                                    swal({
                                        title: "Error",
                                        text: response.data.message,
                                        icon: "error"
                                    });
                                }
                            });
                        break;

                    case 2:
                        axios.get(baseurl + 'api/endgame/'+this.idGame+'/2')
                            .then(response => {
                                if (response.data.result == "success")
                                {
                                    swal({
                                        title: this.player2+" is the winner!",
                                        text: "Data has been saved successfully",
                                        icon: "success"
                                    });
                                    $(".button-back").click();
                                }
                                else
                                {
                                    swal({
                                        title: "Error",
                                        text: response.data.message,
                                        icon: "error"
                                    });
                                }
                            });
                        break;
                }
            },
            clearEverything: function()
            {
                this.idGame = null;
                this.idP1 = null;
                this.idP2 = null;
                this.gameLaunched = false;
                this.player1 = null;
                this.player2 = null;
            }
        }


    })
</script>

<script type="text/javascript">
    var timer = new Timer();

    $('#button-player').click( function () {
        $('.player').addClass('player-active');
        setTimeout(function () {
            $('.player .form').addClass('form-enable');
        }, 200)
    });
    $('#button-game').click( function () {
        $('.game').addClass('game-active');
        setTimeout(function () {
            $('.game .form').addClass('form-enable');
        }, 200)
    });
    $('#button-rank').click( function () {
        $('.ranking').addClass('ranking-active');
    });

    function launchGame() {
        timer.start({precision: 'secondTenths'});
        timer.addEventListener('secondTenthsUpdated', function (e) {
            $('#timer .values').html(timer.getTimeValues().toString(['hours', 'minutes', 'seconds', 'secondTenths']));
        });
        $('.input-p1').hide();
        $('.input-p2').hide();
        $('.paddle-p1').addClass('paddle-p1-active');
        $('.paddle-p2').addClass('paddle-p2-active');
        setTimeout(function () {
            $('.paddle-p1, .paddle-p2').css('opacity','0');
        }, 300)
        setTimeout(function () {
            $('.game-active').addClass('game-running');
            $('#go-game').fadeOut(200);
            $('#timer').css('opacity', '1').css('top', '-50px');
            $('.who-won').fadeIn();
        }, 500)
    }

    $('.button-back').click( function () {
        $(this).parent().removeClass('player-active').removeClass('game-active');
        $('.player .form').removeClass('form-enable');
        $('.game .form').removeClass('form-enable');
        $('.game-running').removeClass('game-running');
        $('.paddle-p1').removeClass('paddle-p1-active');
        $('.paddle-p2').removeClass('paddle-p2-active');
        $('.paddle-p1, .paddle-p2').css('opacity','1');
        $('#go-game').fadeIn(200);
        $('.who-won').hide();
        $('.input-p1').show();
        $('.input-p2').show();
        timer.stop();
        $('#timer').css('opacity', '0').css('top', '-150px');
    });
    $('.button-back-2').click( function () {
        $(this).parent().removeClass('ranking-active');
    });
    $('#win-p1, #win-p2').click(function () {
        timer.stop();
    })
</script>


</html>