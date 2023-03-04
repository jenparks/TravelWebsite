<?php

class Orders {

    private $db;

    /**
     * Instantiates a new orders object
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Places a new order
     */
    public function placeOrder($idCustomers, $idTour, $dateBooked) {
        $query = 'INSERT INTO orders (idCustomers, idTour, dateBooked)
              VALUES (:idCustomers, :idTour, :dateBooked)';
        $statement = $this->db->getDB()->prepare($query);
        $statement->bindValue(':idCustomers', $idCustomers);
        $statement->bindValue(':idTour', $idTour);
        $statement->bindValue(':dateBooked', $dateBooked);
        $statement->execute();
        $statement->closeCursor();
    }

    /**
     * Returns all orders from a customer
     * 
     * @param type $idCustomers
     * @return type All orders from one customer
     */
    public function getPreviousOrders($idCustomers) {
        $query = 'SELECT * FROM orders
              WHERE idCustomers = :idCustomers
              ORDER by dateBooked ASC';
        $statement = $this->db->getDB()->prepare($query);
        $statement->bindValue(':idCustomers', $idCustomers);
        $statement->execute();
        $userOrders = $statement->fetchAll();
        $statement->closeCursor();
        return $userOrders;
    }

}

?>