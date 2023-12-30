<?php 

    include "../connect.php";

    $Username = $_POST['Username'];
    $Password = $_POST['Password'];

    // Check if the user exists
    $stmt = $con->prepare("SELECT * FROM users WHERE Username = ? And Password = ?");
    $stmt->execute(array($Username , $Password));

    $count = $stmt->rowCount();
    if ($count > 0) { 
        echo json_encode(array("status" => "200" , "message" => "Success"));
    } else {   
        echo json_encode(array("status" => "400" , "message" => "error"));
    }

?>