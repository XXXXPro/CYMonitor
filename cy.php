<?php
/** ================================
 *  @package CYGetter
 *  @author 4X_Pro <admin@openproj.ru>
 *  @url http://xxxxpro.ru
 *  Скрипт отслеживания ТИц и уведомлений о его изменении
 *  Список доменов задается в файле domains.json в формате {"домен1":ТИц1,"домен2":ТИц2}
 *  При добавлении нового домена в качестве начального ТИц можно указать любое целое число
 *  ================================ */

// адреса отправителя и получателя писем
define('MAIL_FROM','admin@example.com');
define('MAIL_TO','admin@example.com');
// сюда можно вставить cookies из броузера, иногда это позволяет избежать бана на больший срок
// define('CY_cookie','');

require 'Requests.php';
class CYMonitor {
  private $req;

  function __construct() {
     $this->req = new Requests;
  }

  function get_cy($domain) {
     $data = $this->req->get('https://yandex.ru/yaca/cy/ch/'.$domain);
     if (preg_match('|ресурса — (\d+)|u',$data,$matches)) {
       return intval($matches[1]);
     }
     else return -1;
  }

  function read_domains($user='.',$filename = 'domains.json') {
    $buffer = file_get_contents($user.'/'.$filename);
    $data = json_decode($buffer,true);
    return $data;
  }

  function save_domains($data,$user='.',$filename='domains.json') {
    $buffer = json_encode($data);
    file_put_contents($user.'/'.$filename,$buffer);
  }

  function process($user='.') {
    $changed = array();
    $domains = $this->read_domains($user);
//    print_r($domains);
    foreach ($domains as $domain=>$cy) {
      $new_cy = $this->get_cy($domain);
      if ($new_cy==-1) break;
//      echo "New value: ".$domain." : ".$new_cy."\n";
      if ($new_cy!=$cy && $new_cy!=-1) {
        $changed[$domain] = array('old_cy'=>$cy,'new_cy'=>$new_cy);
        $domains[$domain]=$new_cy;
      }
      sleep(10);
    }
    if (!empty($changed)) {
      $this->save_domains($domains,$user);
      $this->notify($changed,$user);
      $this->save_html($domains,$changed,$user);
    }
    touch($user.'/lastmod.txt');
  }

  function save_html($domains,$changed,$user='.') {
    $html='<!DOCTYPE html>
<html><head><title>ТИц доменов и его изменения</title></head>
<style type="text/css">
body { padding: 20px; margin: 0 }
table { table-layout: fixed; width: 100%; border-collapse: collapse; }
td { padding: 5px 10px; border: #e0e0e0 1px solid; text-align: center }
td:first-child { width: 60%; text-align: right }
.up { color: green }
.down { color: red }
</style>
<body>
<table>';
    foreach ($domains as $domain=>$cy) {
$html.='<tr><td>'.$domain.'</td><td>'.$cy.'</td>';
if (!empty($changed[$domain])) {
  if ($cy > $changed[$domain]['old_cy']) $html.='<td class="up">+'.($cy-$changed[$domain]['old_cy']).'</td>';
  else $html.='<td class="up">+'.($cy-$changed[$domain]['old_cy']).'</td>';
}
else $html.='<td></td>';
$html.="</tr>\n";
}
    $html.='</table>
    <p style="text-align: right; font-style: italic">Последняя проверка ТИц: #DATE#</p>
</body>
</html>';
    file_put_contents($user.'/result.htm',$html);
  }

  function notify($changed,$user='.') {
    $summ = 0;
    $text =
'Доброго времени суток!

На отслеживаемых доменах изменился ТИц:
';
    foreach ($changed as $domain=>$data) {
      $text .= sprintf("%36s :%5d (%+d)\n",$domain,$data['new_cy'],$data['new_cy']-$data['old_cy']);
      $summ += ($data['new_cy']-$data['old_cy']);
    }
    $text .='
Суммарное изменение : '.$summ;
    echo $text."\n";

    $headers ="From: ".$this->mime_encode('Мониторинг ТИц').'<'.MAIL_FROM.">\n";
    $headers.="Return-Path: ".MAIL_FROM."\n";
    $headers.="MIME-Version: 1.0\n";
    $headers.="Content-Type: text/plain; charset=\"utf-8\"\n";
    $headers.="Content-Transfer-Encoding: 8bit\n";
    $headers.="Precedence: bulk\n";
    $headers.="X-Priority: 3\n";
    $headers.="X-Mailer: Intellect Board 3 Pro Framework";
    $subj=$this->mime_encode("Изменения ТИц!");

    mail(MAIL_TO,$subj,$text,$headers);
  }

  function mime_encode($text) {
         return "=?utf-8?B?".base64_encode($text)."?=";
  }
}

chdir(dirname(__FILE__));
$cy = new CYMonitor;
print $cy->process();
