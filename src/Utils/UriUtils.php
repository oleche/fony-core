<?php

namespace Geekcow\FonyCore\Utils;

class UriUtils
{
    public static function processUri($endpoint, $args, $verb): string
    {
        $verbString = "";
        $idString = "";
        if (is_array($args) && empty($args)) {
            if ($verb != ""){
                $idString = "/:id";
            }
        } else {
            if ((count($args) > 0) && (is_numeric($args[0]))) {
                $idString = "/:id";
                $verbString = "/".$verb;
            } else {
                if (preg_match(HashTypes::MD5, $verb)) {
                    $idString = "/:id";
                    if ((count($args) > 0)){
                        $verbString = "/".$args[0];
                    }
                } else {
                    $verbString = "/".$verb;
                }
            }
        }

        return '/'.$endpoint.$idString.$verbString;
    }
}