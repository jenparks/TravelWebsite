<?php

class Tours {

    private $db;

    /**
     * Instantiates a new tours object
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Returns array of all available tours
     * @return type List of all tours
     */
    public function getTours() {
        $query = 'SELECT * FROM tours';
        $statement = $this->db->getDB()->prepare($query);
        $statement->execute();
        $tours = $statement->fetchAll();
        $statement->closeCursor();
        return $tours;
    }

    /**
     * Returns array of all available tours filtered by region
     * @return type List of all tours by region
     */
    public function getToursByRegion($idRegion) {
        $query = 'SELECT * FROM tours 
                  WHERE idRegion = :idRegion';
        $statement = $this->db->getDB()->prepare($query);
        $statement->bindValue(':idRegion', $idRegion);
        $statement->execute();
        $tours = $statement->fetchAll();
        $statement->closeCursor();
        return $tours;
    }

}

?>