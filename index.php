<?php 
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$db = "switch";
$conn = new mysqli($dbhost, $dbuser, $dbpass, $db) or die("Connection Failed: %s\n". $conn -> error);


if(isset($_POST['port'])){
    $port = $_POST['port'];
    $ePort = $_POST['ePort'];
    $pattern = "/\//";
    $components1 = preg_split($pattern, $port);
    $r = $components1[0];
    $s = $components1[1];
    $p = $components1[2];

    $components2 = preg_split($pattern, $ePort);
    $er = $components2[0];
    $es = $components2[1];
    $ep = $components2[2];


    $delsql = "SELECT * FROM `data` WHERE pKey = '".$port."' and endKey = '".$ePort."';";
    $resultdel = $conn -> query($delsql);
    $rowcountdel=mysqli_num_rows($resultdel);
    

    $sqls = "SELECT * FROM `data` WHERE pKey = '".$port."';";
    $sqld = "SELECT * FROM `data` WHERE pKey = '".$ePort."';";
    $pat = "%/%/%";
    $counts = "SELECT * FROM `data` WHERE pKey = '".$port."' and endKey like '".$pat."';";
    $countd = "SELECT * FROM `data` WHERE pKey = '".$ePort."' and endKey like '".$pat."';";
    
    $results = $conn -> query($sqls);
    $resultd = $conn -> query($sqld);
    $resultsc = $conn -> query($counts);
    $resultdc = $conn -> query($countd);

    $rowcounts=mysqli_num_rows($results);
    $rowcountd=mysqli_num_rows($resultd);
    $rowcountsc=mysqli_num_rows($resultsc);
    $rowcountdc=mysqli_num_rows($resultdc);

    if($rowcountd >= 1 and $rowcounts >=1){
        if($rowcountdel == 0){
            if($rowcountsc == 0){
                
                $sql = "UPDATE data SET endKey = '".$ePort."' WHERE pKey = '".$port."';";
                $result = $conn -> query($sql);
            }
            elseif($rowcountsc > 0){
               
                $sql = "INSERT INTO `data`(`pKey`, `switchName`, `endKey`, `rack`, `switch`, `port`) select '".$port."',`switchName`,'".$ePort."','".$r."','".$s."','".$p."' from `data` where pKey='".$port."' limit 1";
                $result = $conn -> query($sql);
            }
            if($rowcountdc == 0){
              
                $sql = "UPDATE data SET endKey = '".$port."' WHERE pKey = '".$ePort."';";
                $result = $conn -> query($sql);
            }
            elseif($rowcountdc > 0){
           
                $sql = "INSERT INTO `data`(`pKey`, `switchName`, `endKey`, `rack`, `switch`, `port`) select '".$ePort."',`switchName`,'".$port."','".$er."','".$es."','".$ep."' from `data` where pKey='".$ePort."' limit 1";
                $result = $conn -> query($sql);
            }
                
        }
            else{
                if($rowcounts == 1){
                    $delsql = "UPDATE `data` SET `endKey`='' WHERE pKey = '".$port."';";
                    $resultdel = $conn -> query($delsql);
                }
                elseif($rowcounts  >1){
                    $delsql = "DELETE FROM `data` WHERE pKey = '".$port."' and endKey = '".$ePort."';";
                    $resultdel = $conn -> query($delsql);
                }
                if($rowcountd == 1){
                    $delsql = "UPDATE `data` SET `endKey`='' WHERE pKey = '".$ePort."';";
                    $resultdel = $conn -> query($delsql);
                }
                elseif($rowcountd  >1){
                    $delsql = "DELETE FROM `data` WHERE pKey = '".$ePort."' and endKey = '".$port."';";
                    $resultdel = $conn -> query($delsql);
                }
                
                
            }
    }
    

    
}

if(isset($_POST['delS'])){
    $rack = $_POST['delR'];
    $switch = $_POST['delS'];
    $pattern = $rack."/".$switch."/%";
    
    $sql = "SELECT * FROM `data` WHERE pKey LIKE '".$pattern."';";
    $result3 = $conn -> query($sql);
    while($row = $result3 -> fetch_array(MYSQLI_NUM)){
        $sql = "DELETE FROM `data` WHERE pKey = '".$row[0]."';";
        $result = $conn -> query($sql);

        $sql = "SELECT * FROM `data` WHERE pKey ='".$row[2]."';";

        $results = $conn -> query($sql);
        $count=mysqli_num_rows($results);
        if($count > 0){
            if($count == 1){
                $sql = "UPDATE data SET endKey = '' WHERE pKey = '".$row[2]."' and endKey = '".$row[0]."';";
                $result = $conn -> query($sql);
            }
            elseif($count > 1){
                $sql = "DELETE FROM `data` WHERE pKey = '".$row[2]."' and endKey = '".$row[0]."';";
                $result = $conn -> query($sql);

            }
        }
    }
}

if(isset($_POST['switch'])) {
    $switch = $_POST['switch'];
    $rack = $_POST['rack'];
    $quantity = $_POST['squantity'];

    for($t = 0;$t<$quantity;$t++){
        $sql = "SELECT pKey FROM `data` ORDER BY port;";
        $result = $conn -> query($sql);
        $switchCount = 0;
        while($row = $result -> fetch_array(MYSQLI_NUM)){
            $pattern = "/\//";
            $components1 = preg_split($pattern, $row[0]);
            if($components1[1] > $switchCount AND $components1[0]==$rack){
                $switchCount = $components1[1];
            }
        }
        $switchCount += 1;
        $sql2 = "SELECT portCount FROM `switch_data` where switchName='".$switch."';";
        $result2 = $conn -> query($sql2);
        while($row = $result2 -> fetch_array(MYSQLI_NUM)){
            for($i = 0; $i<=$row[0];$i++){
                $sql = "INSERT INTO `data` (`pKey`, `switchName`, `endKey`, `rack`, `switch`, `port`) VALUES ('".$rack."/".$switchCount."/".$i."', '".$switch."', '', ".$rack.", ".$switchCount.", ".$i.");";
                
                $result = $conn -> query($sql);
                
            }
            
        }
    }

    
    
}



?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="styles.css?version=<?php echo rand(111,999)?>">
    <script src="jquery.min.js"></script>
    <script>

        

        function getObject(rackId,switchId,portId){
            if(portId==null && switchId==null){
                return $("#container").children("#"+rackId);
            }
            else if(portId==null){
                return $("#container").children("#"+rackId).children("#"+switchId);
            }
            else{
                return $("#container").children("#"+rackId).children("#"+switchId).children("#"+portId);
            }
            
        }

        function addRack(rackId){
            //var count = $("rack").length;
            if($('#container').children('#'+rackId).length == 0){
                var rack = document.createElement("rack");
                rack.id = rackId;
                rack.className = "rack";
                $('#container').append(rack);
            }else{

            }
            
        }

        function addSwitch(rackId, switchId,switch_name){
            //var count = $('#container').children('#'+rackId).children("switch").length;
            if($('#container').children('#'+rackId).children("#"+switchId).length == 0){
                var sw = document.createElement("switch");
		var label = document.createElement("label");
		label.className = "label";
		label.innerHTML = (switchId+1)+": "+switch_name;
                sw.id = switchId;
                sw.className = switch_name;
		sw.append(label);
                $('#container').children('#'+rackId).append(sw);
            }
            else{

            }
            
        }

        function addPort(rackId, switchId, portId, endRack,endSwitch, endPort){
            if($('#container').children('#'+rackId).children("#"+switchId).children("#"+portId).length == 0){
                //var count = $('#container').children('#'+rackId).children("#"+switchId).children("port").length;
                var port = document.createElement("port");
                port.id = portId;
                port.className = "port";
                $(port).data('eRack',endRack);
                $(port).data('eSwitch',endSwitch);
                $(port).data('ePort',endPort);
                $('#container').children('#'+rackId).children("#"+switchId).append(port);
            }
            else{
                var port = getObject(rackId,switchId,portId);
                $(port).data('eRack',($(port).data('eRack')+","+endRack));
                $(port).data('eSwitch',($(port).data('eSwitch')+","+endSwitch));
                $(port).data('ePort',($(port).data('ePort')+","+endPort));
            }
            if(typeof endPort != "undefined"){
                getObject(rackId,switchId,portId).css("background-color","blue");
            }
        }

        function addHover(obj){
            $(function() {
            var moveLeft = 20;
            var moveDown = 10;
            var endPort;
            color = "black";
            obj.hover(function(e) {
                $(this).parent().css("backgroundColor","green");
                $(this).css('backgroundColor',"red");
                var switch_name = this.parentNode.className;
                var portId = parseInt(this.id);
                if(portId==0){
                    portId = "0-console";
                }
                var switchId = parseInt(this.parentNode.id)+1;
                var rackId = parseInt(this.parentNode.parentNode.id)+1;
                
                var eRackId = String($(this).data('eRack')).split(',');
                var eSwitchId = String($(this).data('eSwitch')).split(',');
                var ePortId = String($(this).data('ePort')).split(',');
                
                var output = "";
                endPorts = [];
                for(var i = 0; i < eRackId.length;i++){
                    
                    endPort = getObject(parseInt(eRackId[i]),parseInt(eSwitchId[i]),parseInt(ePortId[i]));
                    endPorts.push(endPort);
                    if(ePortId[i]=="0"){
                        ePortId[i] = "0-console";
                    }
                    var eSwitchName = endPort.parent().attr('class');
                    
                    
                    if(ePortId[i] != "undefined" && eSwitchId[i] != "undefined" && eRackId[i] !="undefined"){
                        color = "blue";
			endPort.parent().parent().css("border-color","green");
                        endPort.parent().css("background-color","green");
                        endPort.css("background-color","red");
                        
                        eSwitchId[i] = parseInt(eSwitchId[i])+1;
                        eRackId[i] = parseInt(eRackId[i])+1;
                        output += eSwitchName + "\n" + eRackId[i]+"/"+eSwitchId[i]+"/"+ePortId[i];
                    }
                    else{
                        color = "black";
                        output = "No End Port";
                    }
                }
                $('div#pop-up p').text(switch_name+"\n"+rackId+"/"+switchId+"/"+portId+"\n"+output);
                $('div#pop-up').show();

            }, function() {
                $('div#pop-up').hide();
                endPorts.forEach(function (item, index) {
                    item.css("background-color",color);
                    item.parent().css("background-color","white");
		    item.parent().parent().css("border-color","black");
                });
                
                $(this).parent().css("backgroundColor","white");
                $(this).css('backgroundColor',color);
		$(this).parent().parent().css("border-color","black");
            });

            obj.mousemove(function(e) {
                $("div#pop-up").css('top', e.pageY + moveDown).css('left', e.pageX + moveLeft);
            });

        });
        }

        

        function start(name, r,s,p,eR,eS,eP){
 
            
                addRack(r);
                addSwitch(r,s,name);
                addPort(r,s,p,eR,eS,eP);
		addHover(getObject(r,s,p));
            }

            function start2(){
                
                for(var i = 0; i < 3; i++){
                    addRack();
                    for(var j = 0; j<10;j++){
                        addSwitch(i);
                        for(var k = 0;k<54;k++){
                            addPort(i,j);
                        }
                    }
                }
		addHover(getObject(r,s,p));
            }

        
        
    </script>


    
    

</head>
<body ">
    
    <UI>
        <con>
        <form action="" method="POST">
            <label for="switch">Select Device:</label>
            <select required name="switch" id="switch">
                <?php 
                    $sql = "SELECT * FROM `switch_data` ORDER BY switchName;";
                    $result = $conn -> query($sql);
                    while($row = $result -> fetch_array(MYSQLI_NUM)){
                        echo "<option value='".$row[0]."'>".$row[0]."</option>";
                    }
                ?>
            </select>
            
            <label for="rack">Select Rack:</label>
            <input placeholder="1" required id="rack" name="rack" type="text">
            <label for="squantity">Quantity:</label>
            <input required placeholder="1" id="squantity" name="squantity" type="text">
            <input class="submit"  type="submit" value="Add Device">
        </form>
        <br>
        <form action="" method="POST">
            <label for="port">Connect Port:</label>
            <input pattern="[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{1,2}"required placeholder="1/1/1" id="port" name="port" type="text">
            <label for="ePort">To Port:</label>
            <input pattern="[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{1,2}" required placeholder="1/1/1" id="ePort" name="ePort" type="text">
            <input class="submit"  type="submit" value="Connect">
        </form>
        <br>
        <form action="" method="POST">
            <label for="delR">From Rack:</label>
            <input class="basic-slide" required placeholder="1" id="delR" name="delR" type="text">
            <label for="delS">Delete Device:</label>
            <input required placeholder="1" id="delS" name="delS" type="text">
            <input class="submit" type="submit" value="Delete Device">
        </form>
                </con>
    </UI>
    <container id="container">
    <?php 

$sql = "SELECT * FROM `data` ORDER BY rack, switch, port;";
$result = $conn -> query($sql);
while($row = $result -> fetch_array(MYSQLI_NUM)){
    $pattern = "/\//";

    $components1 = preg_split($pattern, $row[0]);
    $name = "'".$row[1]."'";
    $r = $components1[0]-1;
    $s = $components1[1]-1;
    $p = $components1[2];


    $components2 = preg_split($pattern, $row[2]);
    
    if(count($components2)==3){
        $eR = $components2[0]-1;
        $eS = $components2[1]-1;
        $eP= $components2[2];
        echo"
        <script>
            start($name,$r,$s,$p,$eR,$eS,$eP);
            
            
        </script>";
    }
    else{
        echo"
        <script>
            start($name,$r,$s,$p);
            
            
        </script>";
    }
    
    
    


    
    
 
    
}

?>
        
    </container>
    <div id="pop-up">
        <p>
         
       </p>
       
     </div>
</body>
</html>