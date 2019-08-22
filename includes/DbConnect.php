<?php

    class DbConnect{
        private $con;

        function connect(){

            include_once dirname(__FILE__) . '/Constants.php';
            $this ->con = new mysqli(DB_HOST,DB_USER,DB_PSW,DB_NAME);
            if(mysqli_connect_errno()){
                echo "Bağlantı Hatası" . mysqli_connect.error();
                return null;
            }
            return $this->con;
            echo "Bağlandı.";
        
        }
    } 