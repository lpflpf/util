<?php

$sql = <<<SQL
SQL;

$data = format($sql);

echo printStruct($data, true);

function typeFormat($type){
    switch ($type){
    case "tinyint":  return "int";
    case "smallint":return "int";
    case "mediumint":return "int";
    case "int":return "int";
    case "bigint":return "int64";

    case "float": return "float";
    case "double": return "float64";
    case "decimal": return "float64";

    case "date": return "time.Time";
    case "time": return "time.Time";
    case "year": return "time.Time";
    case "datetime": return "time.Time";
    case "timestamp": return "time.Time";

    case "char": return "string";
    case "varchar": return "string";
    case "tinyblob": return "string";
    case "tinytext": return "string";
    case "blob": return "string";
    case "text": return "string";
    case "medumblob": return "string";
    case "mediumtext": return "string";
    case "longblob": return "string";
    case "longtext": return "string";
    }

    return "";
}

function nameFormat($name){
    $name = preg_replace_callback('/(^|[^a-zA-Z])([a-z]+)/i', function ($matches){
        $commonList = array(
            "ACL", "API", "ASCII", "CPU", "CSS", "DNS", "EOF", "GUID", "HTML", "HTTP", 
            "HTTPS", "ID", "IP", "JSON", "LHS", "QPS", "RAM", "RHS", "RPC", "SLA", 
            "SMTP", "SQL", "SSH", "TCP", "TLS", "TTL", "UDP", "UI", "UID", "UUID", 
            "URI", "URL", "UTF8", "VM", "XML", "XMPP", "XSRF", "XSS", "CRM", "AID", "SID", "PID");

        foreach($commonList as $initial){
            if (strtoupper($matches[2]) == $initial){
                return $matches[1] . $initial;
            }
        }
        return $matches[0];
    }, $name);

    $str = preg_replace_callback('/([-_]+([a-z]{1}))/i',function($matches){
        return strtoupper($matches[2]);
    },$name);
    return ucfirst($str);
}

function format($sql){
    preg_match('/^\s*CREATE\s+TABLE\s*`(\w+)`\s*\((.*)\)/is', $sql, $result);
    $tablename = $result[1];
    $data = array();

    foreach(explode(",", $result[2]) as $row){
        preg_match("/^\s*`(\w+)`\s([\w]+)/is", trim($row), $result);
        if (count($result) == 0){
            continue;
        }

        $name = $result[1];
        $type = $result[2];

        preg_match("/COMMENT\s+'([^']+)'$/i", trim($row), $result);
        $comment = $result[1];

        $data[] = array(
            "name" => nameFormat($name),
            "type"   => typeFormat($type),
            "comment"=> $comment,
            "column" => $name,
        );
    }

    return array(
        'table' => nameFormat($tablename),
        'columns' => $data
    );
}


function printStruct($data, $json = false){
    $output = sprintf("type %s struct {\n", $data['table']);

    $namewidth = 0;
    $typewidth = 0;
    $columnwidth = 0;

    foreach($data['columns'] as $column){
        if ($namewidth < strlen($column['name'])){
            $namewidth = strlen($column['name']);
        }

        if ($typewidth < strlen($column['type'])){
            $typewidth = strlen($column['type']);
        }

        if ($columnwidth < strlen($column['column'])){
            $columnwidth = strlen($column['column']);
        }
    }

    $tagwidth = strlen('`gorm:"column:"`') + $columnwidth;
    $tagformat = '`gorm:"column:%s"';
    if ($json){
        $tagwidth += strlen('json:""') + $columnwidth + 2;
        $tagformat .= ' json:"%s"';
    }
    $tagformat .= "`";

    $format = '    %-' . $namewidth . 's %-' . $typewidth . 's %-' . $tagwidth . 's';

    foreach($data['columns'] as $column){
        if ($json){
            $tag = sprintf($tagformat, $column['column'], $column['column']);
        }else{
            $tag = sprintf($tagformat, $column['column']);
        }
        $output .= sprintf($format, $column['name'], $column['type'], $tag);

        if ($column['comment']){
            $output .= "//" . $column['comment'];
        }
        $output .= "\n";
    }

    $output .= "}\n";

    return $output;
}

