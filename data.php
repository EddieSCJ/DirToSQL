<?php

namespace data;

function insert($caminho, $arc_id)
{
    require_once("conexao.php");
    $conexao = novaConexao("postgres");

    $sql = "INSERT INTO ecg_data
    (caminho, arc_id)
    VALUES (
        ?, ?
    );";

    $stmt = $conexao->prepare($sql);
    if (!$stmt->execute([
        $caminho,
        $arc_id
    ])) {
        print_r($stmt->errorInfo());
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

function selectArcID($person, $folder)
{

    require_once("conexao.php");
    $conexao = novaConexao("postgres");

    $sql = "SELECT arc_id from header_archive WHERE per_id = ?;";

    $stmt = $conexao->prepare($sql);
    $per_id = selectPersonID($person, $folder);
    $stmt->execute([$per_id]);
    $id = $stmt->fetch();

    $conexao = null;
    return $id['arc_id'];
}

function migrate_paths_to_sql()
{
    $diretorios = array("MIT-ARRYTHMIA DATABASE", "EUROPEAN ST-T DATABASE", "ECG-ID DATABASE");
    foreach ($diretorios as $diretorio_inicial) {
        echo $diretorio_inicial . "<br>";
        $diretorio_1 = dir($diretorio_inicial);
        while ($pasta_1 = $diretorio_1->read()) {
            if ($pasta_1 != "." and $pasta_1 != ".." and $pasta_1 != "header_folder.txt") {
                $diretorio_2 = dir($diretorio_inicial . "/" . $pasta_1);
                while ($pasta_2 = $diretorio_2->read()) {
                    if ($pasta_2 != "." and $pasta_2 != ".." and $pasta_2 != "header_person.txt") {
                        // echo "----------$pasta_2 <br>";

                        $diretorio_3 = dir($diretorio_inicial . "/" .
                            $pasta_1 . "/" . $pasta_2);


                        while ($arquivo = $diretorio_3->read()) {
                            if ($arquivo != "." and $arquivo != "..") {

                                $file = $diretorio_inicial . "/" . $pasta_1 . "/" . $pasta_2 . "/" . $arquivo;

                                $person = explode("_", $pasta_1);
                                $person = (int) $person[1];
                                $arc_id = selectArcID($person, $diretorio_inicial);
                                insert($file, $arc_id);
                              //  echo "------------- $arquivo bem sucedido <br>";
                            }
                        }
                    }
                }
            }
        }
    }
}
