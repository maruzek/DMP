<?php

namespace App\SSO;

// Service parsující data z SSO školy

class SSO
{
    private $response;

    // konstruktor

    public function __construct($response)
    {
        $data = [];
        foreach (explode("\n", $response) as $line) {
            $line = trim($line);
            if ($line == '') {
                continue;
            }

            $parsed = explode(':', $line, 2);

            if (count($parsed) != 2 || strtolower($parsed[0]) == "notice" || strtolower($parsed[0]) == "group" || strtolower($parsed[0]) == "group_name" || strtolower($parsed[0]) == "auth_by" || strtolower($parsed[0]) == "mail") {
                continue;
            }

            if ($parsed[0] == "name") {
                if ($parsed[1][0] == "x") {
                    $lastname = explode('.', $parsed[1])[1];
                    $firstname = explode('.', $parsed[1])[0];

                    $data["lastname"] = $lastname;
                    $data["firstname"] = $firstname;
                } else {
                    $lastname = explode(' ', $parsed[1])[1];
                    $firstname = explode(' ', $parsed[1])[0];

                    $data["lastname"] = $lastname;
                    $data["firstname"] = $firstname;
                }
            } else {
                $class = "";

                if ($parsed[0] == "ou_simple") {
                    $parsed[0] = "ou";
                    if ($parsed[1] != "ucitele") {
                        $classYear = (int)$parsed[1][1] . $parsed[1][2];

                        if (date('n') <= 12 && date('n') >= 9) {
                            $classNum = (int)date('y') - ($classYear - 1);
                        } else {
                            //$classNum = 3;
                            $classNum = (int)date('y') - ($classYear);
                        }

                        if (strlen($parsed[1]) == 4) {
                            $class = trim(strtoupper($parsed[1][3]) . $classNum);
                        } else if (strlen($parsed[1]) == 5) {
                            $class = trim(strtoupper($parsed[1][3]) . strtoupper($parsed[1][4]) . $classNum);
                        }
                    }
                }

                list($key, $value) = $parsed;
                if (!isset($data[$key])) {
                    $data[$key] = $value;
                    $data["role"] = "user";
                    $data["class"] = $class;

                    if ($parsed[1] == "ucitele") {
                        $data["tag"] = "učitel";
                    } else {
                        $data["tag"] = "sudent";
                    }
                }
            }

            $this->response = $data;
        }
    }

    public function getAllData(): array
    {
        return $this->response;
    }
}
