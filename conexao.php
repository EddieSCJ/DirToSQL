<?php

function novaConexao($banco = 'postgres') {
    $servidor = 'localhost';
    $usuario = 'postgres';
    $senha = '44657235';

    try {
        $conexao = new PDO("pgsql:host=$servidor;dbname=$banco",
            $usuario, $senha);
            return $conexao;

    } catch(PDOException $e) {
        die('Erro: ' . $e->getMessage());
    }
}

?>