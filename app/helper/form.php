<?php
function post($name)
{
    if ($_POST==[]){
        $_POST = json_decode(file_get_contents('php://input'),2);
    }

    if (isset($_POST[$name])) {
        if (is_array($_POST[$name]))
            return array_map(function ($item) {
                if (is_array($item))
                    return array_map(function ($item2) {
                        if (is_array($item2))
                            return array_map(function ($item3) {
                                return htmlspecialchars(trim($item3));
                            }, $item2);
                        return htmlspecialchars(trim($item2));
                    }, $item);
                return htmlspecialchars(trim($item));
            }, $_POST[$name]);
        return htmlspecialchars(trim($_POST[$name]));
    }
}


function get($name)
{
    if (isset($_GET[$name])) {
        if (is_array($_GET[$name]))
            return array_map(function ($item) {
                if (is_array($item))
                    return array_map(function ($item2) {
                        if (is_array($item2))
                            return array_map(function ($item3) {
                                return htmlspecialchars(trim($item3));
                            }, $item2);
                        return htmlspecialchars(trim($item2));
                    }, $item);
                return htmlspecialchars(trim($item));
            }, $_GET[$name]);
        return htmlspecialchars(trim($_GET[$name]));
    }
}

function form_control(...$except_these)
{
    unset($_POST["submit"]);
    $data = [];
    $error = false;
    foreach ($_POST as $key => $value) {
        if (is_array(post($key))) {
            foreach (post($key) as $key2 => $value2) {
                if (!in_array($key . '[' . $key2 . ']', $except_these) && !post($key)[$key2]) {
                    $error = true;
                } else {
                    $data[$key][$key2] = $value2;
                }
            }
        } else {
            if (!in_array($key, $except_these) && !post($key)) {
                $error = true;
            } else {
                $data[$key] = post($key);
            }
        }


    }
    if ($error) {
        return false;
    }
    return $data;
}

function idHash ($data,$decode=false){
    $secret = "{burasiidkismi_}";
    return $decode ? str_replace($secret,"",base64_decode($data)) : base64_encode($secret.$data) ;
}
