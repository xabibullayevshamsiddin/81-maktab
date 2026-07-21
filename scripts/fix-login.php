<?php
$file = "D:/OSPanel/domains/localhost/81-maktab/app/Http/Controllers/AuthController.php";
$c = file_get_contents($file);

$old = '    public function login()
    {
        return view("login.login");
    }';

$new = '    public function login()
    {
        return response()
            ->view("login.login")
            ->header("Content-Type", "text/html; charset=UTF-8");
    }';

$c = str_replace($old, $new, $c);
file_put_contents($file, $c);
echo "Fixed login response with explicit Content-Type\n";
