<?php
#---------------------------------------------#
#      ********* RotorCMS *********           #
#           Author  :  Vantuz                 #
#            Email  :  visavi.net@mail.ru     #
#             Site  :  http://visavi.net      #
#              ICQ  :  36-44-66               #
#            Skype  :  vantuzilla             #
#---------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

if (isset($_GET['act'])) {
	$act = check($_GET['act']);
} else {
	$act = 'index';
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}

show_title('История моих авторизаций');

if (is_user()) {
	############################################################################################
	##                                   История авторизаций                                  ##
	############################################################################################
	$total = DB::run() -> querySingle("SELECT count(*) FROM `login` WHERE `login_user`=?;", array($log));
	if ($total > 0) {
		if ($start >= $total) {
			$start = 0;
		}

		$querylogin = DB::run() -> query("SELECT * FROM `login` WHERE `login_user`=? ORDER BY `login_time` DESC LIMIT ".$start.", ".$config['loginauthlist'].";", array($log));
		while ($data = $querylogin -> fetch()) {
			echo '<div class="b">';
			echo' <img src="/images/img/clock.gif" alt="clock" /> ';

			if (empty($data['login_type'])) {
				echo '<b>Автовход</b>';
			} else {
				echo '<b>Авторизация</b>';
			}
			echo ' <small>('.date_fixed($data['login_time']).')</small>';

			echo '</div>';

			echo '<div><span class="data">';
			echo 'Browser '.$data['login_brow'].' / ';
			echo 'IP '.$data['login_ip'];
			echo '</span></div>';
		}

		page_strnavigation('authlog.php?', $config['loginauthlist'], $start, $total);
	} else {
		show_error('История авторизаций отсутствует');
	}
} else {
	show_login('Вы не авторизованы, для просмотра истории, необходимо');
}

echo '<img src="/images/img/back.gif" alt="" /> <a href="/pages/index.php?act=menu">Вернуться</a><br />';

include_once ('../themes/footer.php');
?>
