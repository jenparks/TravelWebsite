<?php

require_once 'autoload.php';
require_once './model/Database.php';
require_once './model/Tours.php';
require_once './model/Customers.php';
require_once './model/Orders.php';
require_once './model/Colors.php';
require_once './model/Regions.php';
require_once './model/Fields.php';
require_once './model/Validator.php';

class Controller {

    private $action;
    private $db;
    private $twig;
    private $tours;
    private $customers;
    private $orders;
    private $colors;
    private $regions;
    private $validator;

    /**
     * Instantiates a new controller
     */
    public function __construct() {
        $loader = new Twig\Loader\FilesystemLoader('./view');
        $this->twig = new Twig\Environment($loader);
        $this->setupConnection();
        $this->connectToDatabase();
        $this->action = $this->getAction();

        $this->twig->addGlobal('session', $_SESSION);

        $this->tours = new Tours($this->db);
        $this->customers = new Customers($this->db);
        $this->orders = new Orders($this->db);
        $this->colors = new Colors($this->db);
        $this->regions = new Regions($this->db);
        $this->validator = new Validator($this->db);

        $this->validator->getFields();
        $this->validator->addField('first_name');
        $this->validator->addField('last_name');
        $this->validator->addField('phone');
        $this->validator->addField('email');
        $this->validator->addField('username');
        $this->validator->addField('password');
        $this->validator->addField('preference');
        $this->validator->addField('date');
    }

    /**
     * Initiates the processing of the current action
     */
    public function invoke() {
        switch ($this->action) {
            case 'Show Login':
                $this->processShowLogin();
                break;
            case 'Login':
                $this->processLogin();
                break;
            case 'Show Registration':
                $this->processShowRegistration();
                break;
            case 'Register':
                $this->processRegistration();
                break;
            case 'Logout':
                $this->processLogout();
                break;
            case 'Home':
                $this->processShowHomePage();
                break;
            case 'Tours':
                $this->processShowToursPage();
                break;
            case 'Choose Region':
                $this->processShowToursPage();
                break;
            case 'Reservation':
                $this->processShowReservationPage();
                break;
            case 'Make Reservation':
                $this->processMakeReservation();
                break;
            case 'View Reservation':
                $this->processViewReservationPage();
                break;
            case 'Choose My Region':
                $this->processViewReservationPage();
                break;
            case 'Contact':
                $this->processShowContactPage();
                break;
            default:
                $this->processShowHomePage();
                break;
        }
    }

    /*     * **************************************************************
     * Process Request
     * ************************************************************* */

    /**
     * Shows the login page
     */
    private function processShowLogin() {
        $login_message = '';
        $template = $this->twig->load('login.twig');
        echo $template->render(['login_message' => $login_message]);
    }

    /**
     * Logs in the user with the credentials specified in the post array
     */
    private function processLogin() {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, 'password');
        if (!($this->validator->isValidUser($username))) {
            $login_message = 'Invalid username or password';
            $template = $this->twig->load('login.twig');
            echo $template->render(['login_message' => $login_message]);
        } else if ($this->customers->isValidUserLogin($username, $password)) {
            $_SESSION['is_valid_user'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['name'] = $this->customers->getCustomerName($username);
            $_SESSION['pref'] = $this->customers->getCustomerPreference($username);

            $tour = $this->tours->getTours();
            $template = $this->twig->load('reservation.twig');
            header("Location: .?action=Reservation");
        } else {
            $login_message = 'Invalid username or password';
            $template = $this->twig->load('login.twig');
            echo $template->render(['login_message' => $login_message]);
        }
    }

    /**
     * Shows the registration page
     */
    private function processShowRegistration() {
        $error_username = '';
        $error_password = '';
        $color = $this->colors->getColors();
        $template = $this->twig->load('registration.twig');
        echo $template->render(['error_username' => $error_username, 'error_password' => $error_password, 'color' => $color]);
    }

    /**
     * Registers the user as specified in the post array
     */
    private function processRegistration() {
        $input['firstName'] = filter_input(INPUT_POST, 'firstName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input['lastName'] = filter_input(INPUT_POST, 'lastName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input['phone'] = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input['email'] = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $input['username'] = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $input['password'] = filter_input(INPUT_POST, 'password');
        $input['preference'] = filter_input(INPUT_POST, 'preference', FILTER_VALIDATE_INT);

        $this->validator->checkText('first_name', $input['firstName'], true, 1, 50);
        $this->validator->checkText('last_name', $input['lastName'], true, 1, 50);
        $this->validator->checkPhone('phone', $input['phone'], true);
        $this->validator->checkEmail('email', $input['email'], true);
        $this->validator->checkUsername('username', $input['username'], true);
        $this->validator->checkPassword('password', $input['password'], true);

        if ($this->validator->foundErrors()) {
            $color = $this->colors->getColors();
            $fields = $this->validator->getFields();
            $error_firstname = $fields->getField('first_name');
            $error_lastname = $fields->getField('last_name');
            $error_phone = $fields->getField('phone');
            $error_email = $fields->getField('email');
            $error_username = $fields->getField('username');
            $error_password = $fields->getField('password');
            $template = $this->twig->load('registration.twig');
            echo $template->render(['input' => $input, 'error_username' => $error_username, 'error_password' => $error_password,
                'color' => $color, 'error_firstname' => $error_firstname, 'error_lastname' => $error_lastname,
                'error_phone' => $error_phone, 'error_email' => $error_email]);
        } else {
            $this->customers->addUser($input['firstName'], $input['lastName'], $input['phone'], $input['email'], $input['username'], $input['password'], $input['preference']);
            $_SESSION['is_valid_user'] = true;
            $_SESSION['username'] = $input['username'];
            $_SESSION['name'] = $this->customers->getCustomerName($input['username']);
            $_SESSION['pref'] = $this->customers->getCustomerPreference($input['username']);
            header("Location: .?action=Reservation");
        }
    }

    /**
     * Shows the home page
     */
    private function processShowHomePage() {
        $template = $this->twig->load('home.twig');
        echo $template->render();
    }

    /**
     * Shows the tours page
     */
    private function processShowToursPage() {
        $idRegion = filter_input(INPUT_POST, 'idRegion', FILTER_VALIDATE_INT);
        if ($idRegion == 0 || $idRegion == null) {
            $tour = $this->tours->getTours();
            $region = $this->regions->getRegions();
            $template = $this->twig->load('tours.twig');
            echo $template->render(['tour' => $tour, 'region' => $region]);
        } else {
            $tour = $this->tours->getToursByRegion($idRegion);
            $region = $this->regions->getRegions();
            $template = $this->twig->load('tours.twig');
            echo $template->render(['tour' => $tour, 'region' => $region]);
        }
    }

    /**
     * Shows the reservation page
     */
    private function processShowReservationPage() {
        $tour = $this->tours->getTours();
        $idCustomers = $this->customers->getCustomerID($_SESSION['username']);
        $userOrders = $this->orders->getPreviousOrders($idCustomers);
        $template = $this->twig->load('reservation.twig');
        echo $template->render(['tour' => $tour, 'userOrders' => $userOrders]);
    }

    /**
     * Makes a new reservation
     */
    private function processMakeReservation() {
        $idTour = filter_input(INPUT_POST, 'idTour', FILTER_VALIDATE_INT);
        $date = filter_input(INPUT_POST, 'date');

        $this->validator->checkDate('date', $date, true);

        if ($this->validator->foundErrors()) {
            $tour = $this->tours->getTours();
            $idCustomers = $this->customers->getCustomerID($_SESSION['username']);
            $userOrders = $this->orders->getPreviousOrders($idCustomers);
            $fields = $this->validator->getFields();
            $error_date = $fields->getField('date');
            $template = $this->twig->load('reservation.twig');
            echo $template->render(['error_date' => $error_date]);
        } else {
            $idCustomers = $this->customers->getCustomerID($_SESSION['username']);
            $region = $this->regions->getRegions();
            $this->orders->placeOrder($idCustomers, $idTour, $date);
            $userOrders = $this->orders->getPreviousOrders($idCustomers);
            $template = $this->twig->load('view_reservation.twig');
            $tour = $this->tours->getTours();
            echo $template->render(['tour' => $tour, 'userOrders' => $userOrders, 'region' => $region]);
        }
    }

    /**
     * Shows user all reservations
     */
    private function processViewReservationPage() {
        $idRegion = filter_input(INPUT_POST, 'idRegion', FILTER_VALIDATE_INT);
        if ($idRegion == 0 || $idRegion == null) {
            $tour = $this->tours->getTours();
            $region = $this->regions->getRegions();
            $idCustomers = $this->customers->getCustomerID($_SESSION['username']);
            $userOrders = $this->orders->getPreviousOrders($idCustomers);

            $template = $this->twig->load('view_reservation.twig');
            echo $template->render(['tour' => $tour, 'userOrders' => $userOrders, 'region' => $region]);
        } else {
            $tour = $this->tours->getToursByRegion($idRegion);
            $region = $this->regions->getRegions();
            $idCustomers = $this->customers->getCustomerID($_SESSION['username']);
            $userOrders = $this->orders->getPreviousOrders($idCustomers);

            $template = $this->twig->load('view_reservation.twig');
            echo $template->render(['tour' => $tour, 'userOrders' => $userOrders, 'region' => $region]);
        }
    }

    /**
     * Shows the contact page
     */
    private function processShowContactPage() {
        $template = $this->twig->load('contact.twig');
        echo $template->render();
    }

    /**
     * Clear all session data from memory and cleans up the session ID
     * in order to logout the current user
     */
    private function processLogout() {
        $_SESSION = array();
        session_destroy();
        $login_message = 'You have been logged out.';
        $template = $this->twig->load('login.twig');
        $this->twig->addGlobal('session', $_SESSION);
        echo $template->render(['login_message' => $login_message]);
    }

    /**
     * Gets the action from $_GET or $_POST array
     * 
     * @return string the action to be processed
     */
    private function getAction() {
        $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($action === NULL) {
            $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if ($action === NULL) {
                $action = '';
            }
        }
        return $action;
    }

    /**
     * Ensures a secure connection and start session
     */
    private function setupConnection() {
        $https = filter_input(INPUT_SERVER, 'HTTPS');
        if (!$https) {
            $host = filter_input(INPUT_SERVER, 'HTTP_HOST');
            $uri = filter_input(INPUT_SERVER, 'REQUEST_URI');
            $url = 'https://' . $host . $uri;
            header("Location: " . $url);
            exit();
        }
        session_start();
    }

    /**
     * Connects to the database
     */
    private function connectToDatabase() {
        $this->db = new Database();
        if (!$this->db->isConnected()) {
            $error_message = $this->db->getErrorMessage();
            $template = $this->twig->load('database_error.twig');
            echo $template->render(['error_message' => $error_message]);
            exit();
        }
    }

}
