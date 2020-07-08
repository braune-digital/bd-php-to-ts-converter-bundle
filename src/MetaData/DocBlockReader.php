<?php

declare(strict_types=1);

namespace BrauneDigital\PhpToTypescriptConverterBundle\MetaData;

class DocBlockReader
{
    public function __invoke(string $docblock): DocBlock
    {
        $description = null;
        $parts = [];

        // split at each line
        foreach(preg_split("/(\r?\n)/", $docblock) as $line)
        {
            $matches = [];

            if(!preg_match('/^(?=\s+?\*[^\/])(.+)/', $line, $matches)) {
                continue;
            }

            $info = $matches[1];

            // remove wrapping whitespace
            $info = trim($info);

            // remove leading asterisk
            $info = preg_replace('/^(\*\s+?)/', '', $info);

            // if it doesn't start with an "@" symbol
            // then add to the description
            if( $info[0] !== "@" ) {
                if(null === $description) {
                    $description = $info;
                } else {
                    $description .= "\n$info";
                }

                continue;
            }

            // get the name of the param
            preg_match('/@(\w+)/', $info, $matches);
            $param_name = $matches[1];

            // remove the param from the string
            $value = str_replace("@$param_name ", '', $info);

            // if the param hasn't been added yet, create a key for it
            if( !isset($parts[$param_name]) ) {
                $parts[$param_name] = array();
            }

            // push the param value into place
            $parts[$param_name][] = $value;
        }

        return new DocBlock($description, $parts);
    }
}