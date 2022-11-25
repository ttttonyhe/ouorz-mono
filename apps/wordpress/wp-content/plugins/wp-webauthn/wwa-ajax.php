<?php
require_once('vendor/autoload.php');
use Webauthn\Server;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialSourceRepository as PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\AuthenticatorSelectionCriteria;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

/**
 * Store all publickeys and pubilckey metas
 */
class PublicKeyCredentialSourceRepository implements PublicKeyCredentialSourceRepositoryInterface {
    // Get one credential by credential ID
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource {
        $data = $this->read();
        if(isset($data[base64_encode($publicKeyCredentialId)])){
            return PublicKeyCredentialSource::createFromArray($data[base64_encode($publicKeyCredentialId)]);
        }
        return null;
    }

    // Get one credential's meta by credential ID
    public function findOneMetaByCredentialId(string $publicKeyCredentialId): ?array {
        $meta = json_decode(wwa_get_option("user_credentials_meta"), true);
        if(isset($meta[base64_encode($publicKeyCredentialId)])){
            return $meta[base64_encode($publicKeyCredentialId)];
        }
        return null;
    }

    // Get all credentials of one user
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array {
        $sources = [];
        foreach($this->read() as $data){
            $source = PublicKeyCredentialSource::createFromArray($data);
            if($source->getUserHandle() === $publicKeyCredentialUserEntity->getId()){
                $sources[] = $source;
            }
        }
        return $sources;
    }

    public function findCredentialsForUserEntityByType(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity, string $credentialType): array {
        $credentialsForUserEntity = $this->findAllForUserEntity($publicKeyCredentialUserEntity);
        $credentialsByType = [];
        foreach($credentialsForUserEntity as $credential){
            if($this->findOneMetaByCredentialId($credential->getPublicKeyCredentialId())["authenticator_type"] === $credentialType){
                $credentialsByType[] = $credential;
            }
        }
        return $credentialsByType;
    }

    // Save credential into database
    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource, bool $usernameless = false): void {
        $data = $this->read();
        $data_key = base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId());
        $data[$data_key] = $publicKeyCredentialSource;
        $this->write($data, $data_key, $usernameless);
    }

    // Update credential's last used
    public function updateCredentialLastUsed(string $publicKeyCredentialId): void {
        $credential = $this->findOneMetaByCredentialId($publicKeyCredentialId);
        if($credential !== null){
            $credential["last_used"] = date('Y-m-d H:i:s', current_time('timestamp'));
            $meta = json_decode(wwa_get_option("user_credentials_meta"), true);
            $meta[base64_encode($publicKeyCredentialId)] = $credential;
            wwa_update_option("user_credentials_meta", json_encode($meta));
        }
    }

    // List all authenticators
    public function getShowList(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array {
        $data = json_decode(wwa_get_option("user_credentials_meta"), true);
        $arr = array();
        $user_id = $publicKeyCredentialUserEntity->getId();
        foreach($data as $key => $value){
            if($user_id === $value["user"]){
                array_push($arr, array(
                    "key" => rtrim(strtr(base64_encode($key), '+/', '-_'), '='),
                    "name" => base64_decode($value["human_name"]),
                    "type" => $value["authenticator_type"],
                    "added" => $value["added"],
                    "usernameless" => isset($value["usernameless"]) ? $value["usernameless"] : false,
                    "last_used" => isset($value["last_used"]) ? $value["last_used"] : "-"
                ));
            }
        }
        return array_map(function($item){return array("key" => $item["key"], "name" => $item["name"], "type" => $item["type"], "added" => $item["added"], "usernameless" => $item["usernameless"], "last_used" => $item["last_used"]);}, $arr);
    }

    // Modify an authenticator
    public function modifyAuthenticator(string $id, string $name, PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity, string $action, string $res_id): string {
        $keys = $this->findAllForUserEntity($publicKeyCredentialUserEntity);
        $user_id = $publicKeyCredentialUserEntity->getId();

        // Check if the user has the authenticator
        foreach($keys as $item){
            if($item->getUserHandle() === $user_id){
                if(base64_encode($item->getPublicKeyCredentialId()) === base64_decode(str_pad(strtr($id, '-_', '+/'), strlen($id) % 4, '=', STR_PAD_RIGHT))){
                    if($action === "rename"){
                        $this->renameCredential(base64_encode($item->getPublicKeyCredentialId()), $name, $res_id);
                    }else if($action === "remove"){
                        $this->removeCredential(base64_encode($item->getPublicKeyCredentialId()), $res_id);
                    }
                    wwa_add_log($res_id, "ajax_modify_authenticator: Done");
                    return "true";
                }
            }
        }
        wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)Authenticator not found, exit");
        return "Not Found.";
    }

    // Rename a credential from database by credential ID
    private function renameCredential(string $id, string $name, string $res_id): void {
        $meta = json_decode(wwa_get_option("user_credentials_meta"), true);
        wwa_add_log($res_id, "ajax_modify_authenticator: Rename \"".base64_decode($meta[$id]["human_name"])."\" -> \"".$name."\"");
        $meta[$id]["human_name"] = base64_encode($name);
        wwa_update_option("user_credentials_meta", json_encode($meta));
    }

    // Remove a credential from database by credential ID
    private function removeCredential(string $id, string $res_id): void {
        $data = $this->read();
        unset($data[$id]);
        $this->write($data, '');
        $meta = json_decode(wwa_get_option("user_credentials_meta"), true);
        wwa_add_log($res_id, "ajax_modify_authenticator: Remove \"".base64_decode($meta[$id]["human_name"])."\"");
        unset($meta[$id]);
        wwa_update_option("user_credentials_meta", json_encode($meta));
    }

    // Read credential database
    private function read(): array {
        if(wwa_get_option("user_credentials") !== NULL){
            try{
                return json_decode(wwa_get_option("user_credentials"), true);
            }catch(\Throwable $exception) {
                return [];
            }
        }
        return [];
    }

    // Save credentials data
    private function write(array $data, string $key, bool $usernameless = false): void {
        if(isset($_POST["type"]) && ($_POST["type"] === "platform" || $_POST["type"] == "cross-platform" || $_POST["type"] === "none") && $key !== ''){
            // Save credentials's meta separately
            $source = $data[$key]->getUserHandle();
            $meta = json_decode(wwa_get_option("user_credentials_meta"), true);
            $meta[$key] = array("human_name" => base64_encode(sanitize_text_field($_POST["name"])), "added" => date('Y-m-d H:i:s', current_time('timestamp')), "authenticator_type" => $_POST["type"], "user" => $source, "usernameless" => $usernameless, "last_used" => "-");
            wwa_update_option("user_credentials_meta", json_encode($meta));
        }
        wwa_update_option("user_credentials", json_encode($data));
    }
}

// Bind an authenticator
function wwa_ajax_create(){
    try{
        $res_id = wwa_generate_random_string(5);
        $client_id = strval(time()).wwa_generate_random_string(24);

        wwa_init_new_options();

        wwa_add_log($res_id, "ajax_create: Start");

        if(!current_user_can("read")){
            wwa_add_log($res_id, "ajax_create: (ERROR)Permission denied, exit");
            wwa_wp_die("Something went wrong.", $client_id);
        }

        if(wwa_get_option('website_name') === "" || wwa_get_option('website_domain') ===""){
            wwa_add_log($res_id, "ajax_create: (ERROR)Plugin not configured, exit");
            wwa_wp_die("Not configured.", $client_id);
        }

        // Check queries
        if(!isset($_GET["name"]) || !isset($_GET["type"]) || !isset($_GET["usernameless"])){
            wwa_add_log($res_id, "ajax_create: (ERROR)Missing parameters, exit");
            wwa_wp_die("Bad Request.", $client_id);
        }else{
            // Sanitize the input
            $wwa_get = array();
            $wwa_get["name"] = sanitize_text_field($_GET["name"]);
            $wwa_get["type"] = sanitize_text_field($_GET["type"]);
            $wwa_get["usernameless"] = sanitize_text_field($_GET["usernameless"]);
            wwa_add_log($res_id, "ajax_create: name => \"".$wwa_get["name"]."\", type => \"".$wwa_get["type"]."\", usernameless => \"".$wwa_get["usernameless"]."\"");
        }

        $user_info = wp_get_current_user();

        if(isset($_GET["user_id"])){
            $user_id = intval(sanitize_text_field($_GET["user_id"]));
            if($user_id <= 0){
                wwa_add_log($res_id, "ajax_create: (ERROR)Wrong parameters, exit");
                wwa_wp_die("Bad Request.");
            }

            if($user_info->ID !== $user_id){
                if(!current_user_can("edit_user", $user_id)){
                    wwa_add_log($res_id, "ajax_create: (ERROR)No permission, exit");
                    wwa_wp_die("Something went wrong.");
                }
                $user_info = get_user_by('id', $user_id);

                if($user_info === false){
                    wwa_add_log($res_id, "ajax_create: (ERROR)Wrong user ID, exit");
                    wwa_wp_die("Something went wrong.");
                }
            }
        }

        // Empty authenticator name
        if($wwa_get["name"] === ""){
            wwa_add_log($res_id, "ajax_create: (ERROR)Empty name, exit");
            wwa_wp_die("Bad Request.", $client_id);
        }

        // Usernameless authentication not allowed
        if($wwa_get["usernameless"] === "true" && wwa_get_option("usernameless_login") !== "true"){
            wwa_add_log($res_id, "ajax_create: (ERROR)Usernameless authentication not allowed, exit");
            wwa_wp_die("Bad Request.", $client_id);
        }

        // Check authenticator type
        $allow_authenticator_type = wwa_get_option("allow_authenticator_type");
        if($allow_authenticator_type !== false && $allow_authenticator_type !== "none"){
            if($allow_authenticator_type != $wwa_get["type"]){
                wwa_add_log($res_id, "ajax_create: (ERROR)Credential type error, type => \"".$wwa_get["type"]."\", allow_authenticator_type => \"".$allow_authenticator_type."\", exit");
                wwa_wp_die("Bad Request.", $client_id);
            }
        }

        $rpEntity = new PublicKeyCredentialRpEntity(
            wwa_get_option("website_name"),
            wwa_get_option("website_domain")
        );
        $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();

        $server = new Server(
            $rpEntity,
            $publicKeyCredentialSourceRepository,
            null
        );

        wwa_add_log($res_id, "ajax_create: user => \"".$user_info->user_login."\"");

        // Get user ID or create one
        $user_key = "";
        if(!isset(wwa_get_option("user_id")[$user_info->user_login])){
            wwa_add_log($res_id, "ajax_create: User not initialized, initialize");
            $user_array = wwa_get_option("user_id");
            $user_key = hash("sha256", $user_info->user_login."-".$user_info->display_name."-".wwa_generate_random_string(10));
            $user_array[$user_info->user_login] = $user_key;
            wwa_update_option("user_id", $user_array);
        }else{
            $user_key = wwa_get_option("user_id")[$user_info->user_login];
        }

        $user = array(
            "login" => $user_info->user_login,
            "id" => $user_key,
            "display" => $user_info->display_name,
            "icon" => get_avatar_url($user_info->user_email, array("scheme" => "https"))
        );

        $userEntity = new PublicKeyCredentialUserEntity(
            $user["login"],
            $user["id"],
            $user["display"],
            $user["icon"]
        );

        $credentialSourceRepository = new PublicKeyCredentialSourceRepository();

        $credentialSources = $credentialSourceRepository->findAllForUserEntity($userEntity);

        // Convert the Credential Sources into Public Key Credential Descriptors for excluding
        $excludeCredentials = array_map(function (PublicKeyCredentialSource $credential) {
            return $credential->getPublicKeyCredentialDescriptor();
        }, $credentialSources);

        wwa_add_log($res_id, "ajax_create: excludeCredentials => ".json_encode($excludeCredentials));

        // Set authenticator type
        if($wwa_get["type"] === "platform"){
            $authenticator_type = AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM;
        }else if($wwa_get["type"] === "cross-platform"){
            $authenticator_type = AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_CROSS_PLATFORM;
        }else{
            $authenticator_type = AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_NO_PREFERENCE;
        }

        // Set user verification
        if(wwa_get_option("user_verification") === "true"){
            wwa_add_log($res_id, "ajax_create: user_verification => \"true\"");
            $user_verification = AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED;
        }else{
            wwa_add_log($res_id, "ajax_create: user_verification => \"false\"");
            $user_verification = AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_DISCOURAGED;
        }

        $resident_key = false;
        // Set usernameless authentication
        if($wwa_get["usernameless"] === "true"){
            wwa_add_log($res_id, "ajax_create: Usernameless set, user_verification => \"true\"");
            $user_verification = AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED;
            $resident_key = true;
        }

        // Create authenticator selection
        $authenticatorSelectionCriteria = new AuthenticatorSelectionCriteria(
            $authenticator_type,
            $resident_key,
            $user_verification
        );

        // Create a creation challenge
        $publicKeyCredentialCreationOptions = $server->generatePublicKeyCredentialCreationOptions(
            $userEntity,
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
            $excludeCredentials,
            $authenticatorSelectionCriteria
        );

        // Save for future use
        wwa_set_temp_val("pkcco", base64_encode(serialize($publicKeyCredentialCreationOptions)), $client_id);
        wwa_set_temp_val("bind_config", array("name" => $wwa_get["name"], "type" => $wwa_get["type"], "usernameless" => $resident_key), $client_id);

        header("Content-Type: application/json");
        $publicKeyCredentialCreationOptions = json_decode(json_encode($publicKeyCredentialCreationOptions), true);
        $publicKeyCredentialCreationOptions["clientID"] = $client_id;
        echo json_encode($publicKeyCredentialCreationOptions);
        wwa_add_log($res_id, "ajax_create: Challenge sent");
        exit;
    }catch(\Exception $exception){
        wwa_add_log($res_id, "ajax_create: (ERROR)".$exception->getMessage());
        wwa_add_log($res_id, wwa_generate_call_trace($exception));
        wwa_add_log($res_id, "ajax_create: (ERROR)Unknown error, exit");
        wwa_wp_die("Something went wrong.", $client_id);
    }catch(\Error $error){
        wwa_add_log($res_id, "ajax_create: (ERROR)".$error->getMessage());
        wwa_add_log($res_id, wwa_generate_call_trace($error));
        wwa_add_log($res_id, "ajax_create: (ERROR)Unknown error, exit");
        wwa_wp_die("Something went wrong.", $client_id);
    }
}
add_action("wp_ajax_wwa_create" , "wwa_ajax_create");

// Verify the attestation
function wwa_ajax_create_response(){
    $client_id = false;
    try{
        $res_id = wwa_generate_random_string(5);

        wwa_init_new_options();

        wwa_add_log($res_id, "ajax_create_response: Client response received");

        if(!isset($_POST["clientid"])){
            wwa_add_log($res_id, "ajax_create_response: (ERROR)Missing parameters, exit");
            wp_die("Bad Request.");
        }else{
            if(strlen($_POST["clientid"]) < 34 || strlen($_POST["clientid"]) > 35){
                wwa_add_log($res_id, "ajax_create_response: (ERROR)Wrong client ID, exit");
                wwa_wp_die("Bad Request.", $client_id);
            }
            // Sanitize the input
            $client_id = sanitize_text_field($_POST["clientid"]);
        }

        if(!current_user_can("read")){
            wwa_add_log($res_id, "ajax_create_response: (ERROR)Permission denied, exit");
            wwa_wp_die("Something went wrong.", $client_id);
        }

        // Check POST
        if(!isset($_POST["data"]) || !isset($_POST["name"]) || !isset($_POST["type"]) || !isset($_POST["usernameless"])){
            wwa_add_log($res_id, "ajax_create_response: (ERROR)Missing parameters, exit");
            wwa_wp_die("Bad Request.", $client_id);
        }else{
            // Sanitize the input
            $wwa_post = array();
            $wwa_post["name"] = sanitize_text_field($_POST["name"]);
            $wwa_post["type"] = sanitize_text_field($_POST["type"]);
            $wwa_post["usernameless"] = sanitize_text_field($_POST["usernameless"]);
            wwa_add_log($res_id, "ajax_create_response: name => \"".$wwa_post["name"]."\", type => \"".$wwa_post["type"]."\", usernameless => \"".$wwa_post["usernameless"]."\"");
            wwa_add_log($res_id, "ajax_create_response: data => ".base64_decode($_POST["data"]));
        }

        if(isset($_GET["user_id"])){
            $user_id = intval(sanitize_text_field($_POST["user_id"]));
            if($user_id <= 0){
                wwa_add_log($res_id, "ajax_create_response: (ERROR)Wrong parameters, exit");
                wwa_wp_die("Bad Request.");
            }

            if(wp_get_current_user()->ID !== $user_id){
                if(!current_user_can("edit_user", $user_id)){
                    wwa_add_log($res_id, "ajax_create_response: (ERROR)No permission, exit");
                    wwa_wp_die("Something went wrong.");
                }
            }
        }

        $temp_val = array(
            "pkcco" => wwa_get_temp_val("pkcco", $client_id),
            "bind_config" => wwa_get_temp_val("bind_config", $client_id)
        );

        // May not get the challenge yet
        if($temp_val["pkcco"] === false || $temp_val["bind_config"] === false){
            wwa_add_log($res_id, "ajax_create_response: (ERROR)Challenge not found in transient, exit");
            wwa_wp_die("Bad request.", $client_id);
        }

        // Check parameters
        if($temp_val["bind_config"]["type"] !== "platform" && $temp_val["bind_config"]["type"] !== "cross-platform" && $temp_val["bind_config"]["type"] !== "none"){
            wwa_add_log($res_id, "ajax_create_response: (ERROR)Wrong type, exit");
            wwa_wp_die("Bad request.", $client_id);
        }

        if($temp_val["bind_config"]["type"] !== $wwa_post["type"] || $temp_val["bind_config"]["name"] !== $wwa_post["name"]){
            wwa_add_log($res_id, "ajax_create_response: (ERROR)Wrong parameters, exit");
            wwa_wp_die("Bad Request.", $client_id);
        }

        // Check global unique credential ID
        $credential_id = base64_decode(json_decode(base64_decode($_POST["data"]), true)["rawId"]);
        $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();
        if($publicKeyCredentialSourceRepository->findOneMetaByCredentialId($credential_id) !== null){
            wwa_add_log($res_id, "ajax_create_response: (ERROR)Credential ID not unique, ID => \"".base64_encode($credential_id)."\" , exit");
            wwa_wp_die("Something went wrong.", $client_id);
        }else{
            wwa_add_log($res_id, "ajax_create_response: Credential ID unique check passed");
        }

        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        );

        $serverRequest = $creator->fromGlobals();

        $rpEntity = new PublicKeyCredentialRpEntity(
            wwa_get_option("website_name"),
            wwa_get_option("website_domain")
        );

        $server = new Server(
            $rpEntity,
            $publicKeyCredentialSourceRepository,
            null
        );

        // Allow to bypass scheme verification when under localhost
        $current_domain = wwa_get_option('website_domain');
        if($current_domain === "localhost" || $current_domain === "127.0.0.1"){
            $server->setSecuredRelyingPartyId([$current_domain]);
            wwa_add_log($res_id, "ajax_create_response: Localhost, bypass HTTPS check");
        }

        // Verify
        try {
            $publicKeyCredentialSource = $server->loadAndCheckAttestationResponse(
                base64_decode($_POST["data"]),
                unserialize(base64_decode($temp_val["pkcco"])),
                $serverRequest
            );

            wwa_add_log($res_id, "ajax_create_response: Challenge verified");

            $publicKeyCredentialSourceRepository->saveCredentialSource($publicKeyCredentialSource, $temp_val["bind_config"]["usernameless"]);

            if($temp_val["bind_config"]["usernameless"]){
                wwa_add_log($res_id, "ajax_create_response: Authenticator added with usernameless authentication feature");
            }else{
                wwa_add_log($res_id, "ajax_create_response: Authenticator added");
            }

            // Success
            echo "true";
        }catch(\Throwable $exception){
            // Failed to verify
            wwa_add_log($res_id, "ajax_create_response: (ERROR)".$exception->getMessage());
            wwa_add_log($res_id, wwa_generate_call_trace($exception));
            wwa_add_log($res_id, "ajax_create_response: (ERROR)Challenge not verified, exit");
            wwa_wp_die("Something went wrong.", $client_id);
        }

        // Destroy transients
        wwa_destroy_temp_val($client_id);
        exit;
    }catch(\Exception $exception){
        wwa_add_log($res_id, "ajax_create_response: (ERROR)".$exception->getMessage());
        wwa_add_log($res_id, wwa_generate_call_trace($exception));
        wwa_add_log($res_id, "ajax_create_response: (ERROR)Unknown error, exit");
        wwa_wp_die("Something went wrong.", $client_id);
    }catch(\Error $error){
        wwa_add_log($res_id, "ajax_create_response: (ERROR)".$error->getMessage());
        wwa_add_log($res_id, wwa_generate_call_trace($error));
        wwa_add_log($res_id, "ajax_create_response: (ERROR)Unknown error, exit");
        wwa_wp_die("Something went wrong.", $client_id);
    }
}
add_action("wp_ajax_wwa_create_response" , "wwa_ajax_create_response");

// Auth challenge
function wwa_ajax_auth_start(){
    try{
        $res_id = wwa_generate_random_string(5);
        $client_id = strval(time()).wwa_generate_random_string(24);

        wwa_init_new_options();

        wwa_add_log($res_id, "ajax_auth: Start");

        // Check queries
        if(!isset($_GET["type"])){
            wwa_add_log($res_id, "ajax_auth: (ERROR)Missing parameters, exit");
            wwa_wp_die("Bad Request.", $client_id);
        }else{
            // Sanitize the input
            $wwa_get = array();
            $wwa_get["type"] = sanitize_text_field($_GET["type"]);
            if(isset($_GET["user"])){
                $wwa_get["user"] = sanitize_text_field($_GET["user"]);
            }
            if(isset($_GET["usernameless"])){
                $wwa_get["usernameless"] = sanitize_text_field($_GET["usernameless"]);
                // Usernameless authentication not allowed
                if($wwa_get["usernameless"] === "true" && wwa_get_option("usernameless_login") !== "true"){
                    wwa_add_log($res_id, "ajax_auth: (ERROR)Usernameless authentication not allowed, exit");
                    wwa_wp_die("Bad Request.", $client_id);
                }
            }
        }

        if($wwa_get["type"] === "test" && !current_user_can('read')){
            // Test but not logged in
            wwa_add_log($res_id, "ajax_auth: (ERROR)Permission denied, exit");
            wwa_wp_die("Bad request.", $client_id);
        }

        $user_key = "";
        $usernameless_flag = false;
        $user_icon = null;
        if($wwa_get["type"] === "test"){
            if(isset($wwa_get["usernameless"])){
                if($wwa_get["usernameless"] !== "true"){
                    // Logged in and testing, if the user haven't bound any authenticator yet, exit
                    $user_info = wp_get_current_user();

                    if(isset($_GET["user_id"])){
                        $user_id = intval(sanitize_text_field($_GET["user_id"]));
                        if($user_id <= 0){
                            wwa_add_log($res_id, "ajax_auth: (ERROR)Wrong parameters, exit");
                            wwa_wp_die("Bad Request.");
                        }

                        if($user_info->ID !== $user_id){
                            if(!current_user_can("edit_user", $user_id)){
                                wwa_add_log($res_id, "ajax_auth: (ERROR)No permission, exit");
                                wwa_wp_die("Something went wrong.");
                            }
                            $user_info = get_user_by('id', $user_id);

                            if($user_info === false){
                                wwa_add_log($res_id, "ajax_auth: (ERROR)Wrong user ID, exit");
                                wwa_wp_die("Something went wrong.");
                            }
                        }
                    }

                    wwa_add_log($res_id, "ajax_auth: type => \"test\", user => \"".$user_info->user_login."\", usernameless => \"false\"");

                    if(!isset(wwa_get_option("user_id")[$user_info->user_login])){
                        wwa_add_log($res_id, "ajax_auth: (ERROR)User not initialized, exit");
                        wwa_wp_die("User not inited.", $client_id);
                    }else{
                        $user_key = wwa_get_option("user_id")[$user_info->user_login];
                        $user_icon = get_avatar_url($user_info->user_email, array("scheme" => "https"));
                    }
                }else{
                    if(wwa_get_option("usernameless_login") === "true"){
                        wwa_add_log($res_id, "ajax_auth: type => \"test\", usernameless => \"true\"");
                        $usernameless_flag = true;
                    }else{
                        wwa_add_log($res_id, "ajax_auth: (ERROR)Wrong parameters, exit");
                        wwa_wp_die("Bad Request.", $client_id);
                    }
                }
            }else{
                wwa_add_log($res_id, "ajax_auth: (ERROR)Missing parameters, exit");
                wwa_wp_die("Bad Request.", $client_id);
            }
        }else{
            // Not testing, create a fake user ID if the user does not exist or haven't bound any authenticator yet
            if(isset($wwa_get["user"]) && $wwa_get["user"] !== ""){
                if(get_user_by('login', $wwa_get["user"])){
                    $user_info = get_user_by('login', $wwa_get["user"]);
                    $user_icon = get_avatar_url($user_info->user_email, array("scheme" => "https"));
                    wwa_add_log($res_id, "ajax_auth: type => \"auth\", user => \"".$user_info->user_login."\"");
                    if(!isset(wwa_get_option("user_id")[$user_info->user_login])){
                        wwa_add_log($res_id, "ajax_auth: User not initialized, initialize");
                        $user_key = hash("sha256", $wwa_get["user"]."-".$wwa_get["user"]."-".wwa_generate_random_string(10));
                    }else{
                        $user_key = wwa_get_option("user_id")[$user_info->user_login];
                    }
                }else{
                    $user_info = new stdClass();
                    $user_info->user_login = $wwa_get["user"];
                    $user_info->display_name = $wwa_get["user"];
                    $user_key = hash("sha256", $wwa_get["user"]."-".$wwa_get["user"]."-".wwa_generate_random_string(10));
                    wwa_add_log($res_id, "ajax_auth: type => \"auth\", user => \"".$wwa_get["user"]."\"");
                    wwa_add_log($res_id, "ajax_auth: User not exists, create a fake id");
                }
            }else{
                if(wwa_get_option("usernameless_login") === "true"){
                    $usernameless_flag = true;
                    wwa_add_log($res_id, "ajax_auth: Empty username, try usernameless authentication");
                }else{
                    wwa_add_log($res_id, "ajax_auth: (ERROR)Missing parameters, exit");
                    wwa_wp_die("Bad Request.", $client_id);
                }
            }
        }

        if(!$usernameless_flag){
            $userEntity = new PublicKeyCredentialUserEntity(
                $user_info->user_login,
                $user_key,
                $user_info->display_name,
                $user_icon
            );
        }

        $credentialSourceRepository = new PublicKeyCredentialSourceRepository();
        $rpEntity = new PublicKeyCredentialRpEntity(
            wwa_get_option('website_name'),
            wwa_get_option('website_domain')
        );

        $server = new Server(
            $rpEntity,
            $credentialSourceRepository,
            null
        );

        if($usernameless_flag){
            // Usernameless authentication, return empty allowed credentials list
            wwa_add_log($res_id, "ajax_auth: Usernameless authentication, allowedCredentials => []");
            $allowedCredentials = array();
        }else{
            // Get the list of authenticators associated to the user
            // $credentialSources = $credentialSourceRepository->findAllForUserEntity($userEntity);
            $allow_authenticator_type = wwa_get_option('allow_authenticator_type');
            if($allow_authenticator_type === false || $allow_authenticator_type === 'none'){
                $credentialSources = $credentialSourceRepository->findAllForUserEntity($userEntity);
            }else if($allow_authenticator_type !== false && $allow_authenticator_type !== 'none'){
                wwa_add_log($res_id, "ajax_auth: allow_authenticator_type => \"".$allow_authenticator_type."\", filter authenticators");
                $credentialSources = $credentialSourceRepository->findCredentialsForUserEntityByType($userEntity, $allow_authenticator_type);
            }

            // Logged in and testing, if the user haven't bind a authenticator yet, exit
            if(count($credentialSources) === 0 && $wwa_get["type"] === "test" && current_user_can('read')){
                wwa_add_log($res_id, "ajax_auth: (ERROR)No authenticator, exit");
                wwa_wp_die("User not inited.", $client_id);
            }

            // Convert the Credential Sources into Public Key Credential Descriptors for excluding
            $allowedCredentials = array_map(function(PublicKeyCredentialSource $credential){
                return $credential->getPublicKeyCredentialDescriptor();
            }, $credentialSources);

            wwa_add_log($res_id, "ajax_auth: allowedCredentials => ".json_encode($allowedCredentials));
        }

        // Set user verification
        if(wwa_get_option("user_verification") === "true"){
            wwa_add_log($res_id, "ajax_auth: user_verification => \"true\"");
            $user_verification = AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED;
        }else{
            wwa_add_log($res_id, "ajax_auth: user_verification => \"false\"");
            $user_verification = AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_DISCOURAGED;
        }

        if($usernameless_flag){
            wwa_add_log($res_id, "ajax_auth: Usernameless authentication, user_verification => \"true\"");
            $user_verification = AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED;
        }

        // Create a auth challenge
        $publicKeyCredentialRequestOptions = $server->generatePublicKeyCredentialRequestOptions(
            $user_verification,
            $allowedCredentials
        );

        // Save for future use
        wwa_set_temp_val("pkcco_auth", base64_encode(serialize($publicKeyCredentialRequestOptions)), $client_id);
        wwa_set_temp_val("auth_type", $wwa_get["type"], $client_id);
        if(!$usernameless_flag){
            wwa_set_temp_val("user_name_auth", $user_info->user_login, $client_id);
        }
        wwa_set_temp_val("usernameless_auth", serialize($usernameless_flag), $client_id);

        // Save the user entity if is not logged in and not usernameless
        if(!($wwa_get["type"] === "test" && current_user_can("read")) && !$usernameless_flag){
            wwa_set_temp_val("user_auth", serialize($userEntity), $client_id);
        }

        header("Content-Type: application/json");
        $publicKeyCredentialRequestOptions = json_decode(json_encode($publicKeyCredentialRequestOptions), true);
        $publicKeyCredentialRequestOptions["clientID"] = $client_id;
        echo json_encode($publicKeyCredentialRequestOptions);
        wwa_add_log($res_id, "ajax_auth: Challenge sent");
        exit;
    }catch(\Exception $exception){
        wwa_add_log($res_id, "ajax_auth: (ERROR)".$exception->getMessage());
        wwa_add_log($res_id, wwa_generate_call_trace($exception));
        wwa_add_log($res_id, "ajax_auth: (ERROR)Unknown error, exit");
        wwa_wp_die("Something went wrong.", $client_id);
    }catch(\Error $error){
        wwa_add_log($res_id, "ajax_auth: (ERROR)".$error->getMessage());
        wwa_add_log($res_id, wwa_generate_call_trace($error));
        wwa_add_log($res_id, "ajax_auth: (ERROR)Unknown error, exit");
        wwa_wp_die("Something went wrong.", $client_id);
    }
}
add_action("wp_ajax_wwa_auth_start" , "wwa_ajax_auth_start");
add_action("wp_ajax_nopriv_wwa_auth_start" , "wwa_ajax_auth_start");

function wwa_ajax_auth(){
    $client_id = false;
    try{
        $res_id = wwa_generate_random_string(5);

        wwa_init_new_options();

        wwa_add_log($res_id, "ajax_auth_response: Client response received");

        if(!isset($_POST["clientid"])){
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Missing parameters, exit");
            wp_die("Bad Request.");
        }else{
            if(strlen($_POST["clientid"]) < 34 || strlen($_POST["clientid"]) > 35){
                wwa_add_log($res_id, "ajax_auth_response: (ERROR)Wrong client ID, exit");
                wwa_wp_die("Bad Request.", $client_id);
            }
            // Sanitize the input
            $client_id = sanitize_text_field($_POST["clientid"]);
        }

        // Check POST
        if(!isset($_POST["type"]) || !isset($_POST["data"]) || !isset($_POST["remember"])){
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Missing parameters, exit");
            wwa_wp_die("Bad Request.", $client_id);
        }else{
            // Sanitize the input
            $wwa_post = array();
            $wwa_post["type"] = sanitize_text_field($_POST["type"]);
            $wwa_post["remember"] = sanitize_text_field($_POST["remember"]);
        }

        $temp_val = array(
            "pkcco_auth" => wwa_get_temp_val("pkcco_auth", $client_id),
            "auth_type" => wwa_get_temp_val("auth_type", $client_id),
            "usernameless_auth" => wwa_get_temp_val("usernameless_auth", $client_id),
            "user_auth" => wwa_get_temp_val("user_auth", $client_id),
            "user_name_auth" => wwa_get_temp_val("user_name_auth", $client_id)
        );

        if($temp_val["auth_type"] === false || $wwa_post["type"] !== $temp_val["auth_type"]){
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Wrong parameters, exit");
            wwa_wp_die("Bad Request.", $client_id);
        }

        // Check remember me
        if($wwa_post["remember"] !== "true" && $wwa_post["remember"] !== "false"){
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Wrong parameters, exit");
            wwa_wp_die("Bad Request.", $client_id);
        }else if(wwa_get_option('remember_me') !== 'true' && $wwa_post["remember"] === "true"){
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Wrong parameters, exit");
            wwa_wp_die("Bad Request.", $client_id);
        }

        // May not get the challenge yet
        if($temp_val["pkcco_auth"] === false || $temp_val["usernameless_auth"] === false || ($wwa_post["type"] !== "test" && $wwa_post["type"] !== "auth")){
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Challenge not found in transient, exit");
            wwa_wp_die("Bad request.", $client_id);
        }

        $temp_val["usernameless_auth"] = unserialize($temp_val["usernameless_auth"]);

        if($temp_val["usernameless_auth"] === false && $temp_val["user_name_auth"] === false){
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Username not found in transient, exit");
            wwa_wp_die("Bad request.", $client_id);
        }
        if($wwa_post["type"] === "test" && !current_user_can("read")){
            // Test but not logged in
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Permission denied, exit");
            wwa_wp_die("Bad request.", $client_id);
        }
        if(!($wwa_post["type"] === "test" && current_user_can("read")) && ($temp_val["usernameless_auth"] === false && $temp_val["user_auth"] === false)){
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Permission denied, exit");
            wwa_wp_die("Bad request.", $client_id);
        }

        $usernameless_flag = $temp_val["usernameless_auth"];

        $psr17Factory = new Psr17Factory();
        $creator = new ServerRequestCreator(
            $psr17Factory,
            $psr17Factory,
            $psr17Factory,
            $psr17Factory
        );

        $serverRequest = $creator->fromGlobals();
        $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();

        // If user entity is not saved, read from WordPress
        $user_key = "";
        if($wwa_post["type"] === "test" && current_user_can('read') && !$usernameless_flag){
            $user_info = wp_get_current_user();

            if(isset($_GET["user_id"])){
                $user_id = intval(sanitize_text_field($_POST["user_id"]));
                if($user_id <= 0){
                    wwa_add_log($res_id, "ajax_auth_response: (ERROR)Wrong parameters, exit");
                    wwa_wp_die("Bad Request.");
                }

                if($user_info->ID !== $user_id){
                    if(!current_user_can("edit_user", $user_id)){
                        wwa_add_log($res_id, "ajax_auth_response: (ERROR)No permission, exit");
                        wwa_wp_die("Something went wrong.");
                    }
                    $user_info = get_user_by('id', $user_id);

                    if($user_info === false){
                        wwa_add_log($res_id, "ajax_auth_response: (ERROR)Wrong user ID, exit");
                        wwa_wp_die("Something went wrong.");
                    }
                }
            }

            if(!isset(wwa_get_option("user_id")[$user_info->user_login])){
                wwa_add_log($res_id, "ajax_auth_response: (ERROR)User not initialized, exit");
                wwa_wp_die("User not inited.", $client_id);
            }else{
                $user_key = wwa_get_option("user_id")[$user_info->user_login];
                $user_icon = get_avatar_url($user_info->user_email, array("scheme" => "https"));
            }

            $userEntity = new PublicKeyCredentialUserEntity(
                $user_info->user_login,
                $user_key,
                $user_info->display_name,
                $user_icon
            );

            wwa_add_log($res_id, "ajax_auth_response: type => \"test\", user => \"".$user_info->user_login."\"");
        }else{
            if($usernameless_flag){
                $data_array = json_decode(base64_decode($_POST["data"]), true);
                if(!isset($data_array["response"]["userHandle"]) || !isset($data_array["rawId"])){
                    wwa_add_log($res_id, "ajax_auth_response: (ERROR)Client data not correct, exit");
                    wwa_wp_die("Bad request.", $client_id);
                }

                wwa_add_log($res_id, "ajax_auth_response: type => \"".$wwa_post["type"]."\"");
                wwa_add_log($res_id, "ajax_auth_response: Usernameless authentication, try to find user by credential_id => \"".$data_array["rawId"]."\", userHandle => \"".$data_array["response"]["userHandle"]."\"");

                $credential_meta = $publicKeyCredentialSourceRepository->findOneMetaByCredentialId(base64_decode($data_array["rawId"]));

                if($credential_meta !== null){
                    $allow_authenticator_type = wwa_get_option("allow_authenticator_type");
                    if($allow_authenticator_type !== false && $allow_authenticator_type !== 'none'){
                        if($credential_meta["authenticator_type"] !== $allow_authenticator_type){
                            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Credential type error, authenticator_type => \"".$credential_meta["authenticator_type"]."\", allow_authenticator_type => \"".$allow_authenticator_type."\", exit");
                            wwa_wp_die("Bad request.", $client_id);
                        }
                    }
                    if($credential_meta["usernameless"] === true){
                        wwa_add_log($res_id, "ajax_auth_response: Credential found, usernameless => \"true\", user_key => \"".$credential_meta["user"]."\"");

                        // Try to find user
                        $all_user = wwa_get_option("user_id");
                        $user_login_name = false;
                        foreach($all_user as $user => $user_id){
                            if($user_id === $credential_meta["user"]){
                                $user_login_name = $user;
                                break;
                            }
                        }

                        // Match userHandle
                        if($credential_meta["user"] === base64_decode($data_array["response"]["userHandle"])){
                            // Found user
                            if($user_login_name !== false){
                                wwa_add_log($res_id, "ajax_auth_response: Found user => \"".$user_login_name."\", user_key => \"".$credential_meta["user"]."\"");

                                // Testing, verify user
                                if($wwa_post["type"] === "test" && current_user_can('read')){
                                    $user_wp = wp_get_current_user();
                                    if($user_login_name !== $user_wp->user_login){
                                        wwa_add_log($res_id, "ajax_auth_response: (ERROR)Credential found, but user not match, exit");
                                        wwa_wp_die("Bad request.", $client_id);
                                    }
                                }
    
                                $user_info = get_user_by('login', $user_login_name);

                                if($user_info === false){
                                    wwa_add_log($res_id, "ajax_auth_response: (ERROR)Wrong user ID, exit");
                                    wwa_wp_die("Something went wrong.");
                                }

                                $userEntity = new PublicKeyCredentialUserEntity(
                                    $user_info->user_login,
                                    $credential_meta["user"],
                                    $user_info->display_name,
                                    get_avatar_url($user_info->user_email, array("scheme" => "https"))
                                );
                            }else{
                                wwa_add_log($res_id, "ajax_auth_response: (ERROR)Credential found, but user not found, exit");
                                wwa_wp_die("Bad request.", $client_id);
                            }
                        }else{
                            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Credential found, but userHandle not matched, exit");
                            wwa_wp_die("Bad request.", $client_id);
                        }
                    }else{
                        wwa_add_log($res_id, "ajax_auth_response: (ERROR)Credential found, but usernameless => \"false\", exit");
                        wwa_wp_die("Bad request.", $client_id);
                    }
                }else{
                    wwa_add_log($res_id, "ajax_auth_response: (ERROR)Credential not found, exit");
                    wwa_wp_die("Bad request.", $client_id);
                }
            }else{
                wwa_add_log($res_id, "ajax_auth_response: type => \"auth\", user => \"".$temp_val["user_name_auth"]."\"");
                $userEntity = unserialize($temp_val["user_auth"]);
            }
        }

        wwa_add_log($res_id, "ajax_auth_response: data => ".base64_decode($_POST["data"]));

        $rpEntity = new PublicKeyCredentialRpEntity(
            wwa_get_option("website_name"),
            wwa_get_option("website_domain")
        );

        $server = new Server(
            $rpEntity,
            $publicKeyCredentialSourceRepository,
            null
        );

        // Allow to bypass scheme verification when under localhost
        $current_domain = wwa_get_option("website_domain");
        if($current_domain === "localhost" || $current_domain === "127.0.0.1"){
            $server->setSecuredRelyingPartyId([$current_domain]);
            wwa_add_log($res_id, "ajax_auth_response: Localhost, bypass HTTPS check");
        }

        // Verify
        try {
            $server->loadAndCheckAssertionResponse(
                base64_decode($_POST["data"]),
                unserialize(base64_decode($temp_val["pkcco_auth"])),
                $userEntity,
                $serverRequest
            );

            wwa_add_log($res_id, "ajax_auth_response: Challenge verified");

            // Success
            $publicKeyCredentialSourceRepository->updateCredentialLastUsed(base64_decode(json_decode(base64_decode($_POST["data"]), true)["rawId"]));
            if(!($wwa_post["type"] === "test" && current_user_can("read"))){
                // Log user in
                if (!is_user_logged_in()) {
                    include("wwa-compatibility.php");

                    if(!$usernameless_flag){
                        $user_login = $temp_val["user_name_auth"];
                    }else{
                        $user_login = $user_login_name;
                    }

                    $user = get_user_by("login", $user_login);

                    if($user_info === false){
                        wwa_add_log($res_id, "ajax_auth_response: (ERROR)Wrong user ID, exit");
                        wwa_wp_die("Something went wrong.");
                    }

                    $user_id = $user->ID;

                    wwa_add_log($res_id, "ajax_auth_response: Log in user => \"".$user_login."\"");

                    $remember_flag = false;

                    if ($wwa_post["remember"] === "true" && (wwa_get_option("remember_me") === false ? "false" : wwa_get_option("remember_me")) !== "false") {
                        $remember_flag = true;
                        wwa_add_log($res_id, "ajax_auth_response: Remember login for 14 days");
                    }

                    wp_set_current_user($user_id, $user_login);
                    if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on"){
                        wp_set_auth_cookie($user_id, $remember_flag, true);
                    }else{
                        wp_set_auth_cookie($user_id, $remember_flag);
                    }
                    do_action("wp_login", $user_login, $user);
                }
            }
            echo "true";
        }catch(\Throwable $exception){
            // Failed to verify
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)".$exception->getMessage());
            wwa_add_log($res_id, wwa_generate_call_trace($exception));
            wwa_add_log($res_id, "ajax_auth_response: (ERROR)Challenge not verified, exit");
            wwa_wp_die("Something went wrong.", $client_id);
        }

        // Destroy session
        wwa_destroy_temp_val($client_id);
        exit;
    }catch(\Exception $exception){
        wwa_add_log($res_id, "ajax_auth_response: (ERROR)".$exception->getMessage());
        wwa_add_log($res_id, wwa_generate_call_trace($exception));
        wwa_add_log($res_id, "ajax_auth_response: (ERROR)Unknown error, exit");
        wwa_wp_die("Something went wrong.", $client_id);
    }catch(\Error $error){
        wwa_add_log($res_id, "ajax_auth_response: (ERROR)".$error->getMessage());
        wwa_add_log($res_id, wwa_generate_call_trace($error));
        wwa_add_log($res_id, "ajax_auth_response: (ERROR)Unknown error, exit");
        wwa_wp_die("Something went wrong.", $client_id);
    }
}
add_action("wp_ajax_wwa_auth" , "wwa_ajax_auth");
add_action("wp_ajax_nopriv_wwa_auth" , "wwa_ajax_auth");

// Get authenticator list
function wwa_ajax_authenticator_list(){
    $res_id = wwa_generate_random_string(5);

    wwa_init_new_options();

    if(!current_user_can("read")){
        wwa_add_log($res_id, "ajax_ajax_authenticator_list: (ERROR)Missing parameters, exit");
        wwa_wp_die("Something went wrong.");
    }

    $user_info = wp_get_current_user();

    if(isset($_GET["user_id"])){
        $user_id = intval(sanitize_text_field($_GET["user_id"]));
        if($user_id <= 0){
            wwa_add_log($res_id, "ajax_ajax_authenticator_list: (ERROR)Wrong parameters, exit");
            wwa_wp_die("Bad Request.");
        }

        if($user_info->ID !== $user_id){
            if(!current_user_can("edit_user", $user_id)){
                wwa_add_log($res_id, "ajax_ajax_authenticator_list: (ERROR)No permission, exit");
                wwa_wp_die("Something went wrong.");
            }
            $user_info = get_user_by('id', $user_id);

            if($user_info === false){
                wwa_add_log($res_id, "ajax_ajax_authenticator_list: (ERROR)Wrong user ID, exit");
                wwa_wp_die("Something went wrong.");
            }
        }
    }

    header('Content-Type: application/json');

    $user_key = "";
    if(!isset(wwa_get_option("user_id")[$user_info->user_login])){
        wwa_add_log($res_id, "ajax_ajax_authenticator_list: Empty authenticator list");
        // The user haven't bound any authenticator, return empty list
        echo "[]";
        exit;
    }else{
        $user_key = wwa_get_option("user_id")[$user_info->user_login];
    }

    $userEntity = new PublicKeyCredentialUserEntity(
        $user_info->user_login,
        $user_key,
        $user_info->display_name,
        get_avatar_url($user_info->user_email, array("scheme" => "https"))
    );

    $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();
    echo json_encode($publicKeyCredentialSourceRepository->getShowList($userEntity));
    exit;
}
add_action("wp_ajax_wwa_authenticator_list" , "wwa_ajax_authenticator_list");

// Modify an authenticator
function wwa_ajax_modify_authenticator(){
    try{
        $res_id = wwa_generate_random_string(5);

        wwa_init_new_options();

        wwa_add_log($res_id, "ajax_modify_authenticator: Start");

        if(!current_user_can("read")){
            wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)Permission denied, exit");
            wwa_wp_die("Bad Request.");
        }

        if(!isset($_GET["id"]) || !isset($_GET["target"])){
            wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)Missing parameters, exit");
            wwa_wp_die("Bad Request.");
        }

        $user_info = wp_get_current_user();

        if(isset($_GET["user_id"])){
            $user_id = intval(sanitize_text_field($_GET["user_id"]));
            if($user_id <= 0){
                wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)Wrong parameters, exit");
                wwa_wp_die("Bad Request.");
            }

            if($user_info->ID !== $user_id){
                if(!current_user_can("edit_user", $user_id)){
                    wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)No permission, exit");
                    wwa_wp_die("Something went wrong.");
                }
                $user_info = get_user_by('id', $user_id);

                if($user_info === false){
                    wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)Wrong user ID, exit");
                    wwa_wp_die("Something went wrong.");
                }
            }
        }

        if($_GET["target"] !== "rename" && $_GET["target"] !== "remove"){
            wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)Wrong target, exit");
            wwa_wp_die("Bad Request.");
        }

        if($_GET["target"] === "rename" && !isset($_GET["name"])){
            wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)Missing parameters, exit");
            wwa_wp_die("Bad Request.");
        }

        $user_key = "";
        if(!isset(wwa_get_option("user_id")[$user_info->user_login])){
            // The user haven't bound any authenticator, exit
            wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)User not initialized, exit");
            wwa_wp_die("User not inited.");
        }else{
            $user_key = wwa_get_option("user_id")[$user_info->user_login];
        }

        $userEntity = new PublicKeyCredentialUserEntity(
            $user_info->user_login,
            $user_key,
            $user_info->display_name,
            get_avatar_url($user_info->user_email, array("scheme" => "https"))
        );

        wwa_add_log($res_id, "ajax_modify_authenticator: user => \"".$user_info->user_login."\"");

        $publicKeyCredentialSourceRepository = new PublicKeyCredentialSourceRepository();

        if($_GET["target"] === "rename"){
            echo $publicKeyCredentialSourceRepository->modifyAuthenticator($_GET["id"], sanitize_text_field($_GET["name"]), $userEntity, "rename", $res_id);
        }else if($_GET["target"] === "remove"){
            echo $publicKeyCredentialSourceRepository->modifyAuthenticator($_GET["id"], "", $userEntity, "remove", $res_id);
        }
        exit;
    }catch(\Exception $exception){
        wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)".$exception->getMessage());
        wwa_add_log($res_id, wwa_generate_call_trace($exception));
        wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)Unknown error, exit");
        wwa_wp_die("Something went wrong.");
    }catch(\Error $error){
        wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)".$error->getMessage());
        wwa_add_log($res_id, wwa_generate_call_trace($error));
        wwa_add_log($res_id, "ajax_modify_authenticator: (ERROR)Unknown error, exit");
        wwa_wp_die("Something went wrong.");
    }
}
add_action("wp_ajax_wwa_modify_authenticator" , "wwa_ajax_modify_authenticator");

// Print log
function wwa_ajax_get_log(){
    if(!wwa_validate_privileges()){
        wwa_wp_die("Bad Request.");
    }

    header('Content-Type: application/json');

    $log = get_option("wwa_log");

    if($log === false){
        echo "[]";
    }else{
        echo json_encode($log);
    }

    exit;
}
add_action("wp_ajax_wwa_get_log" , "wwa_ajax_get_log");

// Clear log
function wwa_ajax_clear_log(){
    if(!wwa_validate_privileges()){
        wwa_wp_die("Bad Request.");
    }

    $log = get_option("wwa_log");

    if($log !== false){
        update_option("wwa_log", array());
    }

    echo "true";
    exit;
}
add_action("wp_ajax_wwa_clear_log" , "wwa_ajax_clear_log");
?>