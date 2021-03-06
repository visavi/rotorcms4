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

show_title('Вклады');

if (is_user()) {
	switch ($act):
	############################################################################################
	##                                    Главная страница                                    ##
	############################################################################################
		case 'index':

			$databank = DB::run() -> queryFetch("SELECT * FROM `bank` WHERE `bank_user`=? LIMIT 1;", array($log));
			if (!empty($databank)) {
				echo '<b>Выписка по счету</b><br />';
				echo 'На руках: '.moneys($udata['users_money']).'<br />';
				echo 'В банке: '.moneys($databank['bank_sum']).'<br /><br />';

				if ($databank['bank_sum'] > 0) {
					if ($databank['bank_sum'] <= $config['maxsumbank']) {
						if ($databank['bank_time'] >= SITETIME) {
							echo '<b>До получения процентов осталось '.formattime($databank['bank_time'] - SITETIME).'</b><br />';
							echo 'Будет получено с процентов: '.moneys(percent_bank($databank['bank_sum'])).'<br /><br />';
						} else {
							echo '<b>Получение процентов</b> ('.moneys(percent_bank($databank['bank_sum'])).')<br />';
							echo '<div class="form">';
							echo '<form action="bank.php?act=prolong&amp;uid='.$_SESSION['token'].'" method="post">';

							echo '<select name="oper">';
							echo '<option value="0">Получить на руки</option><option value="1">Положить в банк</option>';
							echo '</select><br />';

							echo 'Проверочный код:<br /> ';
							echo '<img src="/gallery/protect.php" alt="" /><br />';
							echo '<input name="provkod" size="6" maxlength="6" /><br />';

							echo '<input value="Получить" type="submit" /></form></div><br />';
						}
					} else {
						echo '<b><span style="color:#ff0000">Внимание у вас слишком большой вклад</span></b><br />';
						echo 'Превышена максимальная сумма вклада для получения процентов на '.moneys($databank['bank_sum'] - $config['maxsumbank']).'<br /><br />';
					}
				} else {
					echo 'Для получения процентов на счете должны быть средства, но не более '.moneys($config['maxsumbank']).'<br /><br />';
				}
			} else {
				echo 'Вы новый клиент нашего банка. Мы рады, что вы доверяеете нам свои деньги<br />';
				echo 'Сейчас ваш счет не открыт, вложите свои средства, чтобы получать проценты с вклада<br /><br />';
			}

			echo '<b>Операция</b><br />';

			echo '<div class="form">';
			echo '<form action="bank.php?act=operacia" method="post">';
			echo '<input type="text" name="gold" /><br />';
			echo '<select name="oper">';
			echo '<option value="2">Положить деньги</option><option value="1">Снять деньги</option>';
			echo '</select><br />';
			echo '<input type="submit" value="Выполнить" /></form></div><br />';

			echo 'Максимальная сумма вклада: '.moneys($config['maxsumbank']).'<br /><br />';
			echo 'Процентная ставка зависит от суммы вклада<br />';
			echo 'Вклад до 100 тыс. - ставка 10%<br />';
			echo 'Вклад более 100 тыс. - ставка 6%<br />';
			echo 'Вклад более 250 тыс. - ставка 3%<br />';
			echo 'Вклад более 500 тыс. - ставка 2%<br />';
			echo 'Вклад более 1 млн. - ставка 1%<br />';
			echo 'Вклад более 5 млн. - ставка 0.5%<br /><br />';

			$total = DB::run() -> querySingle("SELECT count(*) FROM `bank`;");

			echo 'Всего вкладчиков: <b>'.$total.'</b><br /><br />';
		break;

		############################################################################################
		##                                 Получене процентов                                     ##
		############################################################################################
		case 'prolong':

			$uid = check($_GET['uid']);
			$oper = (empty($_POST['oper'])) ? 0 : 1;
			$provkod = check(strtolower($_POST['provkod']));

			if ($uid == $_SESSION['token']) {
				if ($provkod == $_SESSION['protect']) {
					$databank = DB::run() -> queryFetch("SELECT * FROM `bank` WHERE `bank_user`=? LIMIT 1;", array($log));
					if (!empty($databank)) {
						if ($databank['bank_sum'] > 0 && $databank['bank_sum'] <= $config['maxsumbank']) {
							if ($databank['bank_time'] < SITETIME) {
								$percent = percent_bank($databank['bank_sum']);

								if (empty($oper)) {
									DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`+? WHERE `users_login`=?", array($percent, $log));
									DB::run() -> query("UPDATE `bank` SET `bank_oper`=`bank_oper`+1, `bank_time`=? WHERE `bank_user`=?", array(SITETIME + 43200, $log));
								} else {
									DB::run() -> query("UPDATE `bank` SET `bank_sum`=`bank_sum`+?, `bank_oper`=`bank_oper`+1, `bank_time`=? WHERE `bank_user`=?", array($percent, SITETIME + 43200, $log));
								}
								echo '<b>Продление счета успешно завершено, получено c процентов: '.moneys($percent).'</b><br /><br />';
							} else {
								show_error('Ошибка! Время получения процентов еще не наступило!');
							}
						} else {
							show_error('Ошибка! У вас нет денег в банке или вклад слишком большой!');
						}
					} else {
						show_error('Ошибка! У вас не открыт счет в банке!');
					}
				} else {
					show_error('Ошибка! Проверочное число не совпало с данными на картинке!');
				}
			} else {
				show_error('Ошибка! Неверный идентификатор сессии, повторите действие!');
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="bank.php">Вернуться</a><br />';
		break;

		############################################################################################
		##                                        Операции                                        ##
		############################################################################################
		case 'operacia':

			$gold = (int)$_POST['gold'];
			$oper = (int)$_POST['oper'];
			// ----------------------- Снятие со счета ----------------------------//
			if ($oper == 1) {
				show_title('Снятие со счета');

				if ($gold > 0) {
					$querysum = DB::run() -> querySingle("SELECT `bank_sum` FROM `bank` WHERE `bank_user`=? LIMIT 1;", array($log));
					if (!empty($querysum)) {
						if ($gold <= $querysum) {
							DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`+? WHERE `users_login`=?", array($gold, $log));
							DB::run() -> query("UPDATE `bank` SET `bank_sum`=`bank_sum`-?, `bank_time`=? WHERE `bank_user`=?", array($gold, SITETIME + 43200, $log));

							echo 'Сумма в размере <b>'.moneys($gold).'</b> успешно списана с вашего счета<br /><br />';
						} else {
							show_error('Ошибка! Вы не можете снять денег больше чем у вас на счете!');
						}
					} else {
						show_error('Ошибка! Вы не можете снимать деньги, так как у вас не открыт счет!');
					}
				} else {
					show_error('Ошибка! Необходимо ввести сумму для снятия денег!');
				}
			}
			// -------------------------- Пополение счета --------------------------------//
			if ($oper == 2) {
				show_title('Пополнение счета');

				if ($gold > 0) {
					if ($gold <= $udata['users_money']) {
						DB::run() -> query("UPDATE `users` SET `users_money`=`users_money`-? WHERE `users_login`=?", array($gold, $log));

						$querybank = DB::run() -> querySingle("SELECT `bank_id` FROM `bank` WHERE `bank_user`=? LIMIT 1;", array($log));
						if (!empty($querybank)) {
							DB::run() -> query("UPDATE `bank` SET `bank_sum`=`bank_sum`+?, `bank_time`=? WHERE `bank_user`=?", array($gold, SITETIME + 43200, $log));
						} else {
							DB::run() -> query("INSERT INTO `bank` (`bank_user`, `bank_sum`, `bank_time`) VALUES (?, ?, ?);", array($log, $gold, SITETIME + 43200));
						}

						echo 'Сумма в размере <b>'.moneys($gold).'</b> успешно зачислена на ваш счет<br />';
						echo 'Получить проценты с вклада вы сможете не ранее чем через 12 часов<br /><br />';
					} else {
						show_error('Недостаточное количество денег, у вас нет данной суммы на руках');
					}
				} else {
					show_error('Ошибка! Необходимо ввести сумму для пополнения счета!');
				}
			}

			echo '<img src="/images/img/back.gif" alt="image" /> <a href="bank.php">Вернуться</a><br />';
		break;

	default:
		redirect("/games/bank.php");
	endswitch;

} else {
	show_login('Вы не авторизованы, чтобы совершать операции, необходимо');
}

echo '<img src="/images/img/chart.gif" alt="image" /> <a href="livebank.php">Статистика вкладов</a><br />';
echo '<img src="/images/img/money.gif" alt="image" /> <a href="kredit.php">Выдача кредитов</a><br />';
echo '<img src="/images/img/games.gif" alt="image" /> <a href="/games/">Развлечения</a><br />';

include_once ('../themes/footer.php');
?>
