<?php

/**
 * Post Class
 *
 * This PHP class provides methods for adding employees and jobs.
 *
 * Usage:
 * 1. Include this class in your project.
 * 2. Create an instance of the class to access the provided methods.
 * 3. Call the appropriate method to add new employees or jobs with the provided data.
 *
 * Example Usage:
 * ```
 * $post = new Post();
 * $employeeData = ... // prepare employee data as an associative array or object
 * $addedEmployee = $post->add_employees($employeeData);
 *
 * $jobData = ... // prepare job data as an associative array or object
 * $addedJob = $post->add_jobs($jobData);
 * ```
 *
 * Note: Customize the methods as needed to handle the addition of data to your actual data source (e.g., database, API).
 */

require_once "global.php"; 

class Post extends GlobalMethods{
    private $pdo;

    public function __construct(\PDO $pdo){
        $this->pdo = $pdo;
    }

    public function executeQuery($sql){
        $data = array(); //place to store records retrieved for db
        $errmsg = ""; //initialized error message variable
        $code = 0; //initialize status code variable

        try{
            if($result = $this->pdo->query($sql)->fetchAll()){ //retrieved records from db, returns false if no records found
                foreach($result as $record){
                    array_push($data, $record);
                }
                $code = 200;
                $result = null;
                return array("code"=>$code, "data"=>$data);
            }
            else{
                //if no record found, assign corresponding values to error messages/status
                $errmsg = "No records found";
                $code = 404;
            }
        }
        catch(\PDOException $e){
            //PDO errors, mysql errors
            $errmsg = $e->getMessage();
            $code = 403;
        }
        return array("code"=>$code, "errmsg"=>$errmsg);
    }

    public function get_records($table, $condition=null){
        $sqlString = "SELECT * FROM $table";
        if($condition != null){
            $sqlString .= " WHERE " . $condition;
        }
        
        $result = $this->executeQuery($sqlString);

        if($result['code']==200){
            return $this->sendPayload($result['data'], "success", "Successfully retrieved records.", $result['code']);
        }
        
        return $this->sendPayload(null, "failed", "Failed to retrieve records.", $result['code']);
    }
    
   

    /**
     * Add a new employee with the provided data.
     *
     * @param array|object $data
     *   The data representing the new employee.
     *
     * @return array|object
     *   The added employee data.
     */


    public function login_user($data) {
        if (!isset($data->Email) || !isset($data->PasswordHash)) {
            return $this->sendPayload(null, "failed", "Email and PasswordHash are required fields.", 400);
        }

        $email = $data->Email;
        $password = $data->PasswordHash;
        $condition = "Email='$email' AND PasswordHash='$password'";
        
        $userData = $this->get_records("users", $condition);
    
        if(!empty($userData['payload'])) {
            session_start();
            $_SESSION['UserID'] = $userData['payload'][0]['UserID'];
            return $userData;
        }
        else {
            return array("errmsg"=>"Invalid username or password");
        }
    }

    
    
     // ADDING DATA
    public function add_user($data){
        $sql = "INSERT INTO users(
        Email, FirstName, LastName, PasswordHash, UsersImage) 
        VALUES (?,?,?,?,?)";
        try{
            $statement = $this->pdo->prepare($sql);
            $statement->execute(
                [
                    $data->Email,
                    $data->FirstName,
                    $data->LastName,
                    $data->PasswordHash,
                    $data->UsersImage
                ]
            );
            return $this->sendPayload(null, "success", "Successfully created a new record.", 200);
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }
       
        return $this->sendPayload(null, "failed", $errmsg, $code);
    }


    // UPDATE
    public function edit_user($data) {
        if ($data === null) {
            return $this->sendPayload(null, "failed", "Data object is null.", 400);
        }

        $sql = "UPDATE users SET Email=?, FirstName=?, LastName=?, PasswordHash=?, UsersImage=? WHERE UserID = ?";
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute(
                [
                    $data->Email,
                    $data->FirstName,
                    $data->LastName,
                    $data->PasswordHash,
                    $data->UsersImage,
                    $data->UserID
                ]
            );
            return $this->sendPayload(null, "success", "Successfully updated record.", 200);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
    
        return $this->sendPayload(null, "failed", $errmsg, $code);
    }

    public function uploadProfile($data) {
        if (!isset($_FILES['file'])) {
            return $this->sendPayload(null, "failed", "No file uploaded.", 400);
        }
    
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return $this->sendPayload(null, "failed", "File upload error.", 400);
        }
    
        $uploadDirectory = '../uploads/profile/';
    
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }
    
        $destination = $uploadDirectory . $_FILES['file']['name'];
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
            return $this->sendPayload(null, "failed", "Failed to move uploaded file.", 400);
        }
    
        return $this->sendPayload(null, "success", "File uploaded successfully.", 200);
    }
    

    public function backupFile($data){
        $sql = "INSERT INTO backup SELECT * FROM files WHERE FileID=?";
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                $data
            ]);
            return $this->sendPayload(null, "success", "File backed up successfully.", 200);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
        return $this->sendPayload(null, "failed", $errmsg, $code);
    }
    
    public function  recoverBackup($data){
        $sql = "INSERT INTO files SELECT * FROM backup WHERE FileID=?";
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                $data
            ]);
            return $this->sendPayload(null, "success", "File recovered successfully.", 200);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
        return $this->sendPayload(null, "failed", $errmsg, $code);
    }

    public function createFolder($data){
         $currentTime = date('Y-m-d');
         $sql = "INSERT INTO folders (
             UserID, FolderName, LastModified)          
             VALUES (?,?,?)";
               try{
                      $statement = $this->pdo->prepare($sql);
                      $statement->execute(
                          [
                       $data->UserID,
                       $data->FolderName,
                     $currentTime
                       ]
                );
                return $this->sendPayload(null, "success", "Successfully created a new record.", 200);
            }
            catch(\PDOException $e){
                $errmsg = $e->getMessage();
                $code = 400;
            }
           
            return $this->sendPayload(null, "failed", $errmsg, $code);
    }

    public function deleteRecoveryFile($data){
        $sql = "DELETE FROM backup WHERE FileID=?";
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                $data
            ]);
            return $this->sendPayload(null, "success", "File deleted from recovery.", 200);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
        return $this->sendPayload(null, "failed", $errmsg, $code);
    }
    

    public function edit_files($data){
        $currentTime = date('Y-m-d');
        if ($data === null) {
            return $this->sendPayload(null, "failed", "Data object is null.", 400);
        }
    
        $sql = "UPDATE files SET FileNames=?, LastModified=? WHERE FileID = ?";
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                $data->FileNames, 
                $currentTime,
                $data->FileID
            ]);

            $uploadDirectory = '../uploads/';
            $oldFileName = $data->oldFileName; 
            $newFileName = $data->FileNames;
    
            $oldFilePath = $uploadDirectory . $oldFileName;
            $newFilePath = $uploadDirectory . $newFileName;
    
            if (file_exists($oldFilePath)) {
                rename($oldFilePath, $newFilePath);
                return $this->sendPayload(null, "success", "Successfully updated record and file.", 200);
            } else {
                return $this->sendPayload(null, "failed", "Old file does not exist.", 400);
            }

        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
            return $this->sendPayload(null, "failed", $errmsg, $code);
        }
    }
    


    //DELETE / Archive
    public function delete_files($id){
        $currentTime = date('Y-m-d');
        $sql = "UPDATE files SET isArchived=1, LastModified=? WHERE FileID= ?";
        try{
            $statement = $this->pdo->prepare($sql);
            $statement->execute(
                [
                    $currentTime,
                    $id
                ]
            );
            return $this->sendPayload(null, "success", "Successfully updated record.", 200);
    
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
            return $this->sendPayload(null, "failed", $errmsg, $code);
        }

    }

    public function updateFolderName($data){
        $currentTime = date('Y-m-d');
        $sql = "UPDATE folders SET FolderName=?, LastModified=? WHERE FolderID = ?";
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                $data->FolderName, 
                $currentTime,
                $data->FolderID
            ]);
            return $this->sendPayload(null, "success", "Successfully updated record.", 200);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
            return $this->sendPayload(null, "failed", $errmsg, $code);
        }
    }

    public function restoreFolder($data) {
        $currentTime = date('Y-m-d');
        try {
    
            // Begin transaction
            $this->pdo->beginTransaction();
    
            $sql = "UPDATE files SET isArchived=0, LastModified=? WHERE FolderID=?";
            $filesStatement = $this->pdo->prepare($sql);
            $filesStatement->execute([
                $currentTime, 
                $data
            ]);

            $sql = "UPDATE folders SET isArchived=0, LastModified=? WHERE FolderID=?";
            $foldersStatement = $this->pdo->prepare($sql);
            $foldersStatement->execute([
                $currentTime, 
                $data
            ]);

            $this->pdo->commit();
    
            return $this->sendPayload(null, "success", "Successfully updated records.", 200);
        } catch(\PDOException $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            $errmsg = $e->getMessage();
            $code = 400;
    
            // Log error message for debugging
            error_log("Error: " . $errmsg);
    
            return $this->sendPayload(null, "failed", $errmsg, $code);
        }
    }

    public function selfRemoveAccess($data){
        $sql = "DELETE FROM collaborations WHERE CollabID=?";
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                $data
            ]);
            return $this->sendPayload(null, "success", "Access removed successfully.", 200);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
        return $this->sendPayload(null, "failed", $errmsg, $code);
    }

    public function addCollaborator($data) {
        if (!isset($data->Email) || !isset($data->FileID)) {
            return $this->sendPayload(null, "failed", "Email and FileID are required fields.", 400);
        }
    
        $email = $data->Email;
        $userData = $this->get_records("users", "Email='$email'");
        if (empty($userData['payload'])) {
            return $this->sendPayload(null, "failed", "User not found.", 404);
        }
    
        $userID = $userData['payload'][0]['UserID'];
        
        // Check if the user is already a collaborator for this file
        $existingCollaboration = $this->get_records("collaborations", "UserID=$userID AND FileID=$data->FileID");
        if (!empty($existingCollaboration['payload'])) {
            return $this->sendPayload(null, "failed", "User is already a collaborator for this file.", 400);
        }
    
        $sql = "INSERT INTO collaborations (UserID, FileID, CollabType) VALUES (?,?,?)";
        
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                $userID,
                $data->FileID,
                $data->CollabType
            ]);
    
            return $this->sendPayload(null, "success", "Collaborator added successfully.", 200);
        } catch(\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
    
        return $this->sendPayload(null, "failed", $errmsg, $code);
    }
    

    public function deleteFolderandFiles($data){
        try {
    
            // Begin transaction
            $this->pdo->beginTransaction();
    
            $sql = "DELETE FROM files WHERE FolderID=?";
            $filesStatement = $this->pdo->prepare($sql);
            $filesStatement->execute([
                $data
            ]);

            $sql = "DELETE FROM folders WHERE FolderID=?";
            $foldersStatement = $this->pdo->prepare($sql);
            $foldersStatement->execute([
                $data
            ]);

            $this->pdo->commit();
    
            return $this->sendPayload(null, "success", "Successfully updated records.", 200);
        } catch(\PDOException $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            $errmsg = $e->getMessage();
            $code = 400;
    
            // Log error message for debugging
            error_log("Error: " . $errmsg);
    
            return $this->sendPayload(null, "failed", $errmsg, $code);
        }
    }

    //Archiving a folder
    public function archiveFolderAndFiles($data) {
        $currentTime = date('Y-m-d');
        try {
    
            // Begin transaction
            $this->pdo->beginTransaction();
    
            $sql = "UPDATE files SET isArchived=1, LastModified=? WHERE FolderID=?";
            $filesStatement = $this->pdo->prepare($sql);
            $filesStatement->execute([
                $currentTime, 
                $data
            ]);

            $sql = "UPDATE folders SET isArchived=1, LastModified=? WHERE FolderID=?";
            $foldersStatement = $this->pdo->prepare($sql);
            $foldersStatement->execute([
                $currentTime, 
                $data
            ]);

            $this->pdo->commit();
    
            return $this->sendPayload(null, "success", "Successfully updated records.", 200);
        } catch(\PDOException $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            $errmsg = $e->getMessage();
            $code = 400;
    
            // Log error message for debugging
            error_log("Error: " . $errmsg);
    
            return $this->sendPayload(null, "failed", $errmsg, $code);
        }
    }
    
    

    //Restore file
    public function restoreFile($data) {
        $currentTime = date('Y-m-d');
        $sql = "UPDATE files SET isArchived=0, LastModified=? WHERE FileID=?";
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                $currentTime,
                $data
            ]);
            return $this->sendPayload(null, "success", "File restored successfully.", 200);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
        return $this->sendPayload(null, "failed", $errmsg, $code);
    }
   
    public function uploadFile($data) {
        $UserID = $_POST['userId'];
        $FolderID = isset($_POST['folderID']) ? $_POST['folderID'] : null;

        if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
            $filename = $_FILES["file"]["name"];
            $filesize = $_FILES["file"]["size"];
            $filetype = $_FILES["file"]["type"];
            
            $path_parts = pathinfo($_FILES["file"]["name"]);
            $extension = $path_parts['extension'];
            
            $uploadDirectory = '../uploads/';
    
            if (!file_exists($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }
    
            $destination = $uploadDirectory . $filename;
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
                return $this->sendPayload(null, "failed", "Failed to move uploaded file.", 400);
            }
            
            $sql = "INSERT INTO files (FolderID, UserID, FileTypeIdentifier, FileNames, FileSize, LastModified)
                    VALUES (?,?,?,?,?,?)";
    
            try {
                $statement = $this->pdo->prepare($sql);
                $statement->execute([
                    $FolderID,
                    $UserID,
                    $extension,
                    $filename,
                    $filesize,
                    date('Y-m-d') 
                ]);
                
                return $this->sendPayload(null, "success", "File uploaded successfully.", 200);
            } catch (\PDOException $e) {
                $errmsg = $e->getMessage();
                $code = 400;
            }
            return $this->sendPayload(null, "failed", $errmsg, $code);
        } else {
            return $this->sendPayload(null, "failed", "File upload error.", 400);
        }
    }
    

    public function deletePermanently($data) {
        $sql = "DELETE FROM files WHERE FileID=?";
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute([
                $data
            ]);
            return $this->sendPayload(null, "success", "Files deleted permanently.", 200);
        } catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
        return $this->sendPayload(null, "failed", $errmsg, $code);
    }


}



