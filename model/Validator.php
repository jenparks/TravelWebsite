<?php

require_once 'Fields.php';

class Validator {

    private $db;

    /**
     * Instantiates a new validator
     */
    public function __construct($db) {
        $this->db = $db;
        $this->fields = new Fields();
    }

    public function getFields() {
        return $this->fields;
    }

    public function foundErrors() {
        return $this->fields->hasErrors();
    }

    public function addField($name, $message = '') {
        return $this->fields->addField($name, $message);
    }

    /**
     * Checks if the specified username is in this database
     * 
     * @param string $username
     * @return boolean - true if username is in this database
     */
    public function isValidUser($username) {
        $query = 'SELECT * FROM customers
              WHERE username = :username';
        $statement = $this->db->getDB()->prepare($query);
        $statement->bindValue(':username', $username);
        $statement->execute();
        $row = $statement->fetch();
        $statement->closeCursor();
        return !($row === false);
    }

    // Validate a generic text field
    public function checkText($name, $value, $required = true, $min = 1, $max = 255) {
        $field = $this->fields->getField($name);
        if (!$required && empty($value)) {
            $field->clearErrorMessage();
            return;
        }
        if ($required && empty($value)) {
            $field->setErrorMessage('Required');
        } else if (strlen($value) < $min) {
            $field->setErrorMessage('Too short');
        } else if (strlen($value) > $max) {
            $field->setErrorMessage('Too long');
        } else {
            $field->clearErrorMessage();
        }
    }

    // Validate a field with a generic pattern
    public function checkPattern($name, $value, $pattern, $message, $required = true) {
        $field = $this->fields->getField($name);
        if (!$required && empty($value)) {
            $field->clearErrorMessage();
            return;
        }
        $match = preg_match($pattern, $value);
        if ($match === false) {
            $field->setErrorMessage('Error testing field.');
        } else if ($match != 1) {
            $field->setErrorMessage($message);
        } else {
            $field->clearErrorMessage();
        }
    }

    public function checkPhone($name, $value, $required = false) {
        $field = $this->fields->getField($name);
        $this->checkText($name, $value, $required);
        if ($field->hasError()) {
            return;
        }
        $pattern = '/^[[:digit:]]{3}-[[:digit:]]{3}-[[:digit:]]{4}$/';
        $message = 'Invalid phone number.';
        $this->checkPattern($name, $value, $pattern, $message, $required);
    }

    public function checkDate($name, $value, $required = false) {
        $field = $this->fields->getField($name);
        $this->checkText($name, $value, $required);
        if ($field->hasError()) {
            return;
        }
        $pattern = '/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12][0-9]|3[01])$/';
        $message = 'Invalid date.';
        $this->checkPattern($name, $value, $pattern, $message, $required);
    }

    public function checkEmail($name, $value, $required = true) {
        $field = $this->fields->getField($name);
        if (!$required && empty($value)) {
            $field->clearErrorMessage();
            return;
        }
        $this->checkText($name, $value, $required);
        if ($field->hasError()) {
            return;
        }

        // Split email address on @ sign and check parts
        $parts = explode('@', $value);
        if (count($parts) < 2) {
            $field->setErrorMessage('At sign required.');
            return;
        }
        if (count($parts) > 2) {
            $field->setErrorMessage('Only one at sign allowed.');
            return;
        }
        $local = $parts[0];
        $domain = $parts[1];

        // Check lengths of local and domain parts
        if (strlen($local) > 64) {
            $field->setErrorMessage('Username part too long.');
            return;
        }
        if (strlen($domain) > 255) {
            $field->setErrorMessage('Domain name part too long.');
            return;
        }

        // Patterns for address formatted local part
        $atom = '[[:alnum:]_!#$%&\'*+\/=?^`{|}~-]+';
        $dotatom = '(\.' . $atom . ')*';
        $address = '(^' . $atom . $dotatom . '$)';

        // Patterns for quoted text formatted local part
        $char = '([^\\\\"])';
        $esc = '(\\\\[\\\\"])';
        $text = '(' . $char . '|' . $esc . ')+';
        $quoted = '(^"' . $text . '"$)';

        // Combined pattern for testing local part
        $localPattern = '/' . $address . '|' . $quoted . '/';

        // Call the pattern method and exit if it yields an error
        $this->checkPattern($name, $local, $localPattern,
                'Invalid username part.');
        if ($field->hasError()) {
            return;
        }

        // Patterns for domain part
        $hostname = '([[:alnum:]]([-[:alnum:]]{0,62}[[:alnum:]])?)';
        $hostnames = '(' . $hostname . '(\.' . $hostname . ')*)';
        $top = '\.[[:alnum:]]{2,6}';
        $domainPattern = '/^' . $hostnames . $top . '$/';

        // Call the pattern method
        $this->checkPattern($name, $domain, $domainPattern,
                'Invalid domain name part.');
    }

    public function checkPassword($name, $value, $required = true) {
        $field = $this->fields->getField($name);
        $this->checkText($name, $value, $required, 6, 20);
        if ($field->hasError()) {
            return;
        }
        $pattern = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])[[:print:]]{6,20}$/';
        $message = 'Invalid password. Must have 1 lowercase, 1 uppercase, & 1 number. Must be between 6 and 20 characters.';
        $this->checkPattern($name, $value, $pattern, $message, $required);
    }

    public function checkUsername($name, $value, $required = true) {
        $field = $this->fields->getField($name);
        $this->checkText($name, $value, $required, 1, 20);
        if ($field->hasError()) {
            return;
        }
        if ($this->isValidUser($value)) {
            $field->setErrorMessage('The username exists already.');
            return;
        }
    }

}

?>