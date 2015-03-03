<?php

/*
 * Class Database
 * Elle permet d'avoir des accès simplifier à la base de données
 */
class Database {
	private $bd;
    private $host, $database, $username, $password;

	public function __construct($host, $database, $username, $password){
        $this->host = $host;
        $this->database = $database;
        $this->username = $username;
        $this->password = $password;

	}


    //On utilise l'objet PDO, l'avantage de celui ci c'est que lorsque nos requête sont préparé
    //Et que les valeurs sont bind, il y a une protection contre les INJECTION SQL
    //Mais aussi qu'il peut communiquer avec d'autre SGBD que MySQL
    public function connect(){
        $this->bd = new PDO('mysql:host='.$this->host.';dbname='.$this->database, $this->username, $this->password);
    }

	public function findAll($table){
        $result =  $this->bd->query("SELECT * FROM $table");
        $data = new stdClass();
        while($d = $result->fetchObject()){
        	$id = $d->id;
            $data->$id = $d;
        }
        $result->closeCursor();
        return $data;
	}

	public function find($table, array $options){
        $fields = (isset($options['field']))?$options['field']:'*';
        $condition = (isset($options['condition']))?$options['condition']:null;
        $cdtString = '';
        $dataCondition = array();
        if(!empty($condition)) {
            foreach ($condition as $k => $c) {
                $cdtString = $cdtString .$k.' = :' . $k . ' AND ';
                $dataCondition[$k] = $c;
            }
            $cdtString = substr($cdtString, 0, (strlen($cdtString)- 4));
        }
        $order = (isset($options['order']))?$options['order']:null;
        $limit = (isset($option['limit']))?$options['limit']:null;
        if(empty($limit) && empty($condition) && empty($order)){
            $result = $this->bd->prepare("SELECT $fields FROM $table");
        }
        elseif(empty($limit) && !empty($order) && empty($condition)){
            $result = $this->bd->prepare("SELECT $fields FROM $table ORDER BY$order");
        }
        elseif(empty($limit) && !empty($condition) && empty($order)){
            $result = $this->bd->prepare("SELECT $fields FROM $table WHERE $cdtString");
        }
        elseif(!empty($limit) && empty($condition) && empty($order)){
            $result = $this->bd->prepare("SELECT $fields FROM $table LIMIT $limit");
        }
        elseif(!empty($limit) && !empty($order) && empty($condition)){
            $result = $this->bd->prepare("SELECT $fields FROM $table ORDER BY $order LIMIT $limit");
        }
        elseif(!empty($limit) && !empty($condition) && empty($order)){
            $result = $this->bd->prepare("SELECT $fields FROM $table WHERE $cdtString LIMIT $limit");
        }
        elseif(!empty($condition) && !empty($order) && empty($limit)){
            $result = $this->bd->prepare("SELECT $fields FROM $table WHERE $cdtString ORDER BY $order");
        }
        elseif(!empty($condition) && !empty($order) && !empty($limit)){
            $result = $this->bd->prepare("SELECT $fields FROM $table WHERE $cdtString ORDER BY $order LIMIT $limit");
        }
        if(!empty($condition)){
            $result->execute($dataCondition);
        }
        else {
            $result->execute();
        }
        $data = new stdClass();
        $cpt = 0;
        $count = $result->rowCount();
        while($d = $result->fetchObject()){
            $data->$cpt =$d;
            $cpt++;
        }
        $result->closeCursor();
        $r = array('data' => $data,
                   'count' => $count);
        return $r;
	}

    public function insert($table, array $data){
        $column = '';
        $value = '';
        foreach($data as $k=>$d){
            $column = $column.$k.', ';
            $value = $value.':'.$k.',';
        }
        $column = substr($column, 0, (strlen($column)-2));
        $value = substr($value, 0, (strlen($value)-1));
        $query = "INSERT INTO $table($column) VALUES($value)";

        $result = $this->bd->prepare($query);
        $result->execute($data) or die('erreur');
        return $result;
    }

    public function sanitize_string($string){

        return htmlentities($string);
    }


    public function delete($table, $condition){
        if(!empty($condition)) {
            return $result = $this->bd->query("DELETE FROM $table WHERE $condition");
        }
    }

    public function getBd(){
        return $this->bd;
    }
    //Fonction qui est appelé lors de la seralization de l'objet
    //Comme il est impossible d'enregistrer l'objet PDO, du au fait
    //qu'il existe des liens constant avec la base de donnée, nous modifions
    //la serialization et nous enregistrons que l'host, database, username, et password
    public function __sleep(){
        return array('host', 'database', 'username', 'password');
    }

    //Fonction qui est appelé lors de la deserialization (unseralize) de l'objet
    //Lorsque nous retrouvons notre objet dans cet état, nous retablissons la connection
    //En appelant l'objet PDO;
    public function __wakeup(){
        $this->connect();
    }
}