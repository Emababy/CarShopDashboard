<?php 

include "connect.php";

// test connection:
// $stmt = $con->prepare("SELECT * FROM cars");
// $stmt->execute();
// $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
// print_r($cars);

// explain PDO statement:
// $stmt = $con->prepare("SELECT * FROM cars");
// $stmt->execute();
// $stmt->fetchAll(PDO::FETCH_ASSOC); // fetch all data (rows) from database
// $stmt->fetch(); // fetch row of data from database
// $stmt->fetchColumn(); // fetch column of data from database but i need to write -> select (ex->name) from (ex->cars) where id = (ex->$id);
// $count = $stmt->rowCount(); // count rows of data from database if there is user with that id

header('Access-Control-Allow-Origin: *');

$action = isset($_GET['action']) ? $_GET['action'] : 'read';

try {
    switch ($action) {
        case 'read':
            // read data from database:
            $stmt = $con->prepare("SELECT * FROM cars");
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if ($data) {
                echo json_encode(array("status" => "200", "message" => "success", "cars" => $data));
            } else {
                echo json_encode(array("status" => "400", "message" => "Add Cars To Show"));
            }
            break;

        
            case 'update':
                $name = isset($_POST['name']) ? filter_var($_POST['name'], FILTER_SANITIZE_STRING) : null;
                $description = isset($_POST['description']) ? filter_var($_POST['description'], FILTER_SANITIZE_STRING) : null;
                $price = isset($_POST['price']) ? filter_var($_POST['price'], FILTER_SANITIZE_NUMBER_INT) : null;
                $modelYear = isset($_POST['modelYear']) ? filter_var($_POST['modelYear'], FILTER_SANITIZE_NUMBER_INT) : null;
                $id = isset($_POST['ID']) ? filter_var($_POST['ID'], FILTER_SANITIZE_NUMBER_INT) : null;

                $stmt = $con->prepare("UPDATE cars SET name = ?, description = ?, price = ?, modelYear = ? WHERE id = ?");
                $stmt->execute([$name, $description, $price, $modelYear, $id]);

                $count = $stmt->rowCount();

                if ($count > 0) {
                    echo json_encode(array("status" => "201", "message" => "Updated successfully"));
                } else {
                    echo json_encode(array("status" => "400", "message" => "Failed in updating"));
                }
                break;
            

        case 'insert':
            // insert data in database:

            $name = isset($_POST['name']) ? filter_var($_POST['name'],FILTER_SANITIZE_STRING) : null;
            $description = isset($_POST['description']) ? filter_var($_POST['description'],FILTER_SANITIZE_STRING) : null;
            $image = isset($_POST['image']) ? $_POST['image'] : null;
            $price = isset($_POST['price']) ? filter_var($_POST['price'],FILTER_SANITIZE_NUMBER_INT) : null;
            $modelYear = isset($_POST['modelYear']) ? filter_var($_POST['modelYear'],FILTER_SANITIZE_NUMBER_INT) : null;
            $id = isset($_POST['id']) ? filter_var($_POST['id'],FILTER_SANITIZE_NUMBER_INT) : null;

            $uploadedPath = "";

            if (isset($_FILES['image']['name'])) {
                $image_name = $_FILES['image']['name'];
                $validExtension = array("png", "jpg", "jpeg", "gif");
                $extension = pathinfo($image_name, PATHINFO_EXTENSION);
            
                if (in_array($extension, $validExtension)) {
                    $newGeneratedImageName = generateRandomString(60);
                    $uploadedPath = "../frontend/src/assets/carsImages/" . $newGeneratedImageName . "." . $extension;
            
                    if (file_exists($uploadedPath)) {
                        $newGeneratedImageName = generateRandomString(60);
                        $image = $newGeneratedImageName . "." . $extension;
                        $uploadedPath = "../frontend/src/assets/carsImages/" . $image;
                    } else {
                        $image = $newGeneratedImageName . "." . $extension;
                    }
            
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadedPath)) {
                        echo json_encode(array('status' => '201', 'message' => 'Image uploaded successfully'));
            
                        // Database insertion
                        $stmt = $con->prepare("INSERT INTO cars (name, description, image, price, modelYear) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $description, $image, $price, $modelYear]);
            
                        $count = $stmt->rowCount();
            
                        if ($count > 0) {
                            echo json_encode(array("status" => "201", "message" => "Success: Image and data inserted."));
                        } else {
                            echo json_encode(array("status" => "400", "message" => "Error: Data insertion failed."));
                        }
                    } else {
                        echo json_encode(array('status' => '400', 'message' => 'Image upload failed.'));
                    }
                } else {
                    echo json_encode(array('status' => '400', 'message' => 'Invalid file extension.'));
                }
            } else {
                echo json_encode(array('status' => '400', 'message' => 'Image Or Data not provided.'));
            }

            break;

        case 'delete':
            // delete data in database
            $image = isset($_POST['image']) ? $_POST['image'] : null;
            $id = isset($_POST['id']) ? filter_var($_POST['id'],FILTER_SANITIZE_NUMBER_INT) : null;

            $stmt = $con->prepare("SELECT * FROM cars WHERE id = ?");
            $stmt->execute([$id]);

            $Exist = $stmt->rowCount();

            if($Exist) {

                $stmt = $con->prepare("DELETE FROM cars WHERE id = ?");
                $stmt->execute([$id]);
                $count = $stmt->rowCount();

                if ($count > 0) {
                    // Delete The
                    $dir = "../frontend/src/assets/carsImages/".$image;
                    if(unlink($dir)){
                        echo json_encode(array("status" => "200", "message" => "The Car Info And Image Deleted successfully"));
                    } else {
                        echo json_encode(array("status" => "400", "message" => "Failed Deleting Image"));
                    }
                } else {
                    echo json_encode(array("status" => "400", "message" => "Deleted Info Failed"));
                }
            }
            break;

        default:
            echo json_encode(array("status" => "400", "message" => "Invalid action"));
    }
} catch (PDOException $e) {
    // Handle database errors
    echo json_encode(array("status" => "500", "message" => "Internal Server Error"));
}
?>


