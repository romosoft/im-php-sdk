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
            throw new \Exception("参数错误");
        }
        // 判断name是否是全英文
        if (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
            throw new \Exception("用户名只能包含字母、数字和下划线");
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

        if ($response->getStatusCode() !== 200) {
            throw new \Exception("请求失败");
        }
        $content = $response->getBody()->getContents();
        if (!is_json($content)) {
            throw new \Exception("返回数据不是json");
        }
        $content = json_decode($content, true);
        $rt = $content["data"] ?? null;
        $code = $content["code"] ?? null;
        $msg = $content["msg"] ?? null;
        if ($code == ERR_SUCCESS) {
            if (!$rt) {
                throw new \Exception("返回数据为空");
            }
            return $rt;
        }
        throw new \Exception("$code $msg");
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