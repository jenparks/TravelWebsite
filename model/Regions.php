<?php

class Regions {

    private $db;

    /**
     * Instantiates a new regions object
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Returns tour regions
     * 
     * @return type All color preference options
     */
    public function getRegions() {
        $query = 'SELECT * FROM regions';
        $statement = $this->db->getDB()->prepare($query);
        $statement->execute();
        $colors = $statement->fetchAll();
        $statement->closeCursor();
        return $colors;
    }

}

?>