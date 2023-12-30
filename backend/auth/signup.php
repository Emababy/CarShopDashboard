<?php 

include "../connect.php";

$Username = filter_var($_POST['Username'], FILTER_SANITIZE_STRING);
$Email = filter_var($_POST['Email'], FILTER_SANITIZE_EMAIL);
$Password = $_POST['Password'];

$HashedPass = password_hash($Password, PASSWORD_DEFAULT);

$stmt = $con->prepare("INSERT INTO users (Username, Email, Password, Date)
                    VALUES (:UserName, :UserEmail, :UserPassword, now())");
$stmt->execute(array(
    'UserName' => $Username,
    'UserEmail' => $Email,
    'UserPassword' => $HashedPass
));

$count = $stmt->rowCount();

if ($count > 0 ){
    echo json_encode(array("status" => "200" , "message" => "Success"));
} else {
    echo json_encode(array("status" => "400" , "message" => "error"));
}

?>