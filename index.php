<?php
require_once('Header_Folder.php');
require_once('Header_Person.php');
require_once('Header_Archive.php');
require_once('data.php');


const SRC = array("MIT-ARRYTHMIA DATABASE", "EUROPEAN ST-T DATABASE", "ECG-ID DATABASE");

// archive\runECGIDDB("filtered");

archive\runECGIDDB();


?>