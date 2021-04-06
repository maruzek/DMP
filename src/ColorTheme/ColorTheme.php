<?php

namespace App\ColorTheme;

// Service, který uděluje barevné palety projektům
class ColorTheme
{
    public function colorPallette($color)
    {
        switch ($color) {
            case 'white':
                $response = [
                    'background' => '',
                    'row' => '#e4e4e4',
                    'post' => 'project-post-white',
                    'nav' => '',
                    'pills' => ''
                ];
                break;
            case 'blue':
                $response = [
                    'background' => '#66b3ff',
                    'row' => '#fff',
                    'post' => 'project-post-blue',
                    'nav' => '',
                    'pills' => 'project-pills-blue'
                ];
                break;
            case 'red':
                $response = [
                    'background' => '#ff6666',
                    'row' => '#fff',
                    'post' => 'project-post-red',
                    'nav' => 'project-nav-red',
                    'pills' => 'project-pills-red'
                ];
                break;
            case 'green':
                $response = [
                    'background' => '#4dff4d',
                    'row' => '#fff',
                    'post' => 'project-post-green',
                    'nav' => 'project-nav-green',
                    'pills' => 'project-pills-green'
                ];
                break;
            case 'yellow':
                $response = [
                    'background' => '#ffff66',
                    'row' => '#fff',
                    'post' => 'project-post-yellow',
                    'nav' => 'project-nav-yellow',
                    'pills' => 'project-pills-yellow'
                ];
                break;
        }

        return $response;
    }
}
