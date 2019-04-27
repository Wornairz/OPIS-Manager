<?php
function filter_graph($url, $fileName, $fieldName) {

  $url = str_replace("./" . $fileName . ".php?", "", $url);
  $url = str_replace($fieldName, "", $url);

  $url = str_replace("1=", "", $url);
  $url = str_replace("2=", "", $url);
  $url = str_replace("3=", "", $url);
  $url = str_replace("4=", "", $url);
  $url = str_replace("5=", "", $url);
  $url = str_replace("6=", "", $url);
  $url = str_replace("7=", "", $url);
  $url = str_replace("8=", "", $url);
  $url = str_replace("9=", "", $url);
  $url = str_replace("10=", "", $url);
  $url = str_replace("FC=", "", $url);

  $url = explode("&", $url);

  return $url;
}

function fill_arr($arr, $elements) {
  foreach ($arr as $key=>$value)
    $arr[$key] = array(@$elements[$key], $arr[$key]);

  return $arr;
}

function check_special_chars(&$str){
  for($j=0; $j<strlen($str)-1; $j++)
    if(strlen(mb_substr($str, $j, 1, 'utf-8')) == 2) {             //se il carattere della stringa occupa 2 caratteri vuol dire che è un carattere "strano"
      $str = substr($str, 0, $j)."0".substr($str, $j+2);    //togliamo i primi 2 (che sarebbe il primo, ma quello strano) caratteri della stringa e concateniamo lo 0
    }
}

function schede($id_cds, $id_gomp, $id_modulo, $canale, $cod_modulo) {
  global $link, $mysqli, $year;

  $url = $link . "val_insegn.php?cod_corso=" . $id_cds . "&cod_gomp=" . $id_gomp . "&cod_modulo=" . $id_modulo . "&canale=" . $canale;

  $xpath = new DOMXPath(getDOM($url));

  $totaleSchede     = $xpath->query('/html/body/table[1]/tr/td/table[4]/tr[2]/td[2]')->item(0)->textContent;
  $femmine          = $xpath->query('/html/body/table[1]/tr/td/table[4]/tr[2]/td[3]')->item(0)->textContent;
  $fuoriCorso       = $xpath->query('/html/body/table[1]/tr/td/table[4]/tr[2]/td[4]')->item(0)->textContent;
  $inattivi         = $xpath->query('/html/body/table[1]/tr/td/table[4]/tr[2]/td[5]')->item(0)->textContent;

  $totaleSchede_nf  = $xpath->query('/html/body/table[1]/tr/td/table[4]/tr[3]/td[2]')->item(0)->textContent;
  // $femmine_nf       = $xpath->query('/html/body/table[1]/tr/td/table[4]/tr[3]/td[3]')->item(0)->textContent; // "non prevista"
  $femmine_nf       = 0; // "non prevista"
  //$fuoriCorso_nf    = $xpath->query('/html/body/table[1]/tr/td/table[4]/tr[3]/td[4]')->item(0)->textContent;
  $fuoriCorso_nf    = 0;
  $inattivi_nf      = $xpath->query('/html/body/table[1]/tr/td/table[4]/tr[3]/td[5]')->item(0)->textContent;
  if ($inattivi_nf=='')
    $inattivi_nf = 0;

  /* data from graphs */
  $eta = "";
  $el = $xpath->query('/html/body/table[1]/tr/td/table[5]/tr/td//img')->item(0);
  if ($el) {
    $eta = $el->getAttribute("src");
    $eta = filter_graph($eta, "graph_eta", "eta");
    $eta = fill_arr($eta, array("18-19","20-21","22-23","24-25","26-27","28-29","30 e oltre"));
  }

  $anno_iscr = "";
  $el = $xpath->query('/html/body/table[1]/tr/td/table[5]/tr/td[2]//img')->item(0);
  if ($el) {
    $anno_iscr = $el->getAttribute("src");
    $anno_iscr = filter_graph($anno_iscr, "graph_annoiscr", "iscr");
    $anno_iscr = fill_arr($anno_iscr, array("1", "2", "3", "4", "5", "6", "FC"));
  }

  $n_studenti = "";
  $el = $xpath->query('/html/body/table[1]/tr/td/table[5]/tr[2]/td[1]//img')->item(0);
  if ($el) {
    $n_studenti = $el->getAttribute("src");
    $n_studenti = filter_graph($n_studenti, "graph_stud_freq", "stud");
    $n_studenti = fill_arr($n_studenti, array("fino 25", "26-50", "51-75", "76-100", "101-151", "151-200", "oltre 200"));
  }

  $ragg_uni = "";
  $el = $xpath->query('/html/body/table[1]/tr/td/table[5]/tr[2]/td[2]//img')->item(0);
  if ($el) {
    $ragg_uni = $el->getAttribute("src");
    $ragg_uni = filter_graph($ragg_uni, "graph_tempo_univ", "tmp");
    $ragg_uni = fill_arr($ragg_uni, array("fino 0.5", "0.5-1", "1-2", "2-3", "oltre 3"));
  }

  $studio_gg = "";
  $el = $xpath->query('/html/body/table[1]/tr/td/table[5]/tr[3]/td[1]//img')->item(0);
  if ($el) {
    $studio_gg = $el->getAttribute("src");
    $studio_gg = filter_graph($studio_gg, "graph_ore_studio_gg", "ore");
    $studio_gg = fill_arr($studio_gg, array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10"));
  }

  $studio_tot = "";
  $el = $xpath->query('/html/body/table[1]/tr/td/table[5]/tr[3]/td[2]//img')->item(0);
  if ($el) {
    $studio_tot = $el->getAttribute("src");
    $studio_tot = str_replace("&ore8=0", "", $studio_tot);
    $studio_tot = filter_graph($studio_tot, "graph_ore_studio_tot", "ore");
    $studio_tot = fill_arr($studio_tot, array("fino 50", "51-100", "101-150", "201-250", "251-300", "301-350", "oltre 300"));
  }

  // questions answers
  $domande = "";
  $el = $xpath->query('/html/body/table[1]/tr/td/table[6]/tr/td[1]')->item(0);
  if ($el && !strpos($el->textContent, "schede insuff.")) {
    $domande = array();
    for ($i = 2; $i < 14; $i++)
      for ($j = 2; $j < 7; $j++)
        $domande[] = $xpath->query('/html/body/table[1]/tr/td/table[6]/tr/td/table[1]/tr[' . $i . ']/td[' . $j . ']')->item(0)->textContent;
  }

  // questions answers (nf)
  $domande_nf = "";
  $el = $xpath->query('/html/body/table[1]/tr/td/table[6]/tr/td[2]')->item(0);
  if ($el && !strpos($el->textContent, "schede insuff.")) {
    $domande_nf = array();
    for ($i = 2; $i < 14; $i++) {
      if ($i == 6)
        $i += 5;

      for ($j = 2; $j < 7; $j++)
        $domande_nf[] = $xpath->query('/html/body/table[1]/tr/td/table[6]/tr/td[2]/table[1]/tr[' . $i . ']/td[' . $j . ']')->item(0)->textContent;
    }

    for ($i = 20; $i < 45; $i++)
      $domande_nf[$i] = "dom. non prevista";
  }

  // reasons nf
  $motivi_nf = "";
  if ($xpath->query('/html/body/table[1]/tr/td/table[6]/td[2]/table/tr[2]/td[1]')->item(0) != NULL && !strpos($xpath->query('/html/body/table[1]/tr/td/table[6]/td[2]')->item(0)->textContent, "schede insuff.")) {
    $motivi_nf = array();
    for ($i = 2; $i < 9; $i++)
      $motivi_nf[] = array(
        $xpath->query('/html/body/table[1]/tr/td/table[6]/td[2]/table/tr[' . $i . ']/td[1]')->item(0)->textContent,
        $xpath->query('/html/body/table[1]/tr/td/table[6]/td[2]/table/tr[' . $i . ']/td[2]')->item(0)->textContent
      );
  }

  // suggestions
  $sugg = "";
  $el = $xpath->query('/html/body/table[1]/tr/td/table[6]/tr[3]/td[1]/table[1]/tr[2]/td[1]')->item(0);
  if ($el && !strpos($el->textContent, "schede insuff.")) {
    $sugg = array();
    for ($i = 2; $i < 12; $i++)
      $sugg[] = array(
        $xpath->query('/html/body/table[1]/tr/td/table[6]/tr[3]/td[1]/table[1]/tr[' . $i . ']/td[1]')->item(0)->textContent,
        $xpath->query('/html/body/table[1]/tr/td/table[6]/tr[3]/td[1]/table[1]/tr[' . $i . ']/td[2]')->item(0)->textContent
      );
  }

  // suggestions nf
  $sugg_nf = "";
  $el = $xpath->query('/html/body/table[1]/tr/td/table[6]/tr[3]/td[2]/table[1]/tr[2]/td[1]')->item(0);
  if ($el && !strpos($el->textContent, "schede insuff.")) {
    $sugg_nf = array();
    for ($i = 2; $i < 12; $i++)
      $sugg_nf[] = array(
        $xpath->query('/html/body/table[1]/tr/td/table[6]/tr[3]/td[2]/table[1]/tr[' . $i . ']/td[1]')->item(0)->textContent,
        $xpath->query('/html/body/table[1]/tr/td/table[6]/tr[3]/td[2]/table[1]/tr[' . $i . ']/td[2]')->item(0)->textContent
      );
  }

  /* Serializing data */

  // graphs
  $eta        = json_encode($eta);
  $anno_iscr  = json_encode($anno_iscr);
  $n_studenti = json_encode($n_studenti);
  $ragg_uni   = json_encode($ragg_uni);
  $studio_gg  = json_encode($studio_gg);
  $studio_tot = json_encode($studio_tot);

  // questions
  $domande    = json_encode($domande);
  $domande_nf = json_encode($domande_nf);
  $motivi_nf  = json_encode($motivi_nf);
  $sugg       = json_encode($sugg);
  $sugg_nf    = json_encode($sugg_nf);

  $_year = str_replace("20", "", $year);
  $_year = str_replace("/", "", $_year);
  $res = $mysqli->query('SELECT * FROM schede WHERE id_insegnamento=\'' . $id_gomp . '\' AND  id_modulo=\''. $cod_modulo . '\' AND canale=\''. $canale .'\' AND anno_accademico='.$_year.';');
  if ($res && $res->num_rows <= 0) {
    $query = "INSERT INTO `schede` (`totale_schede`, `totale_schede_nf`, `femmine`, `femmine_nf`, `fc`, `inatt`, `inatt_nf`, `eta`, `anno_iscr`, `num_studenti`, `ragg_uni`, `studio_gg`, `studio_tot`, `domande`, `domande_nf`, `motivo_nf`, `sugg`, `sugg_nf`, `id_insegnamento`,`id_modulo`, `canale`, `anno_accademico`) VALUES";
    $query .= "\n";
    $query .= utf8_decode('("' . urldecode(str_replace('"', "'", $totaleSchede)) . '", "' . urldecode(str_replace('"', "'", $totaleSchede_nf)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $femmine)) . '", "' . urldecode(str_replace('"', "'", $femmine_nf)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $fuoriCorso)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $inattivi)) . '", "' . urldecode(str_replace('"', "'", $inattivi_nf)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $eta)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $anno_iscr)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $n_studenti)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $ragg_uni)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $studio_gg)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $studio_tot)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $domande)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $domande_nf)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $motivi_nf)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $sugg)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $sugg_nf)) . '", ' .
                '"' . urldecode($id_gomp) . '", ' .
                '"' . urldecode($cod_modulo) . '", ' .
                '"' . urldecode($canale) . '",
              "' . $year . '");');

  if (!$mysqli->query($query))
    die($mysqli->error);

    // echo $query;

  }

}

function oldschede($id_cds, $id_gomp, $canale) {
  global $link, $mysqli, $year;

  $url = $link . "val_insegn.php?cod_corso=" . $id_cds . "&cod_gomp=" . $id_gomp . "&canale=" . $canale;

  $xpath = new DOMXPath(getDOM($url));

  $cod_modulo = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[2]/td[' . ($year == "2013/2014" ? 5 : 2) . ']')->item(0)->textContent;
  $modulo     = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[2]/td[' . ($year == "2013/2014" ? 6 : 3) . ']')->item(0)->textContent;
  $tipo       = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[2]/td[' . ($year == "2013/2014" ? 9 : 6) . ']')->item(0)->textContent;
  $ssd        = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[2]/td[' . ($year == "2013/2014" ? 11 : 8) . ']')->item(0)->textContent;
  $docente    = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[2]/td[' . ($year == "2013/2014" ? 12 : 9) . ']')->item(0)->textContent;
  $assegn     = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[2]/td[' . ($year == "2013/2014" ? 13 : 10) . ']')->item(0)->textContent;
  
  $query =  'UPDATE insegnamento SET id_modulo = "' . explode('-', $cod_modulo)[0] . '", ssd = "' . explode('-', $ssd)[0] . '", tipo = "' . explode('-', $tipo)[0] . '", docente = "' . explode('-', $docente)[0] . '", assegn = "' . explode('-', $assegn)[0] . '" ' .
            'WHERE id_cds = ' . $id_cds . ' AND id = ' . $id_gomp . ' AND anno_accademico = "' . $year . '";';
  
  if (!$mysqli->query($query))
    die($mysqli->error);

  if($xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[3]')->item(0) != NULL){ // c'è un altro modulo
    $cod_modulo .= '-' . $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[3]/td[' . ($year == "2013/2014" ? 5 : 2) . ']')->item(0)->textContent;
    $modulo     .= '-' . $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[3]/td[' . ($year == "2013/2014" ? 6 : 3) . ']')->item(0)->textContent;
    $tipo       .= '-' . $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[3]/td[' . ($year == "2013/2014" ? 9 : 6) . ']')->item(0)->textContent;
    $ssd        .= '-' . $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[3]/td[' . ($year == "2013/2014" ? 11 : 8) . ']')->item(0)->textContent;
    $docente    .= '-' . $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[3]/td[' . ($year == "2013/2014" ? 12 : 9) . ']')->item(0)->textContent;
    $assegn     .= '-' . $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 2 : 3) . ']/tr[3]/td[' . ($year == "2013/2014" ? 13 : 10) . ']')->item(0)->textContent;
  
    $query = 'UPDATE insegnamento SET id_modulo = "' . explode('-', $cod_modulo)[1] . '", ssd = "' . explode('-', $ssd)[1] . '", tipo = "' . explode('-', $tipo)[1] . '", docente = "' . explode('-', $docente)[1] . '", assegn = "' . explode('-', $assegn)[1] . '" ' .
              'WHERE id_cds = ' . $id_cds . ' AND id = ' . $id_gomp . ' AND anno_accademico = "' . $year . '";';
  
    if (!$mysqli->query($query))
      die($mysqli->error);
  }

  //$anno = substr(explode("/", $year)[0], 2) . substr(explode("/", $year)[1], 2);


  $totaleSchede_f = $totaleSchede_nf = $inattivi = $inattivi_nf = $fuoriCorso = $fuoriCorso_nf = 0;

  $totaleSchede_f     = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 3 : 5) . ']/tr[2]/td[2]/b')->item(0)->textContent;
  $totaleSchede_nf  = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 3 : 5) . ']/tr[3]/td[2]/b')->item(0)->textContent;
  
  $inattivi         = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 3 : 5) . ']/tr[2]/td[3]')->item(0)->textContent;
  $inattivi_nf      = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 3 : 5) . ']/tr[3]/td[3]')->item(0)->textContent;

  $fuoriCorso       = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 3 : 5) . ']/tr[2]/td[4]')->item(0)->textContent;
  $fuoriCorso_nf    = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 3 : 5) . ']/tr[3]/td[4]')->item(0)->textContent;

  if($inattivi_nf == '' )
    $inattivi_nf = 0;
  
    
  // data from graphs 
  
  $domande = array();
  $valutazioni_f = array();
  $valutazioni_nf = array();

  $year_idx = ($year == "2013/2014" ? 4 : 6);
  
  // questions answers
  for ($i=1; $i<=11; $i++) {

    if ($xpath->query('/html/body/table[1]/tr/td/table[' . $year_idx . ']/td/table/tr[' . $i . ']/td[2]')->item(0) != NULL) {
      $domande[$i-1] = $xpath->query('/html/body/table[1]/tr/td/table[' . $year_idx . ']/td/table/tr[' . $i . ']/td[2]')->item(0)->textContent;
    }

    $valutazioni_f[$i-1] = "";
    $valutazioni_nf[$i-1] = "";

    for ($j = 2; $j <= ($year == "2013/2014" ? 5 : 6); $j++) {

      if ($xpath->query('/html/body/table[1]/tr/td/table[' . $year_idx . ']/tr/td[1]/table/tr[' . ($i+1) . ']/td[' . $j . ']')->item(0) != NULL) {
        $valutazioni_f[$i-1] .= $xpath->query('/html/body/table[1]/tr/td/table[' . $year_idx . ']/tr/td[1]/table/tr[' . ($i+1) . ']/td[' . $j . ']')->item(0)->textContent . "-";
      }

      if ($xpath->query('/html/body/table[1]/tr/td/table[' . $year_idx . ']/tr/td[2]/table/tr[' . ($i+1) . ']/td[' . $j . ']')->item(0) != NULL) {
        $valutazioni_nf[$i-1] .= $xpath->query('/html/body/table[1]/tr/td/table[' . $year_idx . ']/tr/td[2]/table/tr[' . ($i+1) . ']/td[' . $j . ']')->item(0)->textContent . "-";
      }
    }

    $valutazioni_f[$i-1] = substr($valutazioni_f[$i-1], 0, -1);
    $valutazioni_nf[$i-1] = substr($valutazioni_nf[$i-1], 0, -1);

    check_special_chars($valutazioni_nf[$i-1]);
    check_special_chars($valutazioni_f[$i-1]);
  }

  $motivi_nf = array();

  for($i=2; $i<=6; $i++){
    if($xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 4 : 6) . ']/td[2]/div')->item(0) == NULL)
      break;
    else     
      $motivi_nf[$i-2] = $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 4 : 6) . ']/td[2]/div/table/tr['. $i . ']/td[1]')->item(0)->textContent . '-' . $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 4 : 6) . ']/td[2]/div/table/tr['. $i . ']/td[2]')->item(0)->textContent;
  }

  $sugg_f = array();
  $sugg_nf = array();

  for($i=2; $i<=10; $i++){
    if ($xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 4 : 6) . ']/tr[4]/td/table/tr[' . $i . ']/td[2]')->item(0) != NULL)
      $sugg_f[$i-2] = array($xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 4 : 6) . ']/tr[4]/td/table/tr[' . $i . ']/td[1]')->item(0)->textContent, $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 4 : 6) . ']/tr[4]/td/table/tr[' . $i . ']/td[2]')->item(0)->textContent);
    if($xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 4 : 6) . ']/tr[4]/td[2]/table/tr[' . $i . ']/td[2]')->item(0) != NULL)
      $sugg_nf[$i-2] = array($xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 4 : 6) . ']/tr[4]/td/table/tr[' . $i . ']/td[1]')->item(0)->textContent, $xpath->query('/html/body/table[1]/tr/td/table[' . ($year == "2013/2014" ? 4 : 6) . ']/tr[4]/td[2]/table/tr[' . $i . ']/td[2]')->item(0)->textContent);
  }


  // Serializing data 

  // $domande = json_encode($domande);
  for($i = 0; $i<count($valutazioni_f); $i++){
    $valutazioni_f[$i] =  explode("-", $valutazioni_f[$i]);
    $valutazioni_nf[$i] = explode("-", $valutazioni_nf[$i]);
  }
  for($i = 0; $i<count($motivi_nf); $i++){
    $motivi_nf[$i] = explode("-", $motivi_nf[$i]);

  }

  $valutazioni_f  = json_encode($valutazioni_f);
  $valutazioni_nf = json_encode($valutazioni_nf);
  $motivi_nf  = json_encode($motivi_nf);
  $sugg_f     = json_encode($sugg_f);
  $sugg_nf    = json_encode($sugg_nf);


  // TO REMOVE
  $valutazioni_f  = str_replace("[", "", $valutazioni_f);
  $valutazioni_nf = str_replace("[", "", $valutazioni_nf);
  $valutazioni_f  = str_replace("]", "", $valutazioni_f);
  $valutazioni_nf = str_replace("]", "", $valutazioni_nf);

  $valutazioni_f  = "[" . $valutazioni_f . "]";
  $valutazioni_nf = "[" . $valutazioni_nf . "]";

  if($fuoriCorso == '')
    $fuoriCorso = 0;
  if($fuoriCorso_nf == '')
    $fuoriCorso_nf = 0;
  if($inattivi == '')
    $inattivi = 0;
  if($inattivi_nf == '')
    $inattivi_nf = 0;

  $res = $mysqli->query('SELECT * FROM schede WHERE id_insegnamento=\'' . $id_gomp . '\' AND  id_modulo=\''. explode('-', $cod_modulo)[0] . '\' AND canale=\''. $canale .'\';');
  if ($res && $res->num_rows <= 0) {
    $_year = str_replace("20", "", $year);
    $_year = str_replace("/", "", $_year);

    $query = "INSERT INTO `schede` (`totale_schede`, `totale_schede_nf`, `fc`, `inatt`, `inatt_nf`, `domande`, `domande_nf`, `motivo_nf`, `sugg`, `sugg_nf`, `id_insegnamento`, `canale`, `anno_accademico`) VALUES";
    $query .= "\n";
    $query .= utf8_decode('("' . urldecode(str_replace('"', "'", $totaleSchede_f)) . '", "' . urldecode(str_replace('"', "'", $totaleSchede_nf)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $fuoriCorso))     . '", ' .
                '"' . urldecode(str_replace('"', "'", $inattivi))       . '", ' .
                '"' . urldecode(str_replace('"', "'", $inattivi_nf))    . '", ' .
                '"' . urldecode(str_replace('"', "'", $valutazioni_f))  . '", ' .
                '"' . urldecode(str_replace('"', "'", $valutazioni_nf)) . '", ' .
                '"' . urldecode(str_replace('"', "'", $motivi_nf))      . '", ' .
                '"' . urldecode(str_replace('"', "'", $sugg_f))         . '", ' .
                '"' . urldecode(str_replace('"', "'", $sugg_nf))        . '", ' .
                '"' . urldecode($id_gomp) . '", ' .
                '"' . urldecode($canale)  . '",
              "' . $_year . '");');

  if (!$mysqli->query($query))
    die($mysqli->error);

    // echo $query;

  }

}


?>