<?php

namespace archive;

function header($file, $folder, $person, $filtered, $arc_name)
{
    $dir = $folder . "/" . $person . "/" . $filtered;
    //CSVToJsonArc($file, $dir, $arc_name);

    $person = explode("_", $person);
    $person = (int) $person[1];

    $dados["per_id"] = selectPersonID($person, "$folder");
    $dados["arc_name"] = $arc_name;
    $dados["filtered"] = $filtered == "filtered" or $filtered == "FILTERED" ? true : false;
    return $dados;
}

function CSVToJsonArc($file, $dir, $arc_name)
{
    $arquivo = fopen($file, 'r');
    $dados = runArchiveArc($arquivo);
    fclose($arquivo);

    $arquivoJSON = fopen("$dir" . "/" . "$arc_name" . ".json", 'a+');
    $json = json_encode($dados);
    fwrite($arquivoJSON, $json);
    fclose($arquivoJSON);
    return $dados;
}

function transformArc($chave_valor)
{
    $chave_valor[1] = (float) $chave_valor[1];
    return $chave_valor;
}
function runArchiveArc($arquivo)
{
    $counter = 0;
    while (!feof($arquivo)) {
        $linha =  fgetcsv($arquivo, 0);
        $counter++;

        if ($counter > 2) {
            if (isset($linha[0])) {
                $linha = transformArc($linha);
                $dados["$linha[0]"] = $linha[1];
            }
        }
    }
    return $dados;
}

function insert($file, $folder, $person, $filtered, $arc_name)
{
    require_once("conexao.php");
    $conexao = novaConexao("postgres");

    $dados = header($file, $folder, $person, $filtered, $arc_name);

    $sql = "INSERT INTO header_archive
    (arc_name, filtered, per_id)
    VALUES (
        ?, ?, ?
    );";

    $stmt = $conexao->prepare($sql);
    if (!$stmt->execute([
        $dados['arc_name'],
        $dados['filtered'] == "" ? "false" : "true",
        $dados['per_id']
    ])) {
        print_r($stmt->errorInfo());
        echo "<br> " . $file;
        die("fodeu-se baixinho");
    }

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
function enviar($arc)
{
    $file = explode(".", $arc);
    if ($file[1] == "json") {
        return false;
    } else {
        return true;
    }
}
function runECGIDDB()
{
    $diretorios = array("MIT-ARRYTHMIA DATABASE", "EUROPEAN ST-T DATABASE", "ECG-ID DATABASE");

    foreach ($diretorios as $diretorio_inicial) {
        $diretorio_1 = dir($diretorio_inicial);

        while ($pasta_1 = $diretorio_1->read()) {
            if ($pasta_1 != "." and $pasta_1 != ".." and $pasta_1 != "header_folder.txt") {
                $diretorio_2 = dir($diretorio_inicial . "/" . $pasta_1);

                while ($pasta_2 = $diretorio_2->read()) {
                    if ($pasta_2 != "." and $pasta_2 != ".." and $pasta_2 != "header_person.txt") {

                        $diretorio_3 = dir($diretorio_inicial . "/" .
                            $pasta_1 . "/" . $pasta_2);

                        while ($arquivo = $diretorio_3->read()) {
                            if ($arquivo != "." and $arquivo != "..") {
                                $file = $diretorio_inicial . "/" . $pasta_1 . "/" . $pasta_2 . "/" . $arquivo;
                                $arquivo = explode(".", $arquivo);
                                $arquivo = $arquivo[0] . "." . $arquivo[1];
                                $dir = $diretorio_inicial . "/" . $pasta_1 . "/" . $pasta_2;
                                CSVToJsonArc($file, $dir, $arquivo);
                                //insert($file, $diretorio_inicial, $pasta_1, $pasta_2, $arquivo);
                            }
                        }
                    }
                }
            }
        }
    }
}



function lendoDir()
{
    $path = "ECG-ID DATABASE" . "/person_" . "01" .
        "/" . "filtered/";
    $diretorio = dir($path);
    while ($arquivo = $diretorio->read()) {
        echo "<a href='" . $path . $arquivo . "'>" . $arquivo . "</a><br />";
    }
}
