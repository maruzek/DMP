<?php

namespace App\ColorTheme;

class ColorTheme
{
    public function colorPallette($color)
    {
        switch ($color) {
            case 'white':
                $response = [
                    'background' => '',
                    'row' => '',
                    'post' => '',
                    'nav' => ''
                ];
                break;
            case 'blue':
                $response = [
                    'background' => '#66b3ff',
                    'row' => '#fff',
                    'post' => 'project-post-blue',
                    'nav' => ''
                ];
                break;
            case 'red':
                $response = [
                    'background' => '#ff6666',
                    'row' => '#fff',
                    'post' => 'project-post-red',
                    'nav' => 'project-nav-red'
                ];
                break;
            case 'green':
                $response = [
                    'background' => '#4dff4d',
                    'row' => '#fff',
                    'post' => 'project-post-green',
                    'nav' => 'project-nav-green'
                ];
                break;
            case 'yellow':
                $response = [
                    'background' => '#ffff66',
                    'row' => '#fff',
                    'post' => 'project-post-yellow',
                    'nav' => 'project-nav-yellow'
                ];
                break;
        }

        return $response;
    }
}
