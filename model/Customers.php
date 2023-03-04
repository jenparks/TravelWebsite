<?php

class Customers {

    private $db;

    /**
     * Instantiates a new customers object
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Adds the specified user to the table customers
     * 
     * @param type $username
     * @param type $password
     */
    public function addUser($firstName, $lastName, $phone, $email, $username, $password, $idColor) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $query = 'INSERT INTO customers (firstName, lastName, phone, email, username, password, idColor)
              VALUES (:firstName, :lastName, :phone, :email, :username, :password, :idColor)';
        $statement = $this->db->getDB()->prepare($query);
        $statement->bindValue(':firstName', $firstName);
        $statement->bindValue(':lastName', $lastName);
        $statement->bindValue(':phone', $phone);
        $statement->bindValue(':email', $email);
        $statement->bindValue(':username', $username);
        $statement->bindValue(':password', $hash);
        $statement->bindValue(':idColor', $idColor);
        $statement->execute();
        $statement->closeCursor();
    }

    /**
     * Checks the login credentials
     * 
     * @param type $username
     * @param type $password
     * @return boolean - true if the specified password is valid for the 
     *              specified username
     */
    public function isValidUserLogin($username, $password) {
        $query = 'SELECT password FROM customers
              WHERE username = :username';
        $statement = $this->db->getDB()->prepare($query);
        $statement->bindValue(':username', $username);
        $statement->execute();
        $row = $statement->fetch();
        $statement->closeCursor();
        $hash = $row['password'];
        return password_verify($password, $hash);
    }

    /**
     * Gets the first & last name of the customer
     * @param type $username
     * @return string First & last name of customer
     */
    public function getCustomerName($username) {
        $query = 'SELECT * FROM customers
              WHERE username = :username';
        $statement = $this->db->getDB()->prepare($query);
        $statement->bindValue(':username', $username);
        $statement->execute();
        $row = $statement->fetch();
        $statement->closeCursor();
        $customer = $row['firstName'] . " " . $row['lastName'];
        return $customer;
    }

    /**
     * Gets the customer's customerID
     * @param type $username
     * @return string customer ID
     */
    public function getCustomerID($username) {
        $query = 'SELECT * FROM customers
              WHERE username = :username';
        $statement = $this->db->getDB()->prepare($query);
        $statement->bindValue(':username', $username);
        $statement->execute();
        $row = $statement->fetch();
        $statement->closeCursor();
        $idCustomers = $row['idCustomers'];
        return $idCustomers;
    }

    /**
     * Gets the customer's color preferences
     * @param type $username
     * @return string customer preference
     */
    public function getCustomerPreference($username) {
        $query = 'SELECT * FROM customers
              WHERE username = :username';
        $statement = $this->db->getDB()->prepare($query);
        $statement->bindValue(':username', $username);
        $statement->execute();
        $row = $statement->fetch();
        $statement->closeCursor();
        $preference = $row['idColor'];
        return $preference;
    }

}

?>