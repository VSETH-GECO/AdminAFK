<?php
// ---------------------------------------------------------------------------- 
// Copyright © Lyon e-Sport, 2018
// 
// Contributeur(s):
//     * Ortega Ludovic - ludovic.ortega@lyon-esport.fr
// 
// Ce logiciel, AdminAFK, est un programme informatique servant à administrer 
// et gérer un tournoi CS:GO avec eBot et Toornament. 
// 
// Ce logiciel est régi par la licence CeCILL soumise au droit français et
// respectant les principes de diffusion des logiciels libres. Vous pouvez
// utiliser, modifier et/ou redistribuer ce programme sous les conditions
// de la licence CeCILL telle que diffusée par le CEA, le CNRS et l'INRIA 
// sur le site "http://www.cecill.info".
// 
// En contrepartie de l'accessibilité au code source et des droits de copie,
// de modification et de redistribution accordés par cette licence, il n'est
// offert aux utilisateurs qu'une garantie limitée.  Pour les mêmes raisons,
// seule une responsabilité restreinte pèse sur l'auteur du programme,  le
// titulaire des droits patrimoniaux et les concédants successifs.
// 
// A cet égard  l'attention de l'utilisateur est attirée sur les risques
// associés au chargement,  à l'utilisation,  à la modification et/ou au
// développement et à la reproduction du logiciel par l'utilisateur étant 
// donné sa spécificité de logiciel libre, qui peut le rendre complexe à 
// manipuler et qui le réserve donc à des développeurs et des professionnels
// avertis possédant  des  connaissances  informatiques approfondies.  Les
// utilisateurs sont donc invités à charger  et  tester  l'adéquation  du
// logiciel à leurs besoins dans des conditions permettant d'assurer la
// sécurité de leurs systèmes et ou de leurs données et, plus généralement, 
// à l'utiliser et l'exploiter dans les mêmes conditions de sécurité. 
// 
// Le fait que vous puissiez accéder à cet en-tête signifie que vous avez 
// pris connaissance de la licence CeCILL, et que vous en avez accepté les
// termes.
// ----------------------------------------------------------------------------

include_once '../config/config.php';
include_once '../traitement/check_config.php';
include_once '../traitement/connect_bdd.php';
include_once '../traitement/verif_user.php';
include_once '../traitement/save_log.php';
include_once '../traitement/check_ip.php';
include_once '../traitement/csrf.php';
include_once 'header.php';
include_once 'footer.php';
include_once 'navbar.php';

session_start();
$result_user = check_user($BDD_ADMINAFK, $_SESSION['login']);
if (!isset($_SESSION['login']) || ($result_user['login']!=$_SESSION['login']))
{
    $_SESSION['state']='1';
	$_SESSION['message']="You must be logged in to access this page";
	header('Location: '.$BASE_URL.'admin.php');
	exit();
}
$level=3;
if ($result_user['login']==$_SESSION['login'])
{
	if($result_user['level']>1)
	{
		$level=2;
		$_SESSION['state']='1';
		$_SESSION['message']="You must be Super-Admin to have access to this";
		header('Location: '.$BASE_URL.'admin.php');
		exit();
	}
	$level=1;
}
if(isset($_POST['user_login']) or isset($_POST['choice']))
{	
	$login=$_POST['user_login'];
	$choice=$_POST['choice'];
}
else if(!isset($_POST['user_login']))
{
	$_SESSION['state']='1';
	$_SESSION['message']="You need to select an admin or a super-admin before edit";
	header('Location: '.$BASE_URL.'pages/edit_admin.php');
	exit();
}
if(check_csrf("csrf".$login)==false)
{
	$_SESSION['state']="1";
	$_SESSION['message']="Error CSRF !";
	header('Location: '.$BASE_URL.'pages/edit_admin.php');
	exit();
}
if($choice == "delete")
{
	$req1 = $BDD_ADMINAFK->prepare("DELETE FROM users WHERE login=?");
	$req1->execute(array($login));
	$_SESSION['state']='2';
	$_SESSION['message']="User ".$login." deleted !";
	$ip = get_ip();
	$action=$_SESSION['message'];
	store_action($action, $ip, $BDD_ADMINAFK);
	header('Location: '.$BASE_URL.'pages/edit_admin.php');
	exit();
}
$req1 = $BDD_ADMINAFK->prepare("SELECT login, level FROM users WHERE login=?");
$req1->execute(array($login));
while ($donnees = $req1->fetch())
{
	$login_bdd=$donnees['login'];
	$level_number=$donnees['level'];
	if($donnees['level']=="1")
	{
		$level_bdd="Super-admin";
	}
	else
	{
		$level_bdd="Admin";
	}
}
$req1->closeCursor();
?>
<html>
	<head>
		<?php header_html('../', False, $CONFIG['url_glyphicon']); ?>
	</head>
	<body>
		<div class= "page-wrap">
			<?php
			$path_redirect ="";
			$path_redirect_disco ="../traitement/";
			$path_redirect_index="../";
			$path_img = "../images/";
			$current = "edit_admin";
			if(!isset($CONFIG['url_ebot'])){$CONFIG['url_ebot'] = "";}
			if(!isset($CONFIG['toornament_api'])){$CONFIG['toornament_api'] = "";}
			if(!isset($CONFIG['toornament_client_id'])){$CONFIG['toornament_client_id'] = "";}
			if(!isset($CONFIG['toornament_client_secret'])){$CONFIG['toornament_client_secret'] = "";}
			if(!isset($CONFIG['toornament_id'])){$CONFIG['toornament_id'] = "";}
			if(!isset($CONFIG['display_connect'])){$CONFIG['display_connect'] = "";}
			if(!isset($CONFIG['display_veto'])){$CONFIG['display_veto'] = "";}
			if(!isset($CONFIG['display_bracket'])){$CONFIG['display_bracket'] = "";}
			if(!isset($CONFIG['display_participants'])){$CONFIG['display_participants'] = "";}
			if(!isset($CONFIG['display_schedule'])){$CONFIG['display_schedule'] = "";}
			if(!isset($CONFIG['display_stream'])){$CONFIG['display_stream'] = "";}
			display_navbar($current, $path_redirect, $path_redirect_disco, $path_redirect_index, $path_img, $level, $CONFIG['url_ebot'], $CONFIG['toornament_api'], $CONFIG['toornament_client_id'], $CONFIG['toornament_client_secret'], $CONFIG['toornament_id'], $CONFIG['display_connect'], $CONFIG['display_veto'], $CONFIG['display_bracket'], $CONFIG['display_participants'], $CONFIG['display_schedule'], $CONFIG['display_stream']);
			?>
			<div class="container">
				<br>
				<h1 class="text-center">Edit user <?php echo $login_bdd; ?></h1>
				<br>
			</div>
			<form method="post" action="../traitement/edit.php">
				<div class="container">
					<div class="form-row">
						<div class="col">
							<label for="login_disable">Login</label>
							<fieldset disabled>
								<input type="text" class="form-control" name="login_disable" id="login_disable" placeholder="Login" pattern=".{4,}" value="<?php echo $login_bdd; ?>" required title="4 characters minimum">
							</fieldset>
						</div>
						<div class="col">
							<?php 
								if($login == $_SESSION['login'])
								{
									echo "<label for='level_disable'>Level</label>";
									echo "<fieldset disabled>";
									echo "<input type='level_disable' class='form-control' name='level_disable' id='level_disable' value='".$level_bdd."' placeholder='Level' pattern='.{4,}' required title='4 characters minimum'>";
									echo "</fieldset>";
								}
								else
								{
									echo "<label for='level'>Level</label>";
									echo "<select id='level' name='level' class='form-control'>";
									if($level_number=="1")
									{
										echo "<option>".$level_bdd."</option>";
										echo "<option>Admin</option>";
									}
									else
									{
										echo "<option>".$level_bdd."</option>";
										echo "<option>Super-admin</option>";
									}
									echo "</select>";
								}
							?>
						</div>
					</div>
					<br>
					<div class="form-row">
						<div class="col">
							<div class="form-group">
								<label for="pass">Password</label>
								<input type="password" class="form-control" name="pass" id="pass" placeholder="Password" pattern=".{4,}" required title="4 characters minimum">
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<label for="pass_confirm">Confirm Password</label>
								<input type="password" class="form-control" name="pass_confirm" id="pass_confirm" placeholder="Confirm Password" pattern=".{4,}" required title="4 characters minimum">
							</div>
						</div>
					</div>
					<br>
					<?php
					echo "<input id='login' name='login' type='hidden' value='".$login_bdd."'>";
					new_crsf("csrf");
					if($login == $_SESSION['login']){echo "<input id='level' name='level' type='hidden' value='".$level_bdd."'>";}
					?>
					<button type="submit" name="envoyer" class="btn btn-dark btn-lg btn-block">Edit user</button>
				</div>
			</form>
			<br><br>
		</div>
		<?php
		$path_img = "../images/";
		display_footer($path_img);
		?>
	</body>
</html>