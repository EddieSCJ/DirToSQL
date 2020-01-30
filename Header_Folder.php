<?php


function txtToJsonHeader($file, $src){
    $header = fopen($file, 'r');
    $dados = runArchiveHeaders($header);
    $dados["databaseName"] = $src;
    fclose($header);

    
    return $dados;
   
}

function transformHeaders($chave_valor){
    if(trim($chave_valor[1]) == "not_informed" or trim($chave_valor[1]) == "not-informed"){
        $chave_valor[1] = null;
    }

    if(trim($chave_valor[1]) == "true"){
        $chave_valor[1] = true;
    }
    return $chave_valor;
}

function separateHeaders($linha){
    $chave_valor = explode(":", $linha);
    $chave_valor = transformHeaders($chave_valor);
   
    return $chave_valor;
}

function runArchiveHeaders($header){
    while(!feof($header)){
        $linha =  fgets($header, 1024);
        $chave_valor = separateHeaders($linha);

        $dados["$chave_valor[0]"] = $chave_valor[1];
    }
    return $dados;    
}

function insert($file, $src){
   
    require_once("conexao.php");
    $conexao = novaConexao("postgres");

    $json = txtToJsonHeader($file, $src);


    $sql = "INSERT INTO header_database
    (database_name, records, people, men_quant, women_quant, min_age, max_age, filtered, not_filtered, variation)
    VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    );";
    
    $stmt= $conexao->prepare($sql);
    $stmt->execute([
        $json['databaseName'], 
        $json['records'],
        $json['people'],
        $json['men_quant'],
        $json['women_quant'],
        $json['min_age'],
        $json['max_age'],
        $json['filtered'],
        $json['not_filtered'],
        $json['variation']  
        ]);

    $conexao=null;
}

function run(...$src){
    for($i=0; $i<count($src); $i++){
        $file = $src[$i] . "/header_folder.txt";
       insert($file, $src[$i]);
   }
}

?>
