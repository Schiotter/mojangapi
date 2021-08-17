<?php
class mojangapi {

    //Public get Methods
    public function getbyName(String $PlayerName = '') {
        if(!$this->isValidPlayerName($PlayerName)) return FALSE; //End if PlayerName is invalid
        $user = $this->get_UserInfos($PlayerName);
        if($user === null) return FALSE; //Username Does not exist

        return $this->sortedArray(
            $user,
            $this->get_Textures($user['id']),
            $this->get_NameHistory($user['id'])
        );
    }

    public function getbyUUID(String $UUID = '') {
        if(!$this->isValidUUID($UUID)) return FALSE; //End if  UUID is invalid
        $textures = $this->get_Textures($UUID);
        if($textures === null) return FALSE; //UUID Does not exist

        return $this->sortedArray(
            $this->get_UserInfos($textures['profileName']),
            $textures,
            $this->get_NameHistory($UUID)
        );
    }

    /**
     * These methods are and should remain private.
     * Because they do not include any checks! 
     */

    //Mojang API Endpoints:
    private function get_UserInfos(String $Username) {
        return $this->getJSON('https://api.mojang.com/users/profiles/minecraft/'.$Username);
    }

    private function get_Textures(String $UUID) {
        $tmp = $this->getJSON('https://sessionserver.mojang.com/session/minecraft/profile/'.$UUID);
        return json_decode(base64_decode($tmp['properties'][0]['value']), true);
    }

    private function get_NameHistory(String $UUID) {
        return $this->getJSON('https://api.mojang.com/user/profiles/'.$UUID.'/names');
    }

    // Support Functions
    private function getJSON(String $URL) {

        /**
         * @param String $URL An URL to an JSON APi
         * @return null|Array The Response as a PHP-Array or null, if an error occurred
        */

        $user_agent='Mozilla/5.0 (Linux x86_64; rv:91.0) Gecko/20100101 Firefox/91.0';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $URL);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSLVERSION,CURL_SSLVERSION_DEFAULT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        $error = curl_error($ch); 
        curl_close ($ch);

        if($result === '')
            return null;

        return json_decode($result, TRUE);
    }

    private function isValidUUID(String $UUID):bool {

        /**
         * @param String $UUID An random or pseudo-random UUID to test for (UUIDv4)
         * @return bool returning true if the $UUID is a Valid UUID
         */

        if(strlen($UUID) === 32) {
            //Contains no hyphen, so we test if it is an hex-only string
            return ctype_xdigit($UUID);
        } else if (strlen($UUID) === 36) {
            //String contains hypen > test with regex
            $regex = '/^[0-9A-Fa-f]{8}(?:-[0-9A-Fa-f]{4}){3}-[0-9A-Fa-f]{12}$/';
            if (preg_match($regex, $UUID) === 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    private function isValidPlayerName(String $PlayerName):bool {

        /**
         * @param String $PlayerName The Name of a MC-Player to test
         * @return bool returning true if the given String could be a valid Username
         */

        #if(strlen($PlayerName) < 2 || strlen($PlayerName) > 16) return FALSE; //to short or to long, so return false immediately
        $regex = '/^([a-zA-Z0-9_]{3,16})$/'; //must be a string with uper/lowercase a-z, numbers or an underscore and only 3to16 chars long
        if(preg_match($regex, $PlayerName) === 1) {
            return true;
        } else {
            return false;
        }
    }

    private function sortedArray(Array $u, Array $t, Array $h):array {

        /**
         * @param Array $u User Informations Like isDemo or the Playername
         * @param Array $t The Array containing Texture Informations
         * @param Array $h History informations
         * @return Array sorted player informations
         */

        //Process Legacy and Demo
        if(empty($u['legacy'])) $u['legacy'] = FALSE; //only appears when true (not migrated to mojang account)
        if(empty($u['demo'])) $u['demo'] = FALSE; //only appears when true (account unpaid)

        foreach ($h as $j => $record) {
            if(empty($record['changedToAt'])) {
                $h[$j]['time'] = NULL;
                $h[$j]['timestamp'] = NULL;
            } else {
                // millisecond timestamp to unix timestamp
                $h[$j]['time'] = date('Y-m-d\TH:i:s\Z', $record['changedToAt']/1000); //Dateformat according to ISO 8601
                $h[$j]['timestamp'] = $record['changedToAt']/1000;
            }
            unset($h[$j]['changedToAt']);
        }

        //Prevent PHP-Warnings by seting CAPE to NULL if it does not exist
        if(empty($t['textures']['CAPE']['url'])){
            $cape = NULL;
        } else {
            $cape = $t['textures']['CAPE']['url'];
        }

        return array(
            'uuid' => $u['id'],
            'name' => $u['name'],
            'legacy' => $u['legacy'],
            'demo' => $u['demo'],
            'textures' => array(
                'skin' => $t['textures']['SKIN']['url'],
                'cape' => $cape
            ),
            'history' => $h
        );
    }
}
