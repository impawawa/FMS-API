<?php
/**
 * Get Class
 *
 * This PHP class provides methods for retrieving data related to employees and jobs.
 *
 * Usage:
 * 1. Include this class in your project.
 * 2. Create an instance of the class to access the provided methods.
 * 3. Call the appropriate method to retrieve the desired data.
 *
 * Example Usage:
 * ```
 * $get = new Get();
 * $employeesData = $get->get_employees();
 * $jobsData = $get->get_jobs();
 * ```
 *
 * Note: Customize the methods as needed to fetch data from your actual data source (e.g., database, API).
 */

require_once "global.php";

class Get extends GlobalMethods{
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
     * Retrieve a list of employees.
     *
     * @return string
     *   A string representing the list of employees.
     */
    public function get_users($id=null){
        $condition = null;
        if($id != null){
                            // LAGING PRIMARY KEY NG TABLE
            $condition = "UserID=$id";
        }
                                    //Laging Table Name
        return $this->get_records("users", $condition);
    }
    public function getFiles(){
        $condition = null;
        return $this->get_records("files", $condition);
    }
    
    public function getFolders(){
        $condition = null;
        return $this->get_records("folders", $condition);
    }

    public function getBackUp(){
        $condition = null;
        return $this->get_records("backup", $condition);
    }
    public function get_collaborations(){
        $condition = null;
        return $this->get_records("collaborations", $condition);
    }

    public function get_files($id){
        $condition = "isArchived=0 AND UserID=$id";                
        return $this->get_records("files", $condition);
    }

    public function get_file($id){
        $condition = "isArchived=0 AND FileID=$id";                 
        return $this->get_records("files", $condition);
    }
    public function get_files_by_folder($id){
        $condition = "isArchived=0 AND FolderID=$id";                
        return $this->get_records("files", $condition);
    }

    public function get_folders($id=null){
        // Condition to retrieve non-archived folders for the specified user
        $condition = "isArchived=0 AND UserID=$id";
    
        // SQL query to retrieve folders along with the count of files for each folder
        $sql = "SELECT f.*, 
                   (SELECT COUNT(*) FROM files WHERE files.FolderID = f.FolderID) AS FileCount
                FROM folders f
                WHERE $condition";
    
        // Execute the SQL query and return the result
        return $this->executeQuery($sql);
    }
    

    public function get_folder($id){
        $condition = "isArchived=0 AND FolderID=$id";
        return $this->get_records("folders", $condition);
    }

    public function get_archived_folders($id){
        $condition = "isArchived=1 AND UserID=$id";
        return $this->get_records("folders", $condition);
    }

    public function getFileByID($id){
        $condition = "FileID=$id";
        return $this->get_records("files", $condition);
    }

    public function getCollaborationFile($data) {
        $subQuery = "(SELECT FileID, COUNT(DISTINCT UserID) AS TotalSharedUsersCount FROM collaborations GROUP BY FileID)";
        
        $condition = "files.isArchived = 0 AND collaborations.UserID = $data";
        $joinCondition = "files.FileID = collaborations.FileID";
        
        $sql = "SELECT files.FileID, files.FileNames, files.FileSize, files.LastModified, files.FileTypeIdentifier, 
                collaborations.CollabType, subquery.TotalSharedUsersCount
                FROM files
                INNER JOIN collaborations ON $joinCondition
                INNER JOIN $subQuery AS subquery ON files.FileID = subquery.FileID
                WHERE $condition
                GROUP BY files.FileID, files.FileNames, files.FileSize, files.LastModified, files.FileTypeIdentifier, collaborations.CollabType";
    
        return $this->executeQuery($sql);
    }

    public function getCollaborations(){
        $sql = "SELECT * FROM collaborations";
        return $this->executeQuery($sql);
    }

    public function getCollaboratorByFile($data){
       
        // Inner join the users and collaborations tables to get the collaborator details
        $sql = "SELECT users.UserID, users.FirstName, users.LastName, users.Email, collaborations.FileID, collaborations.CollabID
                FROM users
                INNER JOIN collaborations ON users.UserID = collaborations.UserID
                WHERE collaborations.FileID = $data";
    
        return $this->executeQuery($sql);
    }
    
    public function get_user_backup($id){
        $condition = "UserID=$id";
        return $this->get_records("backup", $condition);
    }
    

    public function get_backup($id=null){
        $condition = null;
        if($id != null){
            $condition = "BackupID=$id";
        }
        return $this->get_records("backup", $condition);
    }

    public function get_archive($id){
        $condition = "isArchived = 1 AND UserID=$id";
        return $this->get_records("files", $condition);
    }
 
    
    public function get_filetypeimage($fileTypeIdentifier) {
        // Define an array mapping file extensions to corresponding image URLs
        $fileTypeClasses = array(
            'pdf' => 'bx bxs-file-pdf me-2 font-24 text-danger',
            'docx' => 'bx bxs-file me-2 font-24 text-primary',
            'txt' => 'mdi mdi-text-box font-size-16 align-middle text-muted me-2',
            'jpg' => 'mdi mdi-image font-size-16 align-middle text-success me-2',
            'png' => 'mdi mdi-image font-size-16 align-middle text-success me-2',
            'jpeg' => 'mdi mdi-image font-size-16 align-middle text-success me-2',
            'gif' => 'mdi mdi-image font-size-16 align-middle text-success me-2',
            'xlsx' => 'bx bxs-file-doc me-2 font-24 text-success'
            // Add more mappings for other file types if needed
        );
    
        // Check if the file type identifier exists in the array
        if (array_key_exists($fileTypeIdentifier, $fileTypeClasses)) {
            return $fileTypeClasses[$fileTypeIdentifier];
        } else {
            // If the file type is not found, return a default CSS class
            return 'default-icon-class';
        }
    }
    
}
