<?php
#-----------------------------------------------------#
#          ********* ROTORCMS *********               #
#              Made by  :  VANTUZ                     #
#               E-mail  :  visavi.net@mail.ru         #
#                 Site  :  http://pizdec.ru           #
#             WAP-Site  :  http://visavi.net          #
#                  ICQ  :  36-44-66                   #
#  Вы не имеете право вносить изменения в код скрипта #
#        для его дальнейшего распространения          #
#-----------------------------------------------------#
require_once ('../includes/start.php');
require_once ('../includes/functions.php');
require_once ('../includes/header.php');
include_once ('../themes/header.php');

$act = (isset($_GET['act'])) ? check($_GET['act']) : 'index';

show_title('Угадай число');

$randgame = mt_rand(1, 100);

if (is_user()) {
	if (isset($_GET['newgame']) || empty($_SESSION['hill'])) {
		$_SESSION['hill'] = $randgame;
		$_SESSION['hi_count'] = 0;
	}

	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			echo '<b>Введите число от 1 до 100</b><br /><br />';
			echo '<b>Попыток: 0</b><br />';

			echo '<div class="form">';
			echo '<form action="hi.php?act=hi" method="post">';
			echo 'Введите число:<br />';
			echo '<input type="text" name="guess" />';
			echo '<input type="submit" value="Угадать" />';
			echo '</form></div><br />';

			echo 'У вас в наличии: '.moneys($udata['users_money']).'<br /><br />';

			echo '<img src="/images/img/faq.gif" alt="image" /> <a href="hi.php?act=faq">Правила</a><br />';
		break;

		############################################################################################
		##                                          Игра                                          ##
		############################################################################################
		case 'hi':

			$guess = abs(intval($_POST['guess']));

			if ($udata['users_money'] >= $config['hisumma']) {
				if ($guess >= 1 && $guess <= 100) {
					$_SESSION['hi_count']++;

					if ($guess != $_SESSION['hill']) {
						if ($_SESSION['hi_count'] < $config['hipopytka']) {
							echo'<b>Введите число от 1 до 100</b><br /><br />';

							echo '<b>Попыток: '.(int)$_SESSION['hi_count'].'</b><br />';

							if ($guess > $_SESSION['hill']) {
								echo $guess.' — это большое число<br /><img src="/images/img/minus.gif" alt="image" /> Введите меньше<br /><br />';
							}
							if ($guess < $_SESSION['hill']) {
								echo $guess.' — это маленькое число<br /><img src="/images/img/plus.gif" alt="image" /> Введите больше<br /><br />';
							}

							echo '<div class="form">';
							echo '<form action="hi.php?act=hi" method="post">';
							echo 'Введите число:<br />';
							echo '<input type="text" name="guess" />';
							echo '<input type="submit" value="Угадать" />';
							echo '</form></div><br />';

							DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`- ".$config['hisumma']." WHERE `users_login`=? LIMIT 1;", array($log));

							$count_pop = $config['hipopytka'] - $_SESSION['hi_count'];

							echo 'Осталось попыток: <b>'.(int)$count_pop.'</b><br />';

							$allmoney = DB::run() -> querySingle("SELECT `users_money` FROM `users` WHERE `users_login`=? LIMIT 1;", array($log));

							echo 'У вас в наличии: '.moneys($allmoney).'<br /><br />';
						} else {
							echo '<img src="/images/img/error.gif" alt="image" /> <b>Вы проигали потому что, не отгадали число за '.(int)$config['hipopytka'].' попыток</b><br />';
							echo 'Было загадано число: '.$_SESSION['hill'].'<br /><br />';

							unset($_SESSION['hill']);
							unset($_SESSION['hi_count']);
						}
					} else {
						DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`+? WHERE `users_login`=? LIMIT 1;", array($config['hiprize'], $log));

						echo '<b>Поздравляем!!! Вы угадали число '.(int)$guess.'</b><br />';
						echo 'Ваш выигрыш составил '.moneys($config['hiprize']).'<br /><br />';

						unset($_SESSION['hill']);
						unset($_SESSION['hi_count']);
					}
				} else {
					show_error('Ошибка! Необходимо ввести число в пределах разрешенного диапазона!');
				}
			} else {
				show_error('Вы не можете играть, т.к. на вашем счету недостаточно средств!');
			}

			echo '<img src="/images/img/reload.gif" alt="image" /> <a href="hi.php?newgame">Начать заново</a><br />';
		break;

		############################################################################################
		##                                    Правила игры                                        ##
		############################################################################################
		case 'faq':

			echo 'Для участия в игре напишите число и нажмите "Угадать", за каждую попытку у вас будут списывать по '.moneys($config['hisumma']).'<br />';
			echo 'После каждой попытки вам дают подсказку большое это число или маленькое<br />';
			echo 'Если вы не уложились за '.$config['hipopytka'].' попыток, то игра будет начата заново<br />';
			echo 'При выигрыше вы получаете на счет '.moneys($config['hiprize']).'<br />';
			echo 'Итак дерзайте!<br /><br />';

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="hi.php">Вернуться</a><br />';
		break;

	default:
		redirect("/games/hi.php");
	endswitch;

} else {
	show_login('Вы не авторизованы, чтобы начать игру, необходимо');
}

echo '<img src="/images/img/games.gif" alt="image" /> <a href="/games/">Развлечения</a><br />';

include_once ('../themes/footer.php');
?>
