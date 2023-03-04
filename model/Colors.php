<?php

class Colors {

    private $db;

    /**
     * Instantiates a new colors object
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Returns color preferences options
     * 
     * @return type All color preference options
     */
    public function getColors() {
        $query = 'SELECT * FROM colors';
        $statement = $this->db->getDB()->prepare($query);
        $statement->execute();
        $colors = $statement->fetchAll();
        $statement->closeCursor();
        return $colors;
    }

}

?>