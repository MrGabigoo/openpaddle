<?php
/**
 * Created by PhpStorm.
 * User: gabriel
 * Date: 16/03/2018
 * Time: 16:07
 */

class Game_model extends CI_Model
{

    public function __construct()
    {
        $this->load->database();
    }

    public function create_game($player1, $player2)
    {
        $data = array(
            'game_p1' => $player1,
            'game_p2' => $player2
        );
        if ($this->db->insert('op_games', $data)) return array(true, $this->db->insert_id());
        else return array(false);
    }

    public function fetch_games($arg = null)
    {
        if ($arg == 'recent')
        {
            $query = $this->db->order_by('game_timestamp', 'DESC')->get('op_games');
        }
        else
        {
            $query = $this->db->get('op_games');
        }
        return $query->result_object();
    }

    public function fetch_game($id)
    {
        $query = $this->db->where('game_id', $id)->get('op_games');
        return $query->result_object();
    }

    public function conclude($id)
    {
        if ( $this->db->where('game_id', $id)->update('op_games', array('game_concluded' => 1)) )
        {
            return true;
        }
        else return false;



    }

}