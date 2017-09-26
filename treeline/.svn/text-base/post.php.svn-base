<?php

/**
 * @author chrisntr
 * @copyright 2007
 */

//$fp = pfsockopen("ssl://".$host, 443, $errno, $errstr);
/*
$host = "www.example.com";

$fp = fsockopen("ssl://".$host, 443, $errno, $errstr, 30);
if (!$fp)
{
echo 'Could not open connection.';
}
else
{
$xmlpacket ='<?xml version="1.0"?>
<Your_xml>
</Your_xml>';

$contentlength = strlen($xmlpacket);

$out = "post /script_name.php HTTP/1.0\r\n";
$out .= "Host: ".$host."\r\n";
$out .= "Connection: Keep-Alive\r\n";
$out .= "Content-type: application/x-www-form-urlencoded\r\n";
$out .= "Content-length: $contentlength\r\n\r\n";
$out .= "xml=$xmlpacket";

fwrite($fp, $out);
while (!feof($fp))
{
	$theOutput .= fgets($fp, 128);
}
fclose($fp);

if ($theOutput == "<response>000001</response>"){
	//It worked
} else {
	//It didn't.
}

// $theOutput is the response returned from the remote script
} 
*/

function postXMLToURL ($server, $path, $xmlDocument) {
	//$xmlSource = $xmlDocument->dump_mem();
	$xmlSource = "
	<?xml version='1.0'?>
	<AMREF>
	<weborder>
		<customer>
			<URN>AX00000001</URN>
			<SourceCode>AXGRB</SourceCode>
			<MediaType>WEB</MediaType>
			<Title>Mr</Title>
			<Forename>John</Forename>
			<Surname>Doe</Surname>
			<Salutation>Dear John Doe</Salutation>
			<Company>A Company</Company>
			<Add1>10 The Street</Add1>
			<Add2>The Road</Add2>
			<Add3></Add3>
			<Add4></Add4>
			<Add5></Add5>
			<Town>Atown</Town>
			<County>Acounty</County>
			<Postcode>RH1 1NN</Postcode>
			<Country>United Kingdom</Country>
			<Telephone>01234 567890</Telephone>
			<Mobile>07775 123456</Mobile>
			<Fax>01234 567891</Fax>
			<Amount>12.00</Amount>
			<PandP>2.00</PandP>
			<Donation>0.00</Donation>
			<Paytype>CC</Paytype>
			<DelTitle>Mr</DelTitle>
			<DelForename>John</DelForename>
			<DelSurname>Doe</DelSurname>
			<DelSalutation>Dear John Doe</DelSalutation>
			<DelCompany>A Company</DelCompany>
			<DelAdd1>10 The Street</DelAdd1>
			<DelAdd2>The Road</DelAdd2>
			<DelAdd3></DelAdd3>
			<DelAdd4></DelAdd4>
			<DelAdd5></DelAdd5>
			<DelTown>Atown</DelTown>
			<DelCounty>Acounty</DelCounty>
			<DelPostcode>RH1 1NN</DelPostcode>
			<DelCountry>United Kingdom</DelCountry>
			<Order>
				<OrderItem>
					<ItemID>AX-CARD01</ItemID>
					<ItemCost>5.00</ItemCost>
					<ItemQty>1</ItemQty>
				</OrderItem>
				<OrderItem>
					<ItemID>AX-CARD02</ItemID>
					<ItemCost>5.00</ItemCost>
					<ItemQty>1</ItemQty>
				</OrderItem>
			</Order>
		</customer>
	</weborder>
</AMREF>";
	//$xmlSource = "hello";
	$contentLength = strlen($xmlSource);
	$fp = fsockopen($server, 80);
	fputs($fp, "POST $path HTTP/1.0\r\n");
	fputs($fp, "Host: $server\r\n");
	fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
	fputs($fp, "Content-Length: $contentLength\r\n");
	fputs($fp, "Connection: close\r\n");
	fputs($fp, "\r\n"); // all headers sent
	fputs($fp, "xml=$xmlSource");
	$result = '';
	while (!feof($fp)) {
		$result .= fgets($fp, 128);
	}
	//echo "ello";
	return $result;
}

function getBody ($httpResponse) {
	$lines = preg_split('/(\r\n|\r|\n)/', $httpResponse);
	$responseBody = '';
	$lineCount = count($lines);
	for ($i = 0; $i < $lineCount; $i++) {
		if ($lines[$i] == '') {
			break;
		}
	}
	for ($j = $i + 1; $j < $lineCount; $j++) {
		$responseBody .= $lines[$j] . "\n";
	}
	return $responseBody;
}
//echo "rar";
//$xmlDocument = domxml_open_file('test.xml');
//echo "rar";
//exit;
$result = postXMLtoURL("chrisntr.co.uk", "/get.php",
$xmlDocument);

//echo $result;exit;
$responseBody = getBody($result);

//header('Content-Type: text/xml');
echo $responseBody; exit;

//$resultDocument = domxml_open_mem($responseBody);

//header('Content-Type: text/xml');
//echo $resultDocument->dump_mem();

?>