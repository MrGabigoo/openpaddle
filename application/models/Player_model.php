<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Player_model extends CI_Model {

    public function __construct()
    {
        $this->load->database();
    }

    public function create_player($name)
    {
        $data = array(
            'player_name' => $name,
            'player_elo' => 50
        );
        if ($this->db->insert('op_players', $data)) return $this->db->insert_id();
        else return false;
    }

    public function fetch_players($sort)
    {
        if ($sort == 'rank') {
            $query = $this->db->order_by('player_elo', 'DESC')->get('op_players');
        }
        else {
            $query = $this->db->get('op_players');
        }

        return $query->result_object();
    }

    public function fetch_player($id)
    {
        $query = $this->db->where('player_id', $id)->get('op_players');
        return $query->result_object();
    }

    public function search($search)
    {
        $query = $this->db->like('player_name', $search)->get('op_players');
        return $query->result_object();
    }

    public function update_rating($p1, $p2, $new1, $new2)
    {
        $player1_data = array('player_elo' => $new1);
        $player2_data = array('player_elo' => $new2);

        $this->db->trans_start();

        $this->db->where('player_id', $p1)->update('op_players', $player1_data);
        $this->db->where('player_id', $p2)->update('op_players', $player2_data);

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE)
        {
            return false;
        }
        else
        {
            return true;
        }

    }

}