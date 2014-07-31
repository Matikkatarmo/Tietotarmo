testiiiiiiiiiiiii
  	  <?php   
	  
	   $con = mysqli_connect("localhost",$_SESSION['tunnus'],$_SESSION['passu'],"matikkatarmo");
	   if (!$con->set_charset("utf8")) {
			printf("Error loading character set utf8: %s\n", $con->error);
		}	 
	   echo $con->error;
	   
	  function haeOppilaat(){
		global $con;
		$oppilaat = mysqli_query($con,"select * from oppilas");
		return $oppilaat;
	  }
	  function tulostaOppilasvalikko(){
		echo "<select name='oppilas'  required> <option value='' selected='selected'>Valitse</option>";
		$oppilaat = haeOppilaat();			  
		while($row = mysqli_fetch_assoc($oppilaat)){
			echo 
				"<option value='" . $row['oppilas_id'] . "'> " .  $row['etunimi'] . " " . $row['sukunimi'] . "</option>";
		}
		echo "</select>";
	  }  
	  
	  //TUNTIHAKU
	  function haeOppilaanTiedot($oppilas_id){
		global $con;
		return mysqli_query($con,"select pvm,date_format(pvm,'%d.%m.%Y') as date, tietoja from tunti where oppilas_id = $oppilas_id order by pvm desc");
		
	  }
	  
	  
	 function tulostaHaetutTunnit(){
		if(isset($_POST['tuntihaku_submit'])){
			$tiedot = haeOppilaanTiedot($_POST['oppilas']);
			echo "<div id='tuntilista'> <table> <tr id='otsikkorivi'> <th> Päivämäärä </th> <th> Kuvaus </th></tr>" ;
				 while($row = mysqli_fetch_assoc($tiedot)){
					$kuvaus = $row['tietoja'];
					$kuvaus = wordwrap($kuvaus, 60,"<br />\n");
					echo 
						"<tr><td> " .$row['date'] . ":</td> <td> " . $kuvaus . "</td></tr>";
				}
			echo "</table></div>";
		}
	}
	 
	 function tarkistaLoggaus(){
		 if(haeOpeId() == ""){
			session_destroy();
			echo "<script> alert('Kirjaudu uudestaan sisään.'); 
			location.href='login.php'; </script>";  
			exit;		
		 }	 
	 }

//-------------------TUNTITIETOJEN LÄHETYS------------------------
 if(isset($_POST['tunti_submit'])){
	//MUUTTUJAT
	
	$vuosi = $_POST['tunti_pvm_y'];
	$kuu = $_POST['tunti_pvm_m'];
	$pv = $_POST['tunti_pvm_d'];
	tarkistaPVM($vuosi,$kuu,$pv);
	$t_pvm = muotoilePVM($vuosi,$kuu,$pv);
	$t_oppilas = $_POST["oppilas"] ;
	$t_kesto = test_input($_POST["tunti_kesto"]);
	$t_kuvaus = test_input($_POST["tunti_kuvaus"]);
	$t_paikka = $_POST['tunti_paikka'];
	
	//TARKISTA LOGGAUS
	tarkistaLoggaus();
	$ope_id = haeOpeId();
	//VALMISTELE SQL JA LÄHETÄ (TULOSTA VARMISTUS)
	global $con;
	$stmt = $con -> prepare("INSERT INTO tunti (oppilas_id,opettaja_id,pvm,kestoMin,tietoja,paikka) values (?,?,?,?,?,?);");
	echo $con->error;
	if($stmt -> bind_param('iisiss',$t_oppilas,$ope_id,$t_pvm,$t_kesto,$t_kuvaus,$t_paikka)){
		echo "Lähetän tiedot ". $t_oppilas . $ope_id . $t_pvm . $t_kesto . $t_kuvaus . $t_paikka;
		$stmt -> execute();
		
	}
	
 }

function muotoilePVM($vuosi,$kuu,$pv){
	return $vuosi . "-" . $kuu . "-" . $pv;
}
function tarkistaPVM($vuosi,$kuu,$pv){	
	//30-PÄIVÄISET KUUKAUDET:
	if(($kuu < 8 and $kuu % 2==0) or ($kuu > 7 and $kuu % 2 == 1)){
		if($pv == 31){
			echo "<script> alert('Päivämäärä ei kelpaa!')</script>";
			return false;
			exit;
		}
	}
	//KARKAUSVUOSI:
	if($kuu == 2){
		if(($vuosi % 4 != 0 and $pv > 28) or ($vuosi % 4 == 0 and $pv > 29)){
			echo "<script> alert('Päivämäärä ei kelpaa!')</script>";
			return false;
			exit;
		}	
	}
	return true;	
}

//----------------TARKISTA SCRIPTIEN VARALTA--------------------------------------------------------	

  function test_input($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlentities($data,NULL,'utf-8');
	//$data = htmlspecialchars($data);
	return $data;
}

  function haeOpeId(){
	$username = $_SESSION['tunnus'];
	global $con;
	echo $con->error;
	$result = mysqli_query($con,"select opettaja_id from opettaja where username = '$username'");
	$ope_id = mysqli_fetch_assoc($result)['opettaja_id'];
	return $ope_id;
}
//_---------------- LISÄÄ ASIAKAS --------------------

	if(isset($_POST['submit_asiakas'])){
	//MUUTTUJAT
		$as_etunimi = test_input($_POST["as_etunimi"]);
		$as_sukunimi = test_input($_POST["as_sukunimi"]);
		$as_puh = test_input($_POST["as_puh"]);
		$as_osoite = test_input($_POST["as_osoite"]);
		$as_email = test_input($_POST["as_email"]);
		

	//VALMISTELE SQL
	global $con;
	$stmt = $con -> prepare("INSERT INTO asiakas (etunimi,sukunimi,puh,osoite,sposti) values (?,?,?,?,?)");
	echo $con->error;
	echo "Lähetän tiedot ". $as_etunimi . $as_sukunimi . $as_puh . $as_osoite . $as_email;	
	$stmt -> bind_param('sssss',$as_etunimi,$as_sukunimi,$as_puh,$as_osoite,$as_email);
	tarkistaLoggaus();
	
	//$stmt -> execute();

	}
	
//-------------------LISÄÄ OPPILAS ---------------------
	if(isset($_POST['submit_oppilas'])){
	//MUUTTUJAT
		$op_etunimi = test_input($_POST['op_etunimi']);
		$op_sukunimi = test_input($_POST['op_sukunimi']);
		$op_puh = test_input($_POST['op_puh']);
		$op_osoite = test_input($_POST['op_osoite']);
		$op_email = test_input($_POST['op_email']);
		$op_maksaja = test_input($_POST['op_maksaja']);

		
		

	//VALMISTELE SQL
	$stmt = $con -> prepare("INSERT INTO oppilas (etunimi,sukunimi,puh,osoite,sposti,maksaja_id) values (?,?,?,?,?,?)");
	echo $con->error;
	$stmt -> bind_param('sssssi',$op_etunimi,$op_sukunimi,$op_puh,$op_osoite,$op_email,$op_maksaja);
	tarkistaLoggaus();
	echo "Lähetän tiedot ". $op_etunimi . $op_sukunimi . $op_puh . $op_osoite . $op_email . $op_maksaja;	
	$stmt -> execute();

	}
	
	//------------------------------ MAKSUHAKU ------------------------------------
	
	
  
  if(isset($_POST['maksuhaku_submit'])){
		//MUUTTUJAT
		$a_y = $_POST['alkaen_y'];
		$a_m = $_POST['alkaen_m'];
		$a_d = $_POST['alkaen_d'];
		$p_y = $_POST['paattyen_y'];
		$p_m = $_POST['paattyen_m'];
		$p_d = $_POST['paattyen_y'];
		
		if(tarkistaPVM($a_y,$a_m,$a_d) and tarkistaPVM($p_y,$p_m,$p_d)){//TARKISTA PÄIVÄMÄÄRÄ JA HAE TIEDOT TIETOKANNASTA
			global $con;
			$ope_id = "'" . haeOpeId() . "'";
			$a_pvm = "'" . muotoilePVM($a_y,$a_m,$a_d) . "'";
			$p_pvm = "'" . muotoilePVM($p_y,$p_m,$p_d) . "'";
			$asiakkaat = mysqli_query($con,"select etunimi,sukunimi,asiakas_id,osoite,postinro,kaupunki from asiakas");
			$result = mysqli_query($con,"select tunti_id,kestoMin,pvm,date_format(pvm,'%d.%m.%Y') as date, asiakas_id,paikkalisa,paikka.paikka from tunti,oppilas,asiakas,paikka where pvm > $a_pvm and 
				tunti.oppilas_id = oppilas.oppilas_id and
				oppilas.maksaja_id = asiakas.asiakas_id and
				tunti.opettaja_id = $ope_id and
				tunti.paikka = paikka.paikka_id");
			$asiakas = array();
			$tunti = array();
			
			while($row = mysqli_fetch_assoc($asiakkaat)){
				$asiakas[$row['asiakas_id']] = $row['etunimi'] . " " . $row['sukunimi'] . " <br \> " . $row['osoite'] . "<br>" . $row['postinro'] . " " . $row['kaupunki']; 
			}
			while($row = mysqli_fetch_assoc($result)){
				$tunti[$row['tunti_id']] = $row;
			}
			//MAKSUTIETOJEN TULOSTUS
			echo "<table class='maksutietotaulukko'>";
			$j=0; //LOMAKKEEN MINUUTIT YHTEENSÄ
			
			foreach(array_keys($asiakas) as $id){
				$i = 0; //ASIAKKAAN MINUUTIT YHTEENSÄ
				$lkm = 1; //ASIAKKAAN TUNTIEN LKM
				$pl = 0; //ASIAKKAAN PAIKKALISÄT YHTEENSÄ
				foreach(array_keys($tunti) as $t_id){ //LASKE TUNTIEN LKM
					if($tunti[$t_id]['asiakas_id'] == $id){
						$lkm ++;
					}
				}
				if($lkm > 1){ //TULOSTA TIETOJA VAIN JOS TUNTEJA VALITULLA AJALLA
					echo "<tr><th> Asiakastiedot </th><th> Päivämäärä </th> <th> Kesto (min) </th><th> Hinta (€) </th> <th> Paikkalisä (€) </th><th> Yht. (€) </th></tr>"; //OTSIKKORIVI
					echo "<tr><td rowspan =" . $lkm . " id = 'hlotiedot'>" . $asiakas[$id] . "</td>"; //HENKILÖTIEDOT
					foreach(array_keys($tunti) as $t_id){  //LASKUTUSTIETOJEN TULOSTUSLOOPPI					
						if($tunti[$t_id]['asiakas_id'] == $id){
							$t = $tunti[$t_id];
							$h = round($t['kestoMin']/45*25,2); //TUNTIHINTA KAHDEN DESIMAALIN TARKKUUDELLA
							$yht = $h + $t['paikkalisa'];	//TUNTIHINTA PAIKKALISINEEN
							echo "<td>" . $t['date'] . "</td><td> " . $t['kestoMin'] . " </td><td> " . $h . "</td><td>" . $t['paikkalisa'] . " (" . $t['paikka'] . ")</td><td>" . $yht . "</td></tr>";
							$i = $i + $t['kestoMin'];
							$pl += $t['paikkalisa'];
						}
					}
					$tuntisumma = round($i/45*25,2);
					$loppusumma = $tuntisumma+$pl;
					echo "<tr class = 'yhteensa'><td > Yhteensä: </td><td>" . $i . "</td><td>" . $tuntisumma . "</td><td>" . $pl . "</td><td>" . $loppusumma . "</td></tr>";
					$j += $i;
				}
			}
			
			echo "</table>";
			echo "Yhteensä: " . round($j/45*25,2) . " euroa brutto.";
		
		}
  }
	
		
	
	
 	
	
	
	
	//-------------------------------LOGOUT-----------------------------------------
	if(isset($_POST['logout'])){
		session_destroy();
		echo "<script> location.href='login.php' </script>";
	}
	
	
	
?>