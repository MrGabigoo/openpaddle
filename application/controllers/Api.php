<?php
/**
 * Created by PhpStorm.
 * User: gabriel
 * Date: 16/03/2018
 * Time: 15:57
 */

class Api extends CI_Controller
{

    /**
     * API Usage
     *
     * prefix: /api/
     *
     * fetch specific player: /fetch/player/{id}
     * fetch all players: /fetch/players
     * fetch all players (sorted by rank): /fetch/players/rank
     *
     * fetch specific game: /fetch/game/{id}
     * fetch all games: /fetch/games
     * fetch all games (most recent first): /fetch/games/recent
     *
     * create player: /create/player/{name}
     * create game: /create/game/{player1_id}/{player2_id}
     *
     * conclude game: /conclude/{gameid}/{1|2}
     */

    public function __construct()
    {
        parent::__construct();
        $this->output->set_header('Content-Type: application/json; charset=utf-8');
    }

    public function index()
    {
        $this->error('Available API endpoints are: /fetch, /create, /search, /endgame');
    }

    public function fetch($what = null, $arg = null)
    {
        switch ($what)
        {
            case 'players':
                $this->load->model('Player_model');
                $players = $this->Player_model->fetch_players($arg);
                $result = json_encode($players);
                break;

            case 'player':
                $this->load->model('Player_model');
                if($arg == null) $this->error('Player ID must be specified');
                $player = $this->Player_model->fetch_player($arg)[0];
                $result = json_encode($player);
                break;

            case 'games':
                $this->load->model('Game_model');
                $games = $this->Game_model->fetch_games($arg);
                $result = json_encode($games);
                break;

            case 'game':
                $this->load->model('Game_model');
                if($arg == null) $this->error('Game ID must be specified');
                $game = $this->Game_model->fetch_game($arg)[0];
                $result = json_encode($game);
                break;

            default:
                $this->error('Available options for fetching are: game, games, player, players');
                break;
        }

        echo $result;
    }

    public function create($what = null, $arg1 = null, $arg2 = null)
    {
        switch($what)
        {
            case 'player':
                $name = urldecode($arg1); //Decode the url since the browser will have encoded some things like spaces and accents into url characters

                if (strlen(str_replace(' ', '', $name)) < 2)
                {
                    $this->error('Name must be at least 2 characters long');
                }

                $this->load->model('Player_model');
                $createPlayer = $this->Player_model->create_player($name);
                if($createPlayer != false)
                {
                    $result = json_encode(array('result' => 'success', 'name' => $name, 'id' => $createPlayer));
                }
                else
                {
                    $this->error('An error occurred while creating the player');
                }
                break;

            case 'game':

                $player1 = $arg1;
                $player2 = $arg2;

                if ($player1 === $player2) //if same IDs
                {
                    $this->error('Can\'t create a game with 2 identical players!');
                }

                $this->load->model('Game_model');

                $createGameResult = $this->Game_model->create_game($player1, $player2);

                if ($createGameResult[0])
                {
                    $result = json_encode(array('result' => 'success', 'gameId' => $createGameResult[1]));
                }
                else
                {
                    $this->error('An error occurred while creating the game');
                }
                break;

            default:
                $result = json_encode(array('Error'));
                break;
        }

        //header('Content-Type: application/json');
        echo $result;

    }

    public function search($what = null, $search = null)
    {
        $this->load->model('Player_model');

        switch ($what)
        {
            case 'player':
                $result = json_encode($this->Player_model->search($search));
                break;

            default:
                $this->error('Available options for search are: player');
                break;
        }

        //header('Content-Type: application/json');
        echo $result;

    }


    public function endgame($gameid = null, $winner = null) //game id, 1 or 2 (games each have a player 1 and a player 2)
    {
        if ($gameid == null) $this->error('Game ID must be specified');
        if ( !in_array($winner, array('1', '2')) ) $this->error('Winner must be player 1 or player 2');

        $this->load->model('Player_model');
        $this->load->model('Game_model');

        $game = $this->Game_model->fetch_game($gameid)[0];



        if ($game->game_concluded)
        {
            $this->error('Game has already been concluded');
        }

        $playerA = $this->Player_model->fetch_player($game->game_p1)[0];
        $playerB = $this->Player_model->fetch_player($game->game_p2)[0];


        $qA = (10^($playerA->player_elo / 400));
        $qB = (10^($playerB->player_elo / 400));

        $expectedA = $qA / ($qA + $qB); //expected score for player A
        $expectedB = $qB / ($qA + $qB); //expected score for player B

        if ($winner == 1) //if player A wins
        {
            $scoreA = 1;
            $scoreB = 0;
        }
        elseif ($winner == 2) //if player B wins
        {
            $scoreA = 0;
            $scoreB = 1;
        }

        $newRatingA = $playerA->player_elo + 32*($scoreA - $expectedA);
        $newRatingB = $playerB->player_elo + 32*($scoreB - $expectedB);

        if ($this->Player_model->update_rating($playerA->player_id, $playerB->player_id, $newRatingA, $newRatingB))
        {
            if ($this->Game_model->conclude($gameid))
            {
                $result = json_encode(array('result' => 'success', 'gameid' => $gameid, 'player1_rating' => $newRatingA, 'player2_rating' => $newRatingB));

            } else $this->error('Error updating game in database. Player ratings updated successfully');

        } else $this->error('Error updating player ratings. Please try again.');

        //$result = array($expectedA, $expectedB, $newRatingA, $newRatingB);

        echo $result;

    }

    private function error($msg) //this method is private so it can't just be accessed through /api/error
    {
        header('Content-Type: application/json');
        echo json_encode(array('result' => 'error', 'message' => $msg));
        die();
    }


}

