<?php   
    /**
 * API Endpoint Router
 *
 * This PHP script serves as a simple API endpoint router, handling GET and POST requests for specific resources.
 *
 *
 * Usage:
 * 1. Include this script in your project.
 * 2. Define resource-specific logic in the 'get.php' and 'post.php' modules.
 * 3. Send requests to the appropriate endpoints defined in the 'switch' cases below.
 *
 * Example Usage:
 * - API_URL: http://localhost/demoproject/api
 * - GET request for employees: API_URL/employees
 * - GET request for jobs: API_URL/jobs
 * - POST request for adding employees: API_URL/addemployee (with JSON data in the request body)
 * - POST request for adding jobs: API_URL/addjob (with JSON data in the request body)
 *
 */


    // Include required modules
    require_once "./modules/get.php";
    require_once "./modules/post.php";
    require_once "./config/database.php";

    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
    }
    
    
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            header("Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS");
    
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    
        exit(0);
    }

    $con = new Connection();
    $pdo = $con->connect();

    // Initialize Get and Post objects
    $get = new Get($pdo);
    $post = new Post($pdo);

    // Check if 'request' parameter is set in the request
    if(isset($_REQUEST['request'])){
         // Split the request into an array based on '/'
        $request = explode('/', $_REQUEST['request']);
    }
    else{
         // If 'request' parameter is not set, return a 404 response
        echo "Not Found";
        http_response_code(404);
    }

    // Handle requests based on HTTP method
    switch($_SERVER['REQUEST_METHOD']){
        // Handle GET requests
        case 'GET':
            switch($request[0]){
                case 'getusers':
                    // Return JSON-encoded data for getting employees
                    if(count($request)>1){
                        echo json_encode($get->get_users($request[1]));
                    }
                    else{
                        echo json_encode($get->get_users());
                    }
                    break;
                case 'getFiles':
                    // Return JSON-encoded data for getting jobs
                    echo json_encode($get->getFiles());
                    break;
                case 'getFolders':
                    // Return JSON-encoded data for getting jobs
                    echo json_encode($get->getFolders());
                    break;
                case 'getcollaborations':
                    // Return JSON-encoded data for getting jobs
                    echo json_encode($get->get_collaborations());
                    break;

                case 'getbackups':
                    // Return JSON-encoded data for getting jobs
                    echo json_encode($get->getBackUp());
                    break;
                
                case 'getfiles':
                    // Return JSON-encoded data for getting files
                        echo json_encode($get->get_files($request[1]));
                    break;
                case 'getfilebyid':
                    // Return JSON-encoded data for getting files
                        echo json_encode($get->get_file($request[1]));
                    break;
                case 'getfilesbyfolder':
                    // Return JSON-encoded data for getting files
                        echo json_encode($get->get_files_by_folder($request[1]));
                    break;

                case 'getarchive':
                    // Return JSON-encoded data for getting jobs
                    echo json_encode($get->get_archive($request[1]));
                    break;
                case 'getbackup':
                    // Return JSON-encoded data for getting jobs
                    echo json_encode($get->get_user_backup($request[1]));
                    break;
                
                case 'getfolders':
                    // Return JSON-encoded data for getting jobs
                        echo json_encode($get->get_folders($request[1]));
                    break;
                case 'getfolderbyid':
                    // Return JSON-encoded data for getting jobs
                        echo json_encode($get->get_folder($request[1]));
                    break;

                case 'getarchivedfolders':
                    // Return JSON-encoded data for getting jobs
                    echo json_encode($get->get_archived_folders($request[1]));
                    break;
                
                case 'getCollaborationFile':
                    // Return JSON-encoded data for getting jobs
                    echo json_encode($get->getCollaborationFile($request[1]));
                    break;
                case 'getCollaboratorByFile':
                    // Return JSON-encoded data for getting jobs
                    echo json_encode($get->getCollaboratorByFile($request[1]));
                    break;
                
                case 'getCollaborations':
                    // Return JSON-encoded data for getting jobs
                    echo json_encode($get->getCollaborations());
                    break;
                default:
                    // Return a 403 response for unsupported requests
                    echo "This is forbidden";
                    http_response_code(403);
                    break;
            }
            break;
            
        // Handle POST requests    
        case 'POST':
            // Retrieves JSON-decoded data from php://input using file_get_contents
            $data = json_decode(file_get_contents("php://input"));
            switch($request[0]){
                case 'login':            
                    // Attempt login
                    echo json_encode($post->login_user($data));
                    break;
                case 'adduser':
                    // Return JSON-encoded data for adding employees
                    echo json_encode($post->add_user($data));
                    break;
                case 'uploadfile':
                    // Upload file
                    echo json_encode($post->uploadFile($data));
                    break;
                case 'restorefile':
                    // Restore file
                    echo json_encode($post->restoreFile($data));
                    break;
                case 'deletefiles':
                    // Delete file
                    echo json_encode($post->delete_files($data));
                    break;
                case 'renamefile':
                    echo json_encode($post->edit_files($data));
                    break;
                
                case 'deletepermanently':
                    // Delete file
                    echo json_encode($post->deletePermanently($data));
                    break;
                case 'updateuser':
                    // 
                    echo json_encode($post->edit_user($data));
                    break;
                case 'uploadProfile':
                    // 
                    echo json_encode($post->uploadProfile($data));
                    break;
                
                case 'backupfile':
                    // 
                    echo json_encode($post->backupFile($data));
                    break;
                case 'recoverfile':
                    // 
                    echo json_encode($post->recoverBackup($data));
                    break;
                case 'deleterecovery':
                    // 
                    echo json_encode($post->deleteRecoveryFile($data));
                    break;
                case 'createfolder':
                    // 
                    echo json_encode($post->createFolder($data));
                    break;
                
                case 'updateFolderName':
                    // 
                    echo json_encode($post->updateFolderName($data));
                    break;
                case 'archivefolder':
                    // 
                    echo json_encode($post->archiveFolderAndFiles($data));
                    break;
                case 'restorefolder':
                    // 
                    echo json_encode($post->restoreFolder($data));
                    break;
                case 'deletefolder':
                    // 
                    echo json_encode($post->deleteFolderandFiles($data));
                    break;
                case 'addcollaborator':
                    // 
                    echo json_encode($post->addCollaborator($data));
                    break;
                
                case 'selfremoveaccess':
                    // 
                    echo json_encode($post->selfRemoveAccess($data));
                    break;
                case 'movefile':
                    // 
                    echo json_encode($post->moveFile($data));
                    break;

                default:
                    // Return a 403 response for unsupported requests
                    echo "This is forbidden";
                    http_response_code(403);
                    break;
            }
            break;
        default:
            // Return a 404 response for unsupported HTTP methods
            echo "Method not available";
            http_response_code(404);
        break;
    }

?>