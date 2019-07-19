<?php
function ambil_list_gatherproxy($alamat){
	$html = file_get_contents($alamat);
	$array_list = explode("gp.insertPrx(", trim($html));
	$max = count($array_list);
	unset($array_list[0]);
	unset($array_list[$max]);
	unset($array_list[$max-1]);
	foreach($array_list as $jsonproxy){
		$jsonproxy_proxy = explode('"PROXY_IP":"', trim($jsonproxy));
		$jsonproxy_proxy_end = explode('","PROXY_LAST_UPDATE"', trim($jsonproxy_proxy[1]));
		$jsonproxy_port = explode('"PROXY_PORT":"', trim($jsonproxy));
		$jsonproxy_port_end = explode('","PROXY_REFS"', trim($jsonproxy_port[1]));
		$penampung[] = $jsonproxy_proxy_end[0].":".hexdec($jsonproxy_port_end[0]);
	}
	array_filter($penampung);
	return $penampung;
}
function ambil_list_proxyspider($alamat){
	$html = file_get_contents($alamat);
	$array_list = explode("appendProxies();", trim($html));
	unset($array_list[1]);
	$jsonproxy_proxy = explode('"proxy":"', trim($array_list[0]));
	$max = count($jsonproxy_proxy);
	unset($array_list[0]);
	unset($array_list[$max]);
	unset($array_list[$max-1]);
	foreach($jsonproxy_proxy as $list){
		$jsonproxy_proxy_end = explode('","proxy_type":"', trim($list));
		$penampung[] = base64_decode($jsonproxy_proxy_end[0]);
	}
	array_filter($penampung);
	return $penampung;
}
function check($url,$usecookie = false,$sock="",$ref) {
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url);  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);  
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,7);
    curl_setopt($ch, CURLOPT_HEADER, 0);   
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/6.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.7) Gecko/20050414 Firefox/1.0.3");  
    if($sock){
        curl_setopt($ch, CURLOPT_PROXY, $sock);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    }
    if ($usecookie){  
        curl_setopt($ch, CURLOPT_COOKIEJAR, $usecookie);  
        curl_setopt($ch, CURLOPT_COOKIEFILE, $usecookie);     
    } 
    if ($ref){  
        curl_setopt($ch, CURLOPT_REFERER,$ref); 
    }
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10); 
    $result["result"]=curl_exec ($ch);  
    $result["info"]=curl_getinfo($ch);  
    curl_close($ch);  
    return $result;  
}
//==================================================================================
$alamat = array("http://www.gatherproxy.com/proxylist/country/?c=indonesia","http://www.gatherproxy.com/proxylist/anonymity/?t=Elite","http://www.gatherproxy.com/proxylist/anonymity/?t=Transparent");
foreach($alamat as $target){
	$penampung[] = ambil_list_gatherproxy($target);
	sleep(1);
}
$penampung[] = ambil_list_proxyspider("https://proxy-spider.com/buy-public-proxies");
$i = 1;
foreach($penampung as $key){
	foreach($key as $value){
		if($value != "" && preg_match('/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})\:?([0-9]{1,5})?/', $value, $match)){
		if($i == 1){
			$file = fopen("list.txt", "w");
		}else{
			$file = fopen("list.txt", "a");
		}
		$keluarannya = trim($value) . "\n";
		fwrite($file, $keluarannya);
		fclose($file);
		$i++;
		}
	}
}
//===================================================================================
$domain = "http://www.google.com/search?pws=0&as_epq=test";
$data_list = file_get_contents('list.txt');
$array_proxy = explode("\n",trim($data_list));
$i = 1;
foreach($array_proxy as $proxy){
	$simpan = check($domain,false,false,"http://www.google.com/");
	if($simpan["result"] != "" ){
		if($i == 1){
			$fp = fopen('list_fix.txt', 'w');
		}else{
			$fp = fopen('list_fix.txt', 'a');
		}
		fwrite($fp, $proxy."\n");
		fclose($fp);
		$i++;
	}
}
?>