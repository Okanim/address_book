<?php

/**
 * Class Produits
 * Classe qui gère les données liées aux produits
 */
class Adresse {
    public $id, $lastName, $firstName, $city, $phone, $code, $address;
    private $bd, $table;

    public function __construct(Database $db){
        $this->db = $db;
        $this->table = 'personnes';
        $this->id = null;
        $this->lastName = null;
        $this->firstName = null;
        $this->city = null;
        $this->phone = null;
        $this->code = null;
        $this->address = null;
    }

    public function findAll(){
        return $this->db->findAll($this->table);
    }

   


    public function save($new){
        $data = array('lastname' => $this->lastName,
                      'firstname' => $this->firstName,
                      'city' => $this->city,
                      'phone' => $this->phone,
                      'code' => $this->code,
                      'address' => $this->address);
        if($new){
            $this->db->insert($this->table, $data);
        }
        else{
            $query = $this->db->getBd()->prepare("UPDATE $this->table SET lastname = :lastname,
                                                  firstname = :firstname, city = :city, phone = :phone,
                                                  code = :code, address= :address
                                                  WHERE id = $this->id");
            $query->execute($data);
        }
    }

    public function delete(){
        return $this->db->delete($this->table, "id = $this->id");
    }
} 