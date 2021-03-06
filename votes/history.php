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
if (isset($_GET['id'])) {
	$id = abs(intval($_GET['id']));
} else {
	$id = 0;
}
if (isset($_GET['start'])) {
	$start = abs(intval($_GET['start']));
} else {
	$start = 0;
}

switch ($act):
############################################################################################
##                                    Главная страница                                    ##
############################################################################################
	case 'index':

		show_title('История голосований');

		$total = DB::run() -> querySingle("SELECT count(*) FROM `vote` WHERE `vote_closed`=? ORDER BY `vote_time`;", array(1));

		if ($total > 0) {
			if ($start >= $total) {
				$start = 0;
			}

			$queryvote = DB::run() -> query("SELECT * FROM `vote` WHERE `vote_closed`=? ORDER BY `vote_time` DESC LIMIT ".$start.", ".$config['allvotes'].";", array(1));

			while ($data = $queryvote -> fetch()) {
				echo '<div class="b">';
				echo '<img src="/images/img/luggage.gif" alt="image" /> <b><a href="history.php?act=result&amp;id='.$data['vote_id'].'&amp;start='.$start.'">'.$data['vote_title'].'</a></b></div>';
				echo '<div>Создано: '.date_fixed($data['vote_time']).'<br />';
				echo 'Всего голосов: '.$data['vote_count'].'</div>';
			}

			page_strnavigation('history.php?', $config['allvotes'], $start, $total);
		} else {
			show_error('Голосований в архиве еще нет!');
		}
	break;

	############################################################################################
	##                                      Голосование                                       ##
	############################################################################################
	case 'result':
		show_title('Результаты голосований');

		$votes = DB::run() -> queryFetch("SELECT * FROM `vote` WHERE `vote_id`=? LIMIT 1;", array($id));

		if (!empty($votes)) {
			if (!empty($votes['vote_closed'])) {
				$config['newtitle'] = $votes['vote_title'];

				echo '<img src="/images/img/luggage.gif" alt="image" /> <b>'.$votes['vote_title'].'</b> (Голосов: '.$votes['vote_count'].')<br /><br />';

				$queryanswer = DB::run() -> query("SELECT `answer_option`, `answer_result` FROM `voteanswer` WHERE `answer_vote_id`=? ORDER BY `answer_result` DESC;", array($id));
				$answer = $queryanswer -> fetchAssoc();

				$total = count($answer);

				if ($total > 0) {
					$sum = $votes['vote_count'];
					$max = max($answer);

					if (empty($sum)) {
						$sum = 1;
					}
					if (empty($max)) {
						$max = 1;
					}

					foreach($answer as $key => $data) {
						$proc = round(($data * 100) / $sum, 1);
						$maxproc = round(($data * 100) / $max);

						echo '<b>'.$key.'</b> (Голосов: '.$data.')<br />';
						progress_bar($maxproc, $proc.'%').'<br /><br />';
					}

					echo 'Вариантов: <b>'.$total.'</b><br /><br />';
				} else {
					show_error('Ошибка! Для данного голосования не созданы варианты ответов!');
				}
			} else {
				show_error('Ошибка! Данного опроса не существует в истории!');
			}
		} else {
			show_error('Ошибка! Данного голосования не существует!');
		}

		echo '<img src="/images/img/back.gif" alt="image" /> <a href="history.php?start='.$start.'">Вернуться</a><br />';
	break;

default:
	redirect("history.php");
endswitch;

echo '<img src="/images/img/stat.gif" alt="image" /> <a href="index.php">Список голосований</a><br />';

include_once ('../themes/footer.php');
?>
