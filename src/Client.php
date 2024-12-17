<?php

namespace Romosoft\IM;

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

    public function register($avatar, $nickname, $username)
    {
        if (!$avatar || !$nickname || !$username) {
            return null;
        }
        return $this->restApiUseAdmin("chat/user/register", [
            "avatar" => $avatar,
            "nickname" => $nickname,
            "name" => $username
        ]);
    }

    public function login($username)
    {
        return $this->restApiUseAdmin("chat/user/login", ["name" => $username]);
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

    public function createGroup($username, $avatar, $groupName)
    {
        $data = [
            "replace_user" => $username,
            "name" => trim(substr($groupName, 0, 20)),
            "avatar" => $avatar
        ];
        return $this->restApiUseAdmin("chat/group/create", $data);
    }

    public function joinGroup($username, $groupCode)
    {
        return $this->restApiUseAdmin("chat/admin/addUserToGroup", [
            "uname" => $username,
            "group" => $groupCode
        ]);
    }

}