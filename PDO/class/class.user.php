<?php
/*
########################################################################################################
Name  : PHP & Mysql Object Oriented Programming with PDO                                                                  
Author  : Sadirul Islam
E-mail  : sadirul.islam786@gmail.com
License : *******
Description :   This a PHP class to Which help you create a dynamic website with MySQL and PDO. 
  You can Login, Register & SELECT, INSERT, UPDATE, DELETE MySQL data by very secure way. 
  You can also generate user real IP address and location. This is a very secure php class 
  to make professional web application.
########################################################################################################
*/
//DATABASE CONNECTION
require_once ('dbconfig.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
//SET TIME ZONE
date_default_timezone_set('Asia/Kolkata');
//CREATE USER CLASS
class USER {
    //CONNECTION VARIABLE
    private $conn;
    //SESSION VARIABLE
    private $sessionName;
    public function __construct() {
        $database = new Database();
        $this->conn = $database->dbConnection();
        $this->sessionName = $database->session_name();
    }
    //MySQL QUERY TO LOGIN
    public function login($uname, $pass, $tbl = "user") {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM $tbl WHERE MD5(username) = :uname AND MD5(password) = :pass");
            $stmt->execute(array('uname' => md5($uname), 'pass' => md5($pass)));
            $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($stmt->rowCount() > 0) {
                if ($pass == $userRow['password']) {
                    $_SESSION[$this->sessionName] = $userRow['id'];
                    return true;
                } else {
                    return false;
                }
            }
        }
        catch(PDOException $e) {
            echo $e->getMessage();
        }
    }
    //CHANGE PASSWORD
    public function change_password($cur_pass, $new_pass, $user_id, $tbl = 'user') {
        $this->query("SELECT password FROM $tbl WHERE id = :id");
        $this->bind("id", $this->sessionID());
        $row = $this->fetchOne();
        if ($row['password'] == $cur_pass) {
            $this->query("UPDATE $tbl SET password = :password WHERE id = :id");
            $this->bind("password", $new_pass);
            $this->bind("id", $user_id);
            if ($this->execute()) {
                return true;
            }
        } else {
            return false;
        }
    }
    //USER EXISTS OR NOT
    public function isExists($tbl, $col, $val) {
        $this->query("SELECT * FROM $tbl WHERE $col = :val");
        $this->bind('val', $val);
        $this->execute();
        if ($this->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
    //LOGOUT
    public function logout($redirect = '') {
        session_destroy();
        unset($_SESSION[$this->sessionName]);
        if (!empty($redirect)) {
            $this->redirect($redirect);
        }
        return true;
    }
    //GET SESSION ID
    public function sessionID() {
        if (isset($_SESSION[$this->sessionName])) {
            return $_SESSION[$this->sessionName];
        }
    }
    //USER IS LOGEDIN OR NOT
    public function isLogedin() {
        if (isset($_SESSION[$this->sessionName]) && !empty($_SESSION[$this->sessionName])) {
            return true;
        }
    }
    //LAST INSERT ID
    public function insertID() {
        return $this->conn->lastInsertId();
    }
    private $stmt;
    private $res;
    // MYSQL QUERY FUNCTION WITH PREPARE STATEMENT
    public function query($sql) {
        return $this->stmt = $this->conn->prepare($sql);
    }
    //MYSQL EXECUTE FUNCTION
    public function execute() {
        return $this->stmt->execute();
    }
    //DELETE MYSQL ROW
    public function delete_row($table, $column, $value) {
        $this->query("DELETE FROM $table WHERE $column = :id");
        $this->bind("id", $id);
        if ($this->execute()) {
            return true;
        }
    }
    // GET ONE FUNCTION
    public function getOne($sql, $type = ''){
        $this->query($sql);
        return $this->fetchOne($type);
    }
    // GET ALL FUNCTION
    public function getAll($sql, $type = ''){
        $this->query($sql);
        return $this->fetchAll($type);
    }
    //MYSQLI FETCH ASSOC
    public function fetchOne($type = '') {
        $this->execute();
        return ($type == "assoc") ? $this->stmt->fetch(PDO::FETCH_ASSOC) : $this->stmt->fetch();
    }
    //MYSQLI FETCH ALL
    public function fetchAll($type = '') {
        $this->execute();
        return ($type == "assoc") ? $this->stmt->fetchAll(PDO::FETCH_ASSOC) : $this->stmt->fetchAll();
    }
    //ROW COUNT
    public function rowCount() {
        $this->execute();
        return $this->stmt->rowCount();
    }
    //CHECK EMAIL IS TRUE OR NOT
    public function isEmail($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
    }
    //CHECK IS NAME OR NOT
    public function isName($name) {
        if (preg_match("/^[a-zA-Z ]*$/", $name)) {
            return true;
        }
    }
    //IS USERNAME
    public function isUsername($username) {
        if (preg_match("/^[a-zA-Z0-9]*$/", $username)) {
            return true;
        }
    }
    //INTEGER VAL IS ODD
    public function isEven($int) {
        $int = $this->int($int);
        if ($int % 2 === 0) {
            return true;
        }
        return false;
    }
    //IS MOBILE
    public function isMobile($mobile) {
        if (preg_match("/^[0-9]*$/", $mobile)) {
            return true;
        }
    }
    //HIDE CARD AND MOBILE NO
    public function hide_number($number, $show, $hidden_with = "X") {
        $no = $this->str($number);
        return str_repeat($hidden_with, $this->len($no) - $show) . substr($no, -$show);
    }
    //REPEAT TEXT
    public function repeat($text, $num) {
        return str_repeat($text, $num);
    }
    //CLOSE CONNECTION
    public function connClose() {
        return $this->conn = null;
    }
    //DINAMICALLY BIND DATA
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        return $this->stmt->bindValue($param, $value, $type);
    }
    //ENCRYPT DATA
    public function encrypt($value) {
        return md5(sha1($value));
    }
    //CREATE COOKIE
    public function setcookie($cookie_name, $cookie_value, $time_in_hour = 1) {
        return setcookie($this->encrypt($cookie_name), $cookie_value, time() + (3600 * $time_in_hour), "/");
    }
    //GET COOKIE
    public function getcookie($cookie_name) {
        if (isset($_COOKIE[$this->encrypt($cookie_name) ])) {
            return $_COOKIE[$this->encrypt($cookie_name) ];
        }
        return false;
    }
    //DELETE EXISTS COOKIE
    public function deletecookie($cookie_name) {
        if (isset($_COOKIE[$this->encrypt($cookie_name) ])) {
            return setcookie($this->encrypt($cookie_name), "", time() - 3600, "/");
        }
    }
    //SLEEP FUNCTION
    public function pause($second) {
        return sleep($second);
    }
    //STRING LOWER CASE
    public function lower($str) {
        $str = $this->str($str);
        return strtolower($str);
    }
    //STRING IN CAMELCASE
    public function camelcase($str) {
        $str = $this->str($str);
        return ucwords($this->lower($str));
    }
    //STRING UPPER CASE
    public function upper($str) {
        $str = $this->str($str);
        return strtoupper($str);
    }
    //RANDOM NUMBER
    public function random($start, $end) {
        if (is_integer($start) && is_integer($end) && $start < $end) {
            return mt_rand($start, $end);
        } else {
            throw new Exception("random() function take two param as integer and start value must be less than end value.");
        }
    }
    //RANDOM STRING
    public function str_random($str) {
        $str = $this->str($str);
        return str_shuffle($str);
    }
    //DELETE LAST ELEMRNT FORM AN ARRAY
    public function pop($array) {
        if (is_array($array)) {
            array_pop($array);
            return $array;
        } else {
            throw new Exception("pop() function take only one array argument!");
        }
    }
    //WHICH ELEMRNT IS APPEND IN AN ARRAY
    public function popped($array) {
        if (is_array($array)) {
            return array_pop($array);
        } else {
            throw new Exception("popped() function take only one array argument!");
        }
    }
    //APPEND ELEMRNT IN AN ARRAY
    public function append($array, $val) {
        if (is_array($array)) {
            array_push($array, $val);
            return $array;
        } else {
            throw new Exception("append() function take only one array argument!");
        }
    }
    //SPLIT TEXT TO ARRAY
    public function split($text, $where = '') {
        if (is_string($text)) {
            if (empty($where)) {
                return str_split($text);
            } else {
                return explode($where, $text);
            }
        } else {
            throw new Exception("split() function take one string argument!");
        }
    }
    //JOIN AN ARRAY TO STRING
    public function makejoin($array, $option = "") {
        if (is_array($array)) {
            return implode($option, $array);
        } else {
            throw new Exception("makejoin() function take one array argument!");
        }
    }
    //TRIM STRING
    public function strip($str) {
        $str = $this->str($str);
        return trim($str);
    }
    //LTRIM STRING
    public function lstrip($str) {
        $str = $this->str($str);
        return ltrim($str);
    }
    //RTRIM STRING
    public function rstrip($str) {
        $str = $this->str($str);
        return rtrim($str);
    }
    //GET 1ST ELEMENT OF AN ARRAY
    public function first($arr) {
        if (is_array($arr)) {
            return current($arr);
        } else {
            throw new Exception("first() function take one array argument!");
        }
    }
    //GET LAST ELEMENT OF AN ARRAY
    public function last($arr) {
        if (is_array($arr)) {
            return end($arr);
        } else {
            throw new Exception("last() function take one array argument!");
        }
    }
    //GET SECONDLAST ELEMENT OF AN ARRAY
    public function secondlast($arr) {
        if (is_array($arr)) {
            return $arr[$this->len($arr) - 2];
        } else {
            throw new Exception("secondlast() function take one array argument!");
        }
    }
    //JSON ENCODE AND DECODE
    public function json($arr) {
        if (is_array($arr) || is_object($arr)) {
            return json_encode($arr);
        } elseif (is_string($arr)) {
            return json_decode($arr, true);
        }
    }
    //REVERSE ARRAY AND STRING
    public function reverse($arr_or_str) {
        if (is_string($arr_or_str)) {
            return strrev($arr_or_str);
        } elseif (is_array($arr_or_str)) {
            return array_reverse($arr_or_str);
        } else {
            throw new Exception("reverse() function take array or string argument!");
        }
    }
    //UNIQUE FROM ARRAY
    public function unique_array($arr) {
        if (is_array($arr)) {
            return array_unique($arr);
        } else {
            throw new Exception("unique() function take one array argument!");
        }
    }
    //GET ALL KEYS OF AN ARRAY
    public function all_keys($arr) {
        if (is_array($arr)) {
            return array_keys($arr);
        } else {
            throw new Exception("all_keys() function take one array argument!");
        }
    }
    //VIEW SOURCE CODE OF THE FILE
    public function view_source($file) {
        if (is_file($file)) {
            return show_source($file);
        } else {
            throw new Exception("$file not exists!");
        }
    }
    //AUTH KEY
    public function auth_key($length = 20) {
        return substr(str_shuffle("AbCdEfGhIjKlMnOpQrStUvWxYz" . sha1(time()) . md5(date('Y/m/d h:i:s'))), 0, $length);
    }
    //SUM OF ARRAY
    public function sum($array) {
        if (is_array($array)) {
            return array_sum($array);
        } else {
            throw new Exception("sum() function take one array argument!");
        }
    }
    //MULTIPLY EACH VALUE OF AN ARRAY
    public function multiply($arr) {
        if (is_array($arr)) {
            $ans = 1;
            foreach ($arr as $val) {
                if (is_integer($val)) {
                    $ans*= $val;
                }
            }
            return $ans;
        }
    }
    //CHECKING VALUE IN ARRAY OR NOT
    public function in($key, $arr) {
        if (is_array($arr) && !empty($key)) {
            if (in_array($key, $arr)) {
                return true;
            }
        } else {
            throw new Exception("In in() function second param must be an not empty array!");
        }
    }
    //GET DATA TYPE
    public function type($var) {
        return gettype($var);
    }
    //GET DATA FROM WEB BY GET REQUESTS
    public function requests($url, $data = array(), $method = 'get') {
        if ($this->is_url($url) && !empty($url)) {
            if (is_array($data)) {
                if ($method === "get") {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                } else if ($method === "post") {
                    $ch = curl_init($url);
                    $param = http_build_query($data);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
                }
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $output = curl_exec($ch);
                curl_close($ch);
                return $output;
            } else {
                throw new Exception("Second argument be an array");
            }
        } else {
            throw new Exception("Unknown URL!");
        }
    }
    //DOWNLOAD FILES FROM WEB
    public function download($url, $dir, $filename) {
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        if ($this->is_url($url)) {
            $content = file_get_contents($url);
            return $this->file($dir . '/' . $filename, "w", $content);
        } else {
            throw new Exception("Wrong download URL!");
        }
    }
    //CHECk REAL URL OR NOT
    public function is_url($url) {
        if (preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $url)) {
            return true;
        }
    }
    //REDIRECT
    public function redirect($url) {
        header("Location: $url");
    }
    //READ TEXT FILE
    public function file($filename, $mode = "r", $text = '') {
        if ($mode == "r" || $mode == "r+") {
            if (file_exists($filename)) {
                $fp = fopen($filename, $mode);
                return fread($fp, filesize($filename));
                fclose($fp);
            } else {
                throw new Exception("$filename not exists to read!");
            }
        } elseif (in_array($mode, ['a', 'a+', 'w', 'w+'])) {
            $fp = fopen($filename, $mode);
            fwrite($fp, $text);
            fclose($fp);
        } else {
            throw new Exception("Wrong file mode!");
        }
    }
    // DELETE FILE
    public function file_delete($filename) {
        if (file_exists($filename)) {
            unlink($filename);
            return true;
        }
        return false;
    }
    //READ FILE LINE B LINE
    public function readlines($filename) {
        if (file_exists($filename)) {
            $fp = fopen($filename, "r");
            $arr = array();
            while (($line = fgets($fp)) !== false) {
                array_push($arr, $line);
            }
            return $arr;
            fclose($fp);
        } else {
            throw new Exception("$filename file not exists!");
        }
    }
    //GET EMAIL FROM TEXT
    public function mail_from_text($text) {
        if (!empty($text)) {
            $res = preg_match_all("/[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}/i", $text, $matches);
            if ($res) {
                foreach (array_unique($matches[0]) as $email) {
                    echo $email . "<br />";
                }
            } else {
                throw new Exception("No emails found!");
            }
        }
    }
    //URL DIR WITH FILE NAME
    public function dir_and_file() {
        return $_SERVER['PHP_SELF'];
    }
    //URL FILE NAME
    public function url_file_name() {
        return basename($_SERVER['PHP_SELF']);
    }
    //FULL URL
    public function full_url() {
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return $actual_link;
    }
    //GET FULL DOMAIN
    public function get_domain() {
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        return $actual_link;
    }
    //STRING TO INTEGER
    public function strtoint($str) {
        $intValue = intval(preg_replace('/[^0-9]+/', '', $str));
        return $intValue;
    }
    //GET QUERY VARIABLE FROM COSTUME URL
    public function get_url_var($url) {
        $parts = parse_url($url);
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
            return $query;
        }
    }
    public function YouTubeID($url) {
        $video_id = false;
        $url = parse_url($url);
        if (strcasecmp($url['host'], 'youtu.be') === 0) {
            $video_id = substr($url['path'], 1);
        } elseif (strcasecmp($url['host'], 'www.youtube.com') === 0) {
            if (isset($url['query'])) {
                parse_str($url['query'], $url['query']);
                if (isset($url['query']['v'])) {
                    $video_id = $url['query']['v'];
                }
            }
            if ($video_id == false) {
                $url['path'] = explode('/', substr($url['path'], 1));
                if (in_array($url['path'][0], array('e', 'embed', 'v'))) {
                    $video_id = $url['path'][1];
                }
            }
        }
        return $video_id;
    }
    //GET DATE FUNCTION
    public function get_date($type) {
        if ($type == 'first') {
            return date('Y-m-01 H:i:s');
        } elseif ($type == 'last') {
            return date('Y-m-t H:i:s');
        } elseif ($type == 'today') {
            return date('Y-m-d H:i:s');
        } elseif ($type == 'next') {
            return date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +1 day'));
        } elseif ($type == 'prev') {
            return date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' -1 day'));
        } elseif ($type == 'time') {
            return date('Y-m-d H:i:s');
        }
    }
    // DATE FORMAT
    public function dateFormat($format, $date) {
        $new_date = date($format, strtotime($date));
        return $new_date;
    }
    //EXPIRY DATE
    public function expiry_date($expiry = "+28 days", $date = "") {
        if (empty($date)) {
            $date = date("Y-m-d H:i:s");
        }
        return date('Y-m-d H:i:s', strtotime($date . $expiry));
    }
    //DATE IS EXPIRED OR NOT
    public function is_expired($date) {
        $today = date("Y-m-d H:i:s");
        if ($date < $today) {
            return true;
        }
    }
    //GE DAY BETWEEN TWO DATE
    public function total_day($date2, $date1 = '') {
        if (empty($date1)) {
            $date1 = date_create(date("Y-m-d h:i:s"));
        } else {
            $date1 = date_create($date1);
        }
        $date2 = date_create($date2);
        $diff = date_diff($date1, $date2);
        return $diff->format("%R%a");
    }
    //PAGINATION
    public function pagination($row_per_page, $total_row) {
        if(!isset($_GET['page'])) {
            $page=1;
        } else {
            $page=$_GET['page'];
        }
        $next_page = $page+1;

        $previous_page = $page-1;

        $offset = $previous_page * $row_per_page;

        $total_pages = ceil($total_row/$row_per_page);

        $pagination_data = array('page'=>$page, 'next_page'=>$next_page, 'previous_page'=>$previous_page, 'offset'=>$offset, 'limit'=>$row_per_page, 'total_page'=>$total_pages, 'total_row'=>$total_row);
        return (object)$pagination_data;
    }
    //CHECKING WHO LOGGEDIN
    public function isAdmin($tbl = 'admin') {
        $stmt = $this->query("SELECT type FROM $tbl WHERE id = :uid");
        $stmt->execute(array('uid' => $_SESSION[$this->sessionName]));
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $type = $userRow['type'];
        if ($type == 'admin') {
            return true;
        } else {
            return false;
        }
    }
    //GET USER DATA BY ID
    public function user_data($tbl = 'user', $id) {
        $this->query("SELECT * FROM $tbl WHERE id = :uid");
        $this->bind('uid', $id);
        if ($this->rowCount() > 0) {
            $userRow = $this->fetchOne();
            return (array)$userRow;
        }
    }
    //GET DB ROW
    public function select_row($tbl, $column, $value) {
        $this->query("SELECT * FROM $tbl WHERE $column = :val");
        $this->bind('val', $value);
        if ($this->rowCount() > 0) {
            $userRow = $this->fetchOne();
            return (array)$userRow;
        }
    }
    //GET USER IP ADDRESS
    public function userIP() {
        $clint = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote = $_SERVER['REMOTE_ADDR'];
        if (filter_var($clint, FILTER_VALIDATE_IP)) {
            $ip = $clint;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        return $ip;
    }
    //ESCAPE SPECIAL CHAR
    public function escape($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars_decode($data);
        $data = strip_tags($data);
        return $data;
    }
    //BREAK
    public function br($str) {
        return nl2br($str);
    }
    //REPLACE TEXT IN TEXT
    public function replace($from, $to, $text) {
        return str_replace($from, $to, $text);
    }
    //ENCODE STRING
    public function hash_text($text) {
        $text = $this->str($text);
        return str_rot13($text);
    }
    //ADD KEY INTO ARRAY
    public function add_keys($keys, $arr_val) {
        if (is_array($keys) && is_array($arr_val)) {
            return array_combine($keys, $arr_val);
        }
    }
    //MERGE TOW ARRAY
    public function add_array($arr1, $arr2) {
        if (is_array($arr1) && is_array($arr2)) {
            return array_merge($arr1, $arr2);
        } else {
            throw new Exception("add_array() function take two array!");
        }
    }
    //ADD PAGE VISITOR
    public function visitor($type = "set") {
        $sql = "CREATE TABLE IF NOT EXISTS visitor (
        id int NOT NULL AUTO_INCREMENT,
        ip text NOT NULL,
        date date NOT NULL,
        count int NOT NULL,
        PRIMARY KEY (id)
    )";
        $this->query($sql);
        $this->execute();
        switch ($type) {
            case 'set':
                $date = date('Y-m-d');
                $ip = $this->userIP();
                $this->query("SELECT * FROM visitor WHERE date = :date AND ip = :ip");
                $this->bind("date", $date);
                $this->bind("ip", $ip);
                if ($this->rowCount() > 0) {
                    $sql = $this->query("UPDATE visitor SET count = count + :count WHERE date = :date AND ip = :ip");
                    $this->bind("count", 1);
                    $this->bind("date", $date);
                    $this->bind("ip", $ip);
                    $this->execute();
                } else {
                    $sql = $this->query("INSERT INTO visitor(ip, date, count) VALUES(:ip, :date, :count)");
                    $this->bind("ip", $ip);
                    $this->bind("date", $date);
                    $this->bind("count", 1);
                    $this->execute();
                }
            break;
            case 'today_unique':
                $date = date('Y-m-d');
                $this->query("SELECT id FROM visitor WHERE date = :date");
                $this->bind("date", $date);
                $this->execute();
                return $this->rowCount();
            break;
            case 'today_total':
                $date = date('Y-m-d');
                $this->query("SELECT sum(count) AS count FROM visitor WHERE date = :date");
                $this->bind("date", $date);
                $this->execute();
                $count = $this->fetchOne();
                return $count['count'];
            break;
            case 'monthly_unique':
                $start = date("Y-m-01");
                $endd = date("Y-m-t");
                $this->query("SELECT id FROM visitor WHERE date BETWEEN :start AND :endd");
                $this->bind("start", $start);
                $this->bind("endd", $endd);
                return $this->rowCount();
            break;
            case 'monthly_total':
                $start = date("Y-m-01");
                $end = date("Y-m-t");
                $this->query("SELECT sum(count) AS count FROM visitor WHERE date BETWEEN :start AND :endd");
                $this->bind("start", $start);
                $this->bind("endd", $end);
                $cnt_data = $this->fetchOne();
                return $cnt_data['count'];
            break;
            case 'yearly_total':
                $start = date("Y-01-01");
                $end = date("Y-12-31");
                $this->query("SELECT sum(count) AS count FROM visitor WHERE date BETWEEN :start AND :endd");
                $this->bind("start", $start);
                $this->bind("endd", $end);
                $cnt_data = $this->fetchOne();
                return $cnt_data['count'];
            break;
        }
    }
    //GENERATE CSRF TOKEN
    public function csrf_token($type = "set", $session_name = "csrf_toten") {
        if ($type === 'set') {
            $tkn = $this->str_random(md5(sha1(time() . "ABCDEFGIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789")));
            $csrf_tkn = $this->create_session($session_name, $tkn);
            return '<input class="csrf_token" type="hidden" name="' . $session_name . '" value="' . $csrf_tkn . '" id="csrf_token">';
        } elseif ($type === 'get') {
            if (isset($_POST[$session_name]) && !empty($_POST[$session_name]) && !empty($this->get_session($session_name))) {
                if ($_POST[$session_name] == $this->get_session($session_name)) {
                    return true;
                }
            }
        }
    }
    // GET CSRF TOKEN
    public function inline_csrf($type = "set", $session_name = "inline_csrf_token") {
        if ($type == "set") {
            $tkn = $this->str_random(md5(sha1(time() . "ABCDEFGIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789")));
            $csrf_tkn = $this->create_session($session_name, $tkn);
            return $tkn;
        } elseif ($type === 'get') {
            if (isset($_GET[$session_name]) && !empty($_GET[$session_name]) && !empty($this->get_session($session_name))) {
                if ($_GET[$session_name] == $this->get_session($session_name)) {
                    return true;
                }
            }
        }
    }
    //GET LENGTH OF ARRAY AND STRING
    public function len($val) {
        if (is_string($val)) {
            return strlen($val);
        } elseif (is_array($val)) {
            return count($val);
        } else {
            throw new Exception("len() function take string or array!");
        }
    }
    //FILE EXISTS OR NOT
    public function file_exists($file) {
        if (file_exists($file)) {
            return true;
        }
    }
    //DIRECTORY
    public function directory($dir, $mode = "c") {
        if ($mode == "c") {
            if (!is_dir($dir)) {
                mkdir($dir);
            }
        } elseif ($mode == "d") {
            if (is_dir($dir)) {
                rmdir($dir);
            } else {
                throw new Exception($dir . " directory not exists!");
            }
        }
    }
    //WORD COUNT FUNCTION
    public function word_count($str) {
        return str_word_count($str);
    }
    //MOVE UPLOADED FILE
    public function upload_to($file, $destination) {
        return move_uploaded_file($file, $destination);
    }
    //CREATE SESSION
    public function create_session($session_name, $session_value = '') {
        return $_SESSION[$session_name] = $session_value;
    }
    //GET SESSION
    public function get_session($session_name) {
        if (isset($_SESSION[$session_name])) {
            return $_SESSION[$session_name];
        }
    }
    //CONVERT TO INTEGER
    public function int($val) {
        return intval($val);
    }
    //CONVERT TO STRING
    public function str($val) {
        return strval($val);
    }
    //UPLOAD FILES
    public function file_upload($file, $type = ['png', 'jpg', 'jpeg'], $up_path = '', $file_name = '', $size = 999999999999) {
        if (!is_array($type)) {
            throw new Exception("file_upload() type must be an array!");
            exit();
        }
        $up_path = $up_path . '/';
        $this->directory($up_path, "c");
        $filename = $file['name'];
        $filesize = $file['size'];
        $file_tmp_name = $file['tmp_name'];
        $file_error = $file['error'];
        $expld = explode('.', $filename);
        $file_ext = strtolower(end($expld));
        $up_name = $this->str_random(time() . uniqid()) . '.' . $file_ext;
        $up_name = empty($file_name) ? $up_name : $file_name;
        $upload_to = $up_path . basename($up_name);
        $ext_arr = $type;
        //CHECKING AND UPLOAD
        if (empty($file_tmp_name)) {
            return ['status' => 'error', 'msg' => 'empty_file'];
        }
        if (!in_array($file_ext, $ext_arr)) {
            return ['status' => 'error', 'msg' => 'ext_not_allowed'];
        }
        if ($filesize >= $size) {
            return ['status' => 'error', 'msg' => 'learge_file'];
        }
        if ($file_error != 0) {
            return ['status' => 'error', 'msg' => 'something_wrong'];
        }
        if (move_uploaded_file($file_tmp_name, $upload_to)) {
            $details = array('status' => 'success', 'file_name' => $up_name, 'old_file' => $filename, 'file_size' => $filesize, 'temp_name' => $file_tmp_name, 'file_ext' => $file_ext);
            return $details;
        }
    }
    //FILE INFO
    public function fileInfo($file) {
        $file_name = $file['name'];
        $expld = explode(".", $file_name);
        $file_ext = end($expld);
        $file_type = $file['type'];
        $file_size = $file['size'];
        $file_tmp_name = $file['tmp_name'];
        $file_error = $file['error'];
        $file_data = array('name' => $file_name, 'ext' => $file_ext, 'type' => $file_type, 'size' => $file_size, 'tmp_name' => $file_tmp_name, 'file_error' => $file_error);
        return (object)$file_data;
    }
    //CHECK REQUEST IS SAME SERVER OR NOT
    public function isServer() {
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $curl = parse_url($_SERVER['HTTP_REFERER']);
            $get_url = $curl['host'];
            $own_url = $_SERVER['HTTP_HOST'];
            if ($get_url == $own_url) {
                return true;
            }
        }
    }
    //CREATE CAPTCH
    public function captcha($sess = "captcha") {
        $image = @imagecreatetruecolor(120, 30) or die("Cannot Initialize new GD image stream");
        $background = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
        imagefill($image, 0, 0, $background);
        $linecolor = imagecolorallocate($image, 0xCC, 0xCC, 0xCC);
        $textcolor = imagecolorallocate($image, 0x33, 0x33, 0x33);
        for ($i = 0;$i < 6;$i++) {
            imagesetthickness($image, 1);
            imageline($image, 0, rand(0, 30), 120, rand(0, 30), $linecolor);
        }
        $digit = '';
        for ($x = 15;$x <= 95;$x+= 20) {
            $digit.= ($num = rand(0, 9));
            imagechar($image, rand(3, 5), $x, rand(2, 14), $num, $textcolor);
        }
        $this->create_session($sess, $digit);
        header('Content-type: image/png');
        imagepng($image);
        imagedestroy($image);
    }
    //CHECK CAPTCHA
    public function is_captcha($sess = "captcha") {
        if (isset($_POST['captcha_val']) && !empty($_POST['captcha_val'])) {
            if ($_POST['captcha_val'] === $this->get_session($sess)) {
                return true;
            }
        }
    }
    //CAPTCHA REFRESH
    public function captcha_script() {
        return '<script>
          function captcha_refresh() {
          document.getElementById("captcha_img").src = "captcha.php?" + new Date().getTime();
         }
      </script>';
    }
    //SHOW CAPTHCA IMAGE
    public function captcha_image($file = 'captcha.php', $height = '', $width = '') {
        return '<img src="' . $file . '?' . rand() . '" onclick="captcha_refresh()" id="captcha_img" height="' . $height . '" width="' . $width . '">';
    }
    //CAPTTCH FORM
    public function captcha_form() {
        echo '<input type="text" name="captcha_val" placeholder="Enter captcha" id="captcha_val" class="captcha_val" required>';
    }
    //GET A TO Z IN A STRING
    public function lower_string($start = 0, $end = 26) {
        return substr('abcdefghijklmnopqrstuvwxyz', $start, $end);
    }
    //GET A TO Z IN UPPER
    public function upper_string($start = 0, $end = 26) {
        return substr('ABCDEFGIJKLMNOPQRSTUVWXYZ', $start, $end);
    }
    //CREATE OTP
    public function otp($len = 6, $session_name = "otp") {
        $start = $this->int(str_repeat("1", $len));
        $end = $this->int(str_repeat("9", $len));
        $otp = $this->random($start, $end);
        $this->create_session($session_name, $otp);
        return $otp;
    }
    //VALIDATE OTP
    public function is_otp($otp, $session_name = "otp") {
        if (isset($otp) && !empty($otp)) {
            if ($otp == $this->get_session($session_name)) {
                return true;
            }
        }
    }
    //CREATE ERROR
    public function show_error($err_msg, $type = E_USER_ERROR) {
        return trigger_error($err_msg, $type);
    }
    //SET ALERT
    public function set_alert($msg, $type) {
        if ($type == "error") {
            $_SESSION['alert'] = '<div class="alert alert-danger alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                              <div class="alert-icon">
                                <i class="fa fa-times"></i>
                              </div>
                              <div class="alert-message">
                                <span>' . $msg . '</span>
                              </div>
                            </div>';
        } else {
            $_SESSION['alert'] = '<div class="alert alert-success alert-dismissible" role="alert">
                            <button type="button" class="close" data-dismiss="alert">×</button>
                              <div class="alert-icon">
                                <i class="fa fa-check"></i>
                              </div>
                              <div class="alert-message">
                                <span>' . $msg . '</span>
                              </div>
                            </div>';
        }
    }
    // GET ALERT
    public function get_alert() {
        if (isset($_SESSION['alert'])) {
            echo $_SESSION['alert'];
            unset($_SESSION['alert']);
        }
    }
    // PAGE BACK
    public function back() {
        echo "<script>window.history.back()</script>";
    }
    // SET WORD
    public function get_words($sentence, $count = 10) {
        preg_match("/(?:\w+(?:\W+|$)){0,$count}/", $sentence, $matches);
        return $matches[0];
    }
    // TEXT HYPERLINK
    public function text_hyperlink($text) {
        $url_pattern = '/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/';
        $str = preg_replace($url_pattern, '<a href="$0">$0</a>', $text);
        return $str;
    }
    public function bsToImg($base64_string, $output_file) {
        $ifp = fopen($output_file, 'wb');
        $data = explode(',', $base64_string);
        fwrite($ifp, base64_decode($data[1]));
        fclose($ifp);
        return $output_file;
    }
    // EXPORT TO CSV
    public function exportCSV($file_name = '') {
        if (empty($file_name)) {
            $file_name = $this->auth_key(30);
        }
        $ext = $this->split($file_name, ".");
        if (end($ext) != "csv") {
            $file_name.= ".csv";
        }
        header('Content-Type: application/csv');
        header('Content-disposition: attachment; filename=' . $file_name);
    }
    // EXPORT TO EXCEL
    public function exportEXCEL($file_name = '') {
        if (empty($file_name)) {
            $file_name = $this->auth_key(30);
        }
        $ext = $this->split($file_name, ".");
        if (end($ext) != "xls") {
            $file_name.= ".xls";
        }
        header('Content-Type: application/xls');
        header('Content-disposition: attachment; filename=' . $file_name);
    }
    // VALIDATE POST REQUEST BY KEY
    public function postValidate($key) {
        if (isset($_POST[$key]) && !empty($_POST[$key])) {
            return true;
        } else {
            return false;
        }
    }
    // VALIDATE GET REQUEST BY KEY
    public function getValidate($key) {
        if (isset($_GET[$key]) && !empty($_GET[$key])) {
            return true;
        } else {
            return false;
        }
    }
    //DATE RANGE
    public function dateRange($first, $last, $step = '+1 day', $format = 'Y-m-d') {
        $dates = [];
        $current = strtotime($first);
        $last = strtotime($last);
        while($current <= $last) {
            $dates[] = date( $format, $current );
            $current = strtotime( $step, $current );
        }
        return $dates;
    }
    // SHOW PHP ERROR
    public function showAllError(){
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    }
    // MAKE DATA FOR SQL IN FUNCTION
    public function makeMulti($myArray, $type = "str"){
        if(is_array($myArray)){
            $myArray = implode(",", $myArray);
            $myArray = array_unique(explode(",", $myArray));
            $myArray = array_map("trim", $myArray);
            $myArray  = array_filter($myArray);
            $myArray = implode(",", $myArray);
            
        }
        
        return ($type == "str") ? "'".$this->replace(",", "','", $myArray)."'" : $myArray;
    }
    // PAGE LOADER
    public function pageLoader($type = "hide"){
        $type = ($type == "hide") ? "none" : "block";
        return '<div class="loading" id="loader" style="display: '.$type.';"></div>';
    }
    // GROUP CONCAT FUNCTION
    public function groupConcat($array, $key){
        return implode(",", array_column($array, $key));
    }
    // ADD QUOOTE
    public function addQuote($val){
        return "'".$val."'";
    }
    // REMOVE SPECIAL CHAR
    public function removeSpecialChar($string){
        $specialChar = ["~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "-", "-", "+", "{", "[", "]", "}", "\\", "|","/", '"', "'", ".", ",", "=", ":", ";", "<", ">"];
        $result = str_replace($specialChar, '', trim($string));
        $result =  $this->lower(str_replace(" " , "_", $result));
        return $result;
    }
    // IMPORT CSV/EXCEL
    public function importExcelCSV($file, $file_ext_array = ["csv", "xls", "xlsx"], $type = "assoc"){
        $filename = $file['name'];
        $file_tmp_name = $file['tmp_name'];
        $file_ext = $this->fileExtension($filename);

        if (!in_array($file_ext, $file_ext_array)) {
            return ['status' => 'error', 'data' => 'ext_not_allowed'];
            exit();
        }
        if($file_ext == "csv"){
            $handle = fopen($file_tmp_name, "r");
            $file_head = fgetcsv($handle);
            $i = 0;
            $mainResults = [];
            while(($filesop = fgetcsv($handle, 1000, ",")) !== false){
                $mainResults[] = ($type == "assoc") ? array_combine(array_map(array($this, 'removeSpecialChar'), $file_head), $filesop) : $filesop;
                
            }
            fclose($handle);
        }else{
            if (!file_exists(__DIR__."/excel/src/SimpleXLSX.php")) {
            throw new Exception("excel/src/SimpleXLSX.php file not found!");
            exit();
        }
            require_once (__DIR__."/excel/src/SimpleXLSX.php");
            if ( $xlsx = SimpleXLSX::parse($file_tmp_name)) {
                $allRows = $xlsx->rows();
                $file_head = $this->first($allRows);
                foreach(array_slice($allRows, 1) as $rowArr){
                    $mainResults[] = ($type == "assoc") ? array_combine(array_map(array($this, 'removeSpecialChar'), $file_head), $rowArr) : $rowArr;
                }

            } else {
                return ['status' => 'error', 'data' => SimpleXLSX::parseError()];
                exit();
            }


        }
        return ['status' => 'success', 'data' => $mainResults];
    }
    // HTML TO PDF
    public function html2pdf($title = "PDF", $file_name = 'mypdf.pdf', $status = "view", $type = "p", $logo = '', $logo_size = array(120, 120), $logo_opacity = 0.1) {
        if (!file_exists(__DIR__ . '/mpdf/autoload.php')) {
            throw new Exception("mpdf/autoload.php file not found!");
            exit();
        }
        require_once (__DIR__ . '/mpdf/autoload.php');
        if (empty($file_name)) {
            $file_name = $this->auth_key(30);
        }
        $ext = $this->split($file_name, ".");
        if (end($ext) != "pdf") {
            $file_name.= ".pdf";
        }

        $mpdf = new \Mpdf\Mpdf();
        if ($type == "l") {
            echo '<style>@page { sheet-size: A3-L; }@page bigger { sheet-size: 420mm 370mm; }@page toc { sheet-size: A4; }h1.bigsection {page-break-before: always;page: bigger;}</style>';
        }
        
        $html = ob_get_contents();
        if (!empty($logo)) {
            $mpdf->SetWatermarkImage($logo, $logo_opacity, $logo_size);
            $mpdf->showWatermarkImage = true;
        }
        $mpdf->WriteHTML($html);
        $mpdf->SetTitle($title);
        ob_end_clean();
        ob_end_flush();
        if($status == "view"){
            $mpdf->Output();
        }elseif($status == "save"){
            $mpdf->Output($file_name, "F");
        }else{
            $mpdf->Output($file_name, "D");
        }
    }
    // LINK MPDF
    public function linkMpdf(){
        if (!file_exists(__DIR__ . '/mpdf/autoload.php')) {
            throw new Exception("mpdf/autoload.php file not found!");
            exit();
        }
        require_once (__DIR__ . '/mpdf/autoload.php');
    }
    // SEND EMAIL FUNCTION
    public function sendEmail($email_host_info = [], $to_emails = [],  $email_body = '', $files_name = [], $cc_email = [], $bcc_email = []){
        if (!file_exists(__DIR__."/PHPMailer/vendor/autoload.php")) {
            throw new Exception("PHPMailer/vendor/autoload.php file not found!");
            exit();
        }
        require_once (__DIR__."/PHPMailer/vendor/autoload.php");

        // HOST INFO ARRAY SAMPLE
        // $host_info = ["username" => 'FROM_EMAIL', "password" => 'FROM_EMAIL_PASSWORD', "from_name" => 'FROM_NAME', "subject" => 'MAIL_SUBJECT'];

        $fromEmail = $email_host_info['username'];
        $password = $email_host_info['password'];

        $fromName = $email_host_info['from_name'];
        $subject = $email_host_info['subject'];

            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->SMTPAuth = true;
            $mail->SMTPDebug = 1;
            $mail->SMTPSecure = "ssl";
            $mail->Host = "smtp.gmail.com";
            $mail->Port = 465;
            $mail->Username = $fromEmail;
            $mail->Password = $password;
            $mail->ClearReplyTos();
            $mail->SetFrom($fromEmail, $fromName);
            foreach($files_name as $file_name){
                $split = explode("/", $file_name);
                $filename = end($split);
                $mail->addStringAttachment(file_get_contents($file_name), $filename);
            }

            foreach($cc_email as $cc){
                $mail->addCC($cc);
            }

            foreach($bcc_email as $bcc){
                $mail->addBcc($bcc);
            }
            $mail->Subject = $subject;
            $mail->MsgHTML($email_body);

            foreach($to_emails as $to_email){
                $mail->AddAddress($to_email);
            }
            $mail->Send();
    }
    // MULTU SPLIT
    public function multiSplit($text, $where){
        $pattern = "/[\s:".$where."]/";
        return  preg_split($pattern, $text);
    }
    // READ DIRECTORY
    public function readDirectory($dirname, $type = 'all', $ext = "*"){
        if (strpos($dirname, '/') == false){
            $dirname .= "/";
        }
        if(is_dir($dirname)){
            if (strpos($ext, '*') == false){
                $dirname .= "*";
            }
            if($type == "all"){
                $files = glob($dirname.$ext);
            }elseif($type == "file"){
                $files = array_filter(glob($dirname.$ext), 'is_file');
            }else{
                $files = glob($dirname.$ext, GLOB_ONLYDIR);
            }
            return $files;
        }else{
            throw new Exception($dirname . " directory not exists!");
        }
    }
    // RECURSIVE DIRECTORY
    public function recursiveDirectory($dirname, $maxdepth=10, $depth=0){
        if ($depth >= $maxdepth) {
            return false;
        }
        $subdirectories = array();
        $files = array();
        if (is_dir($dirname) && is_readable($dirname)) {
        $d = dir($dirname);
        while (($f = $d->read()) !== false) {
           $file = $d->path.'/'.$f;
               if (('.'==$f) || ('..'==$f)) {
                continue;
               }
            if (is_dir($dirname.'/'.$f)) {
                array_push($subdirectories,$dirname.'/'.$f);
            } else {
                array_push($files,$dirname.'/'.$f);
           }
        }
            $d->close();
            foreach ($subdirectories as $subdirectory) {
                $files = array_merge($files, $this->recursiveDirectory($subdirectory, $maxdepth, $depth+1));
            }
        }
         return $files;
    }
    // CREATE ZIP FILE
    public function createZipFile($filename, $files, $password = '', $method = "create"){
        if (strpos($filename, '.zip') == false){
            $filename .= ".zip";
        }

        $zip = new ZipArchive();
        $zipStatus =  $zip->open($filename,  ZipArchive::CREATE);
        if ($zipStatus !== true) {
            throw new RuntimeException('Failed to create zip archive. (Status code : )'. $zipStatus);
        }
        if(!empty($password)){
            if (!$zip->setPassword($password)) {
                throw new RuntimeException('Set password failed');
            }
        }
        foreach($files as $file){
            $zip->addFile($file);
            if(!empty($password)){
                $zip->setEncryptionName($file, ZipArchive::EM_AES_256);
            }
        }
        
        $zip->close();
    
        if($method != "create" && file_exists($filename)){
            header('Cache-Control: no-cache, must-revalidate');
            header('Content-Type: application/x-zip'); 
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Content-Length: ' . filesize($filename));
            readfile($filename);
            unlink($filename);
        }
    }

    // CREATE QR CODE
    function createQrCode($text = "Developed by : Sadirul Islam", $output_path = 'qrcode.png', $type = "save", $logo = ''){
        if (!file_exists(__DIR__."/phpqrcode/qrlib.php")) {
            throw new Exception("phpqrcode/qrlib.php file not found!");
            exit();
        }
        require_once (__DIR__."/phpqrcode/qrlib.php");
        QRcode::png($text, $output_path , QR_ECLEVEL_H, 20);

        if(!empty($logo)){
            $QR = imagecreatefrompng($output_path);
            $logo = imagecreatefromstring(file_get_contents($logo));
            $QR_width = imagesx($QR);
            $QR_height = imagesy($QR);
            $logo_width = imagesx($logo);
            $logo_height = imagesy($logo);
            $logo_qr_width = $QR_width / 8;
            $scale = $logo_width / $logo_qr_width;
            $logo_qr_height = $logo_height / $scale;
            imagecopyresampled($QR, $logo, $QR_width / 2.3, $QR_height / 2.3, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
            imagepng($QR, $output_path);
        }
        if($type == "view"){
            echo file_get_contents($output_path);
            header('Content-type: image/png');
        }else{
            return ["text" => $text, "path" => $output_path, "file_size_bytes" => filesize($output_path), "has_logo" => empty($logo) ? "no" : "yes"];
        }
    }

    //NUMBER TO WORDS
    public function numberToWords($number){
        $no = round($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array('0' => '', '1' => 'one', '2' => 'two','3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six','7' => 'seven', '8' => 'eight', '9' => 'nine','10' => 'ten', '11' => 'eleven', '12' => 'twelve','13' => 'thirteen', '14' => 'fourteen','15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen','18' => 'eighteen', '19' =>'nineteen', '20' => 'twenty','30' => 'thirty', '40' => 'forty', '50' => 'fifty','60' => 'sixty', '70' => 'seventy','80' => 'eighty', '90' => 'ninety');
        $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] .
                " " . $digits[$counter] . $plural . " " . $hundred
                :
                $words[floor($number / 10) * 10]
                . " " . $words[$number % 10] . " "
                . $digits[$counter] . $plural . " " . $hundred;
            } else {
                $str[] = null;
            }
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ?
        "." . $words[$point / 10] . " " . 
        $words[$point = $point % 10] : '';
        if (!empty($points)) {
            return $result . "rupees  " . $points . " paise";
        }else{
            return $result . "rupees  ";
        }
    }

    //PERCENTAGE
    public function getPercentahe($number, $percent){
        return ($percent / 100) * $number;
    }

    // DAYS TO d/m/Y
    public function dateToDMY($to, $from = ''){
        $from = empty($from) ? date("Y-m-d H:i:s") : $from;
        $date1 = new DateTime($from);
        $date2 = new DateTime($to);
        $interval = $date2->diff($date1);
        return ["days" => $interval->d, "months" => $interval->m, "years" => $interval->y, "hours" => $interval->h, "minute" => $interval->i, "second" => $interval->s, "total_days" => $interval->days];
    }

} //END USER CLASS

?>
