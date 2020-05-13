<pre>
sms request: 1110000
sms response: #25500127657aae#1#0000###
sms response format: mcc mnc lac cid ?num ?signal
i.e.: mcc=255, mnc=1 lac=10085 (i.e. hexdec("2765")) cid=31406 (i.e. hexdec("7aae"))
</pre>
<?php
                if (isset($_POST["lac"]) && isset($_POST["cid"]) && isset($_POST["mnc"]) && isset($_POST["mcc"])) {
                $lac = $_POST["lac"];
                    $cid = $_POST["cid"];
                    $mcc = $_POST["mcc"];
                    $mnc = $_POST["mnc"];
                        $data = 
                            "\x00\x0e". 
                            "\x00\x00\x00\x00\x00\x00\x00\x00". 
                            "\x00\x00". 
                            "\x00\x00". 
                            "\x00\x00". 
                            "\x1b". 
                            "\x00\x00\x00\x00".  
                            "\x00\x00\x00\x00".  
                            "\x00\x00\x00\x00".  
                            "\x00\x00". 
                            "\x00\x00\x00\x00".  
                            "\x00\x00\x00\x00".  
                            "\x00\x00\x00\x00".  
                            "\x00\x00\x00\x00".  
                            "\xff\xff\xff\xff". 
                            "\x00\x00\x00\x00";

                    $is_umts_cell = ($cid > 65535);
                    if ($is_umts_cell) // GSM: 4 hex digits, UTMS: 6 hex digits 
                        $data[0x1c] = chr(5);
                    else
                        $data[0x1c] = chr(3);

                    $hexmcc = substr("00000000".dechex($_POST["mcc"]),-8);
                    $hexmnc = substr("00000000".dechex($_POST["mnc"]),-8);
                    $hexlac = substr("00000000".dechex($_POST["lac"]),-8);
                    $hexcid = substr("00000000".dechex($_POST["cid"]),-8);

                    echo "<p>MCC=$hexmcc MNC=$hexmnc LAC=$hexlac CID=$hexcid";

                    $data[0x11] = pack("H*",substr($hexmnc,0,2));
                    $data[0x12] = pack("H*",substr($hexmnc,2,2));
                    $data[0x13] = pack("H*",substr($hexmnc,4,2));
                    $data[0x14] = pack("H*",substr($hexmnc,6,2));

                    $data[0x15] = pack("H*",substr($hexmcc,0,2));
                    $data[0x16] = pack("H*",substr($hexmcc,2,2));
                    $data[0x17] = pack("H*",substr($hexmcc,4,2));
                    $data[0x18] = pack("H*",substr($hexmcc,6,2));

                    $data[0x27] = pack("H*",substr($hexmnc,0,2));
                    $data[0x28] = pack("H*",substr($hexmnc,2,2));
                    $data[0x29] = pack("H*",substr($hexmnc,4,2));
                    $data[0x2a] = pack("H*",substr($hexmnc,6,2));

                    $data[0x2b] = pack("H*",substr($hexmcc,0,2));
                    $data[0x2c] = pack("H*",substr($hexmcc,2,2));
                    $data[0x2d] = pack("H*",substr($hexmcc,4,2));
                    $data[0x2e] = pack("H*",substr($hexmcc,6,2));

                    $data[0x1f] = pack("H*",substr($hexcid,0,2));
                    $data[0x20] = pack("H*",substr($hexcid,2,2));
                    $data[0x21] = pack("H*",substr($hexcid,4,2));
                    $data[0x22] = pack("H*",substr($hexcid,6,2));

                    $data[0x23] = pack("H*",substr($hexlac,0,2));
                    $data[0x24] = pack("H*",substr($hexlac,2,2));
                    $data[0x25] = pack("H*",substr($hexlac,4,2));
                    $data[0x26] = pack("H*",substr($hexlac,6,2));

                    /* I used file_get_contents() at my laptop webserver, but it seems like the PHP version 
                     * at my hosting company is old and it is not supporting that. 
                     * For the hosting company, here we're using cURL.
                     */
                    $use_curl = false;
                    if ($use_curl) {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "http://www.google.com/glm/mmap");
                        curl_setopt($ch, CURLOPT_HEADER, true);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 
                        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-type: application/binary"));
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        $response = curl_exec($ch);
                        if (curl_errno($ch)) 
                            exit("Error: Failed to post data $str curl_errno($ch)");

                        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                        $str = substr($response, $header_size);

                        curl_close($ch);
                    } else {
                        $context = array (
                            'http' => array (
                                'method' => 'POST',
                                'header'=> "Content-type: application/binary\r\n"
                                            . "Content-Length: " . strlen($data) . "\r\n",
                                            'content' => $data
                            )
                        );
                        $xcontext = stream_context_create($context);
                        $str=file_get_contents("http://www.google.com/glm/mmap", FALSE, $xcontext);
                    }

                    $opcode1 = ((ord($str[0]) << 8)) | ord($str[1]);
                    $opcode2 = ord($str[2]);

                    if (($opcode1 != 0x0e) || ($opcode2 != 0x1b)) 
                        exit("<p>Error: Invalid opcode $opcode1 $opcode2. Maybe the LAC/CID is invalid");

                    $retcode = ((ord($str[3]) << 24) | (ord($str[4]) << 16) | (ord($str[5]) << 8) | (ord($str[6])));
                    if ($retcode != 0) 
                        exit("<p>Error: Invalid return code $retcode. Maybe the LAC/CID is invalid");

                    $lon = ((ord($str[11]) << 24) | (ord($str[12]) << 16) | (ord($str[13]) << 8) | (ord($str[14]))) / 1000000;
                    $lat = ((ord($str[7]) << 24) | (ord($str[8]) << 16) | (ord($str[9]) << 8) | (ord($str[10]))) / 1000000;

                    // exit script if cannot geocode cell e.g. not on google's database
                    if ($lat == 0 and $lon == 0)
                        exit('ERROR: cannot determine cell tower location from cell LAC: ' . $_POST["lac"] . ', ' . 'CID: ' . $_POST["cid"]);

                    $addr = ''.$lat . ',' . $lon;
                    $t = urlencode(" (MCC=$mcc MNC=$mnc LAC=$lac CID=$cid)");
                    //echo "<p><a href='http://maps.google.com/maps?q=$addr$t' target='_top'>Lat=$lat Lon=$lon</a>";
                }
            ?>
<pre>
<?php
    echo empty($data) ? '<hr>' : implode(':', unpack("H*",$data));
?>
</pre>
            <form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">

                        <div >MCC
                            <input type="text" size="5" name="mcc" value="<?php if(isset($mcc)) echo $mcc; else echo ""?>">
                        MNC
                            <input type="text" size="5" name="mnc" value="<?php if(isset($mnc)) echo $mnc; else echo ""?>">
                        LAC
                            <input type="text" size="5" name="lac" value="<?php if(isset($lac)) echo $lac; else echo ""?>">
                        CID
                            <input type="text" size="5" name="cid" value="<?php if(isset($cid)) echo $cid; else echo ""?>">


                        <input type="Submit" value="Check it"></div>

            </form>

            <p>
            <iframe 
                width="600" height="400" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
                src="http://maps.google.com/maps?q=<?php if(isset($addr)) echo $addr; else echo ''?>&ie=UTF8&ll=<?php if(isset($addr)) echo $addr; else echo ''?>&sll=<?php if(isset($addr)) echo $addr; else echo ''?>&z=11&output=embed"></iframe>


            </p>