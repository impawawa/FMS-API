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

    public function get_files($id){
        $condition = "isArchived=0 AND UserID=$id";                
        return $this->get_records("files", $condition);
    }

    public function get_file($id){
        $condition = "isArchived=0 AND FileID=$id";                 
        return $this->get_records("files", $condition);
    }

    public function get_folders($id=null){
        $condition = null;
        if($id != null){
            $condition = "FolderID=$id";
        }
        return $this->get_records("folders", $condition);
    }

    public function getFileByID($id){
        $condition = "FileID=$id";
        return $this->get_records("files", $condition);
    }


    public function get_backup($id=null){
        $condition = null;
        if($id != null){
            $condition = "BackupID=$id";
        }
        return $this->get_records("backup", $condition);
    }

    public function get_archive(){
        $condition = "isArchived=1";
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
