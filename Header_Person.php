<?php

namespace person;

function txtToJsonHeader($file, $nomeDB)
{
    $header = fopen($file, 'r');
    $dados = runArchiveHeaders($header);
    $dados["header_database_id"] = select("$nomeDB");
    fclose($header);

    return $dados;
}

function transformHeaders($chave_valor)
{
    if (trim($chave_valor[1]) == "not_informed" or trim($chave_valor[1]) == "not-informed") {
        $chave_valor[1] = null;
    }

    if (trim($chave_valor[1]) == "true") {
        $chave_valor[1] = true;
    }
    if ($chave_valor[1] !== null) {
        $chave_valor[0] = trim($chave_valor[0]);
        $chave_valor[1] = trim($chave_valor[1]);
    }
    return $chave_valor;
}

function separateHeaders($linha)
{
    $chave_valor = explode(":", $linha);
    $chave_valor = transformHeaders($chave_valor);

    return $chave_valor;
}

function runArchiveHeaders($header)
{
    while (!feof($header)) {
        $linha =  fgets($header, 1024);
        $chave_valor = separateHeaders($linha);

        $dados["$chave_valor[0]"] = $chave_valor[1];
    }
    return $dados;
}

function insert($file, $nomeDB)
{

    require_once("conexao.php");
    $conexao = novaConexao("postgres");

    $json = txtToJsonHeader($file, $nomeDB);
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

function select($nomeDB)
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

function runEcgID()
{
    for ($i = 1; $i <= 90; $i++) {

        $num = $i;
        if ($num < 10) {
            $num = 0 . $num;
        }

        insert(
            "ECG-ID DATABASE" . "/person_" . $num . "/header_person.txt",
            "ECG-ID DATABASE"
        );
    }
}

function runMITADB()
{
    for ($i = 101; $i <= 234; $i++) {
        if (is_dir("MIT-ARRYTHMIA DATABASE" . "/person_" . "$i")) {
            // $dados = txtToJsonHeader(
            //     "MIT-ARRYTHMIA DATABASE". "/person_" . $i . "/header_person.txt", 
            //     "MIT-ARRYTHMIA DATABASE");
            //  $dados = json_encode($dados);

            //  print_r($dados . "<br> <br>");
            insert(
                "MIT-ARRYTHMIA DATABASE" . "/person_" . $i . "/header_person.txt",
                "MIT-ARRYTHMIA DATABASE"
            );
        } 
    }
}

function runEUSTDB()
{
    
    for ($i = 103; $i <= 103; $i++) {
        $num = $i;
        if ($num < 1000) {
            $num = 0 . $num;
        }
        if (is_dir("EUROPEAN ST-T DATABASE" . "/person_" . "$num")) {
            insert(
                "EUROPEAN ST-T DATABASE" . "/person_" . $num . "/header_person.txt",
                "EUROPEAN ST-T DATABASE"
            );
            echo "Pessoa $num" . "adicionada com sucesso <br> ";
        } 
    }
}
