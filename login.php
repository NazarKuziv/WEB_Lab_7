<?php 

// $pdo = require('db/connect.php');
// if(!$pdo){
//     die();
// }

session_start(); 

include "db/connect.php";

if (isset($_POST['phone_number']) && isset($_POST['password'])) {

    function validate($data){

       $data = trim($data);

       $data = stripslashes($data);

       $data = htmlspecialchars($data);

       return $data;

    }

    $phone_number = validate($_POST['phone_number']);

    $pass = validate($_POST['password']);

    if (empty($phone_number)) {

        header("Location: index.php?error=Phone number is required");

        exit();

    }else if(empty($pass)){

        header("Location: index.php?error=Password is required");

        exit();

    }else{

        $sql = "SELECT * FROM employees WHERE phone_number='$phone_number' AND password='$pass'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {

            $row = mysqli_fetch_assoc($result);

            if ($row['phone_number'] === $phone_number && $row['password'] === $pass) {

                echo "Logged in!";

                $_SESSION['employee_id'] = $row['employee_id'];

                $_SESSION['employee_name'] = $row['employee_name'];

                $_SESSION['level'] = $row['level'];

                header("Location: issuance_literature.php");

                exit();

            }else{

                header("Location: index.php?error=Incorect Phone number or password");

                exit();

            }

        }else{

            header("Location: index.php?error=Incorect Phone number or password");

            exit();

        }

    }

}else{

    header("Location: index.php");

    exit();

}