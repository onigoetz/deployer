<?php


function prepare_directory($directory, $base)
{
    if ($directory[0] == '/') {
        return $directory;
    } else {
        return $base . $directory;
    }
}
