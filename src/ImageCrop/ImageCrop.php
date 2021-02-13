<?php

namespace App\ImageCrop;

use App\Entity\Project;
use App\Entity\User;

class ImageCrop
{
    private $file;
    private $entity;
    private $path;
    private $em;

    public function __construct($file, $path, $em)
    {
        $this->file = $file;
        $this->path = $path;
        $this->em = $em;
    }

    public function cropProjectImage(Project $project, $type = null)
    {
        $ext = $this->file->guessClientExtension();

        $filename = md5(uniqid()) . '.' . $ext;
        if ($ext == "jpeg" || $ext == "jpg") {
            $im = imagecreatefromjpeg($this->file);
            $x = imagesx($im);
            $y = imagesy($im);
            if ($x > $y) {
                if (($x / 4) + $y > $x) {
                    $ind = 1;
                    while ((($x / 4) + $y) - $ind >= $x) {
                        echo $ind;
                        $ind++;
                    }
                    $crop = imagecrop($im, ['x' => ($x / 4) - $ind, 'y' => 0, 'width' => $y, 'height' => $y]);
                } else {
                    $crop = imagecrop($im, ['x' => $x / 4, 'y' => 0, 'width' => $y, 'height' => $y]);
                }
            } else if ($x < $y) {
                //! ORIGINAL
                if (($y / 4) + $x > $y) {
                    $ind = 1;
                    while ((($y / 4) + $x) - $ind >= $y) {
                        $ind++;
                    }
                    $crop = imagecrop($im, ['x' => 0, 'y' => ($y / 4) - $ind, 'width' => $x, 'height' => $x]);
                } else {
                    $crop = imagecrop($im, ['x' => 0, 'y' => $y / 4, 'width' => $x, 'height' => $x]);
                }
            } else if ($x == $y) {
                $this->file->move(
                    $this->path,
                    $filename
                );
            }

            if ($crop !== false && $x !== $y) {
                imagedestroy($im);
                imagejpeg($crop, $this->path . '/' . $filename);
            }
            imagedestroy($crop);
        } else if ($ext == "png") {
            $im = imagecreatefrompng($this->file);
            $x = imagesx($im);
            $y = imagesy($im);
            if ($x > $y) {
                if (($x / 4) + $y > $x) {
                    $ind = 1;
                    while ((($x / 4) + $y) - $ind >= $x) {
                        echo $ind;
                        $ind++;
                    }
                    $crop = imagecrop($im, ['x' => ($x / 4) - $ind, 'y' => 0, 'width' => $y, 'height' => $y]);
                } else {
                    $crop = imagecrop($im, ['x' => $x / 4, 'y' => 0, 'width' => $y, 'height' => $y]);
                }
            } else if ($x < $y) {
                if (($y / 4) + $x > $y) {
                    $ind = 1;
                    while ((($y / 4) + $x) - $ind >= $y) {
                        $ind++;
                    }
                    $crop = imagecrop($im, ['x' => 0, 'y' => ($y / 4) - $ind, 'width' => $x, 'height' => $x]);
                } else {
                    $crop = imagecrop($im, ['x' => 0, 'y' => $y / 4, 'width' => $x, 'height' => $x]);
                }
            } else if ($x == $y) {
                $this->file->move(
                    $this->path,
                    $filename
                );
            }

            if ($crop !== false && $x !== $y) {
                imagedestroy($im);
                $this->file = imagepng($crop, $this->path . '/' . $filename);
            }
        } else {
            return false;
        }

        if (file_exists($this->path . '/' . $project->getImage()) && $project->getImage() != 'default.png' && $project->getImage() != '') {
            unlink($this->path . '/' . $project->getImage());
        }

        $project->setImage($filename);
        if ($type != "new") {
            $this->em->flush();
        }
        return true;
    }

    public function cropUserImage(User $user)
    {
        $ext = $this->file->guessClientExtension();

        if ($ext == "jpeg" || $ext == "jpg" || $ext == "png") {
            $filename = md5(uniqid()) . '.' . $ext;
            if ($ext == "jpeg" || $ext == "jpg") {
                $im = imagecreatefromjpeg($this->file);
                $x = imagesx($im);
                $y = imagesy($im);
                if ($x > $y) {
                    if (($x / 4) + $y > $x) {
                        $ind = 1;
                        while ((($x / 4) + $y) - $ind >= $x) {
                            echo $ind;
                            $ind++;
                        }
                        $crop = imagecrop($im, ['x' => ($x / 4) - $ind, 'y' => 0, 'width' => $y, 'height' => $y]);
                    } else {
                        $crop = imagecrop($im, ['x' => $x / 4, 'y' => 0, 'width' => $y, 'height' => $y]);
                    }
                } else if ($x < $y) {
                    //! ORIGINAL
                    if (($y / 4) + $x > $y) {
                        $ind = 1;
                        while ((($y / 4) + $x) - $ind >= $y) {
                            $ind++;
                        }
                        $crop = imagecrop($im, ['x' => 0, 'y' => ($y / 4) - $ind, 'width' => $x, 'height' => $x]);
                    } else {
                        $crop = imagecrop($im, ['x' => 0, 'y' => $y / 4, 'width' => $x, 'height' => $x]);
                    }
                } else if ($x == $y) {
                    $this->file->move(
                        $this->path,
                        $filename
                    );
                }

                if ($crop !== false && $x !== $y) {
                    imagedestroy($im);
                    imagejpeg($crop, $this->path . '/' . $filename);
                }
                imagedestroy($crop);
            } else if ($ext == "png") {
                $im = imagecreatefrompng($this->file);
                $x = imagesx($im);
                $y = imagesy($im);
                if ($x > $y) {
                    if (($x / 4) + $y > $x) {
                        $ind = 1;
                        while ((($x / 4) + $y) - $ind >= $x) {
                            echo $ind;
                            $ind++;
                        }
                        $crop = imagecrop($im, ['x' => ($x / 4) - $ind, 'y' => 0, 'width' => $y, 'height' => $y]);
                    } else {
                        $crop = imagecrop($im, ['x' => $x / 4, 'y' => 0, 'width' => $y, 'height' => $y]);
                    }
                } else if ($x < $y) {
                    if (($y / 4) + $x > $y) {
                        $ind = 1;
                        while ((($y / 4) + $x) - $ind >= $y) {
                            $ind++;
                        }
                        $crop = imagecrop($im, ['x' => 0, 'y' => ($y / 4) - $ind, 'width' => $x, 'height' => $x]);
                    } else {
                        $crop = imagecrop($im, ['x' => 0, 'y' => $y / 4, 'width' => $x, 'height' => $x]);
                    }
                } else if ($x == $y) {
                    $this->file->move(
                        $this->path,
                        $filename
                    );
                }

                if ($crop !== false && $x !== $y) {
                    imagedestroy($im);
                    $this->file = imagepng($crop, $this->path . '/' . $filename);
                }
            }

            if (file_exists($this->path . '/' . $user->getImage()) && $user->getImage() != 'default.png') {
                unlink($this->path . '/' . $user->getImage());
            }

            $user->setImage($filename);
            $this->em->flush();
            return true;
        } else {
            return false;
        }
    }
}
