<?php

use GuzzleHttp\Client as HttpClient;

class Client
{
    private $domain = "https://www.leftsky.top/api/";
    private $project_code;
    private $secret;

    public function __construct($project_code, $secret)
    {
        $this->project_code = $project_code;
        $this->secret = $secret;
    }

    public function registerIM(array $data)
    {
        return self::restApiUseAdmin("chat/user/register", $data);
    }

    public function loginIM($name)
    {
        return self::restApiUseAdmin("chat/user/login", ["name" => $name]);
    }

    private function restApiUseAdmin($uri, $json)
    {
        $client = new HttpClient([
            "timeout" => 3
        ]);
        $json["project_code"] = $this->project_code;
        $json["secret"] = $this->secret;
        $json["signature"] = "";
        ksort($json);
        $json["signature"] = md5(implode(",", $json));
        unset($json["secret"]);
        $response = $client->post($this->domain . $uri, [
            "json" => $json
        ]);

        $code = $response->getStatusCode();
        if ($code !== 200) {
            return null;
        }
        $content = $response->getBody()->getContents();
        if (!is_json($content)) {
            return null;
        }
        $content = json_decode($content, true);
        $rt = $content["data"] ?? null;
        if ($content["code"] == ERR_SUCCESS && $rt == null) {
            return true;
        }
        return $rt;
    }

    public function createGroup($replace_user, $avatar, $name)
    {
        $data = [
            "replace_user" => $replace_user,
            "name" => trim(substr($name, 0, 20)),
            "avatar" => $avatar
        ];
        return self::restApiUseAdmin("chat/group/create", $data);
    }

    public function joinGroup(string $uname, string $group)
    {
        return self::restApiUseAdmin("chat/admin/addUserToGroup", [
            "uname" => $uname,
            "group" => $group
        ]);
    }

}