<?php

namespace archive;

function header($file, $person, $folder){
    $dados["per_id"] = selectPersonID($person, "$folder");
}

function CSVToJsonArc($file)
{
    $arquivo = fopen($file, 'r');
    $dados = runArchiveArc($arquivo);
    fclose($arquivo);
    return json_encode($dados);
}

function transformArc($chave_valor)
{
    $chave_valor[1] = (float) $chave_valor[1];
    return $chave_valor;
}

// function separateArc($linha)
// {
//     $chave_valor = explode(",", $linha);
//     // $chave_valor = transformArc($chave_valor);

//     return $chave_valor;
// }

function runArchiveArc($arquivo)
{
    $counter = 0;
    while (!feof($arquivo)) {
        $linha =  fgetArc($arquivo, 0);
        $counter++;

         if ($counter > 2) {
            if(isset($linha[0])){
                $linha = transformArc($linha);
                $dados["$linha[0]"] = $linha[1];
    
            }
            
          }
    }
    return $dados;
}

function insert($file, $person)
{

    require_once("conexao.php");
    $conexao = novaConexao("postgres");

    $json = CSVToJsonArc($file, $person, $folder);
    $sql = "INSERT INTO header_person
    (person, age, sex, date, medicament, diagnosis, database_id)
    VALUES (
        ?, ?, ?, ?, ?, ?, ?
    );";

    $stmt = $conexao->prepare($sql);
    $stmt->execute([
        $json['person'],
        $json['age'],
        $json['sex'],
        $json['date'],
        $json['medicament'],
        $json['diagnosis'],
        $json['header_database_id']
    ]);

    $conexao = null;
}

function selectDatabaseID($nomeDB)
{

    require_once("conexao.php");
    $conexao = novaConexao("postgres");

    $sql = "SELECT database_id from header_database WHERE database_name = ?;";

    $stmt = $conexao->prepare($sql);
    $stmt->execute([$nomeDB]);
    $id = $stmt->fetch();

    $conexao = null;
    return $id['database_id'];
}

function selectPersonID($person, $folder)
{

    require_once("conexao.php");
    $conexao = novaConexao("postgres");

    $sql = "SELECT per_id from header_person WHERE person = ? and database_id = ?;";

    $stmt = $conexao->prepare($sql);
    $database_id = selectDatabaseID($folder);
    $stmt->execute([$person, $database_id]);
    $id = $stmt->fetch();

    $conexao = null;
    return $id['per_id'];
}

function runECGIDDB($filter)
{
    $diretorio_inicial = "ECG-ID DATABASE";
    $diretorio_person = "/person_";

    $diretorio_1 = dir($diretorio_inicial);

    while($pasta_1 = $diretorio_1->read()){
            if($pasta_1 != "." and $pasta_1 != ".." and $pasta_1 != "header_folder.txt"){
                echo $pasta_1 . "<br>";
            }
    }
    // for ($i = 1; $i <= 1; $i++) {
    //     $num = $i;
    //     if ($num < 10) {
    //         $num = 0 . $num;
    //     }
    //     if (is_dir($folder . $person . "$num")) {

    //         $diretorio = dir($folder . $person . "$num" ."/" . $filter); 
    //         //while(
    //         $arquivo = $diretorio -> read();
    //         $arquivo = $diretorio -> read();
           
    //         echo $arquivo;
            //){

            //}
            

                    // $record = CSVToJsonArc($folder . 
                    // $person . "$num" .
                    // "/" . "$filter" . 
                    // "/" . $arquivo,
                    // "person_$i", 
                    // "ECG-ID DATABASE");
                    // print_r($record);

                    
            //}
            // insert(
            //     "ECG-ID DATABASE" . "/person_" . $num . "/header_person.CSV",
            //     "ECG-ID DATABASE"
            // );
            // echo "Pessoa $num" . "adicionada com sucesso <br> ";
    //     }
    // }

}



function lendoDir(){
    $path = "ECG-ID DATABASE" . "/person_" . "01" .
    "/" . "filtered/"; 
    $diretorio = dir($path); 
    while($arquivo = $diretorio -> read()){
        echo "<a href='".$path.$arquivo."'>".$arquivo."</a><br />";
    }
}

